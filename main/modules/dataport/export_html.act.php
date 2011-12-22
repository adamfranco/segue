<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(dirname(__FILE__)."/Rendering/FileExportSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This action will export the files in a site.
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
class export_htmlAction
	extends Action
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 */
	public function isAuthorizedToExecute () {
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.view'),
			SiteDispatcher::getCurrentRootNode()->getQualifierId());
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 */
	public function execute () {
		try {
			if (!$this->isAuthorizedToExecute())
				throw new PermissionDeniedException();
			
			ob_start();
			$harmoni = Harmoni::instance();
					
			$component = SiteDispatcher::getCurrentNode();
			$site = SiteDispatcher::getCurrentRootNode();
			
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotBySiteId($site->getId());
			
			$exportDirname = $slot->getShortname()."-html";
			$exportDir = DATAPORT_TMP_DIR."/".$exportDirname;
			$archivePath = DATAPORT_TMP_DIR.'/'.$exportDirname.".zip";


			if (file_exists($exportDir)) {
				$changedTime = filemtime($exportDir);
				// If the export is more than an hour old, trash it.
				if ($changedTime < time() - 3600)
					$this->deleteRecursive($exportDir);
				// If it is less than an hour old, leave it in place.
				else
					throw new AlreadyExportingException("Another export of this site is in progress (data written last on ".date('r', $changedTime).").  Please wait. <br/><br/>The other export will be force-quit if it does not finish in ".round((3600 - (time() - $changedTime))/60)." minutes.");
			}
			mkdir($exportDir);

			// Do the export
			$urlParts = parse_url(MYURL);
			$urlPrefix = rtrim($urlParts['path'], '/');
			$include = array(
				$urlPrefix.'/gui2', 
				$urlPrefix.'/images',
				$urlPrefix.'/javascript',
				$urlPrefix.'/polyphony',
				$urlPrefix.'/repository',
				$urlPrefix.'/plugin_manager',
				$urlPrefix.'/rss',
				$urlPrefix.'/dataport/html/site/'.$slot->getShortname(),
			);
			if (defined('WGET_PATH'))
				$wget = WGET_PATH;
			else
				$wget = 'wget';
			$command = $wget." -r --page-requisites --html-extension --convert-links --no-directories "
				."--directory-prefix=".escapeshellarg($exportDir.'/content')." "
				."--include=".escapeshellarg(implode(',', $include))." "
				."--header=".escapeshellarg("Cookie: ".session_name()."=".session_id())." "
				.escapeshellarg($harmoni->request->quickURL('dataport', 'html', array('site' => $slot->getShortname())));
			
// 			throw new Exception($command);
			
			// Close the session. If we don't, a lock on the session file will 
			// cause the request initiated via wget to hang.
			session_write_close();
			
			exec($command, $output, $exitCode);
			
			if ($exitCode) {
				throw new Exception('Wget Failed. '.implode("\n", $output));
			}
			
			// Copy the main HTML file to index.html
			copy($exportDir.'/content/'.$slot->getShortname().'.html', $exportDir.'/content/index.html');
			// Copy the index.html file up a level to make it easy to find
			file_put_contents($exportDir.'/index.html', 
				preg_replace('/(src|href)=([\'"])([^\'"\/]+)([\'"])/', '$1=$2content/$3$4',
					file_get_contents($exportDir.'/content/index.html')));
			
			
			// Zip up the result
			$archive = new ZipArchive();
			if ($archive->open($archivePath, ZIPARCHIVE::CREATE) !== TRUE)
				throw new Exception("Could not create zip archive.");
			$this->addDirectoryToZip($archive, $exportDir, $exportDirname);
			$archive->close();
						
			// Remove the directory
			$this->deleteRecursive($exportDir);
			
			if ($output = ob_get_clean()) {
				print $output;
				throw new Exception("Errors occurred, output wasn't clean.");
			}
			
			header("Content-Type: application/zip;");
			header('Content-Disposition: attachment; filename="'
								.basename($archivePath).'"');
			header('Content-Length: '.filesize($archivePath));
			print file_get_contents($archivePath);
			
			// Clean up the archive
			unlink($archivePath);
		} catch (PermissionDeniedException $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($archivePath))
				unlink($archivePath);
			
			return new Block(
				_("You are not authorized to export this component."),
				ALERT_BLOCK);
		} catch (AlreadyExportingException $e) {
			return new Block(
				$e->getMessage(),
				ALERT_BLOCK);
		} catch (Exception $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($archivePath))
				unlink($archivePath);
			
			throw $e;
		}
		
		error_reporting(0);
		exit;
	}
	
	/**
	 * Recursively delete a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 */
	protected function deleteRecursive ($path) {
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					$this->deleteRecursive($path.DIRECTORY_SEPARATOR.$entry);
				}
			}
			rmdir($path);
		} else {
			unlink($path);
		}
	}
	
	/**
	 * Recursively add files to a zip archive.
	 * 
	 * @param ZipArchive $archive
	 * @param string $sourcePath
	 * @param string $localPath
	 * @return void
	 */
	protected function addDirectoryToZip (ZipArchive $archive, $sourcePath, $localPath = '') {
		foreach (scandir($sourcePath) as $file) {
			if (is_dir($sourcePath.'/'.$file)) {
				if (!preg_match('/^\./', $file))
					$this->addDirectoryToZip($archive, $sourcePath.'/'.$file, trim($localPath.'/'.$file, '/'));
			} else {
				$archive->addFile($sourcePath.'/'.$file, trim($localPath.'/'.$file, '/'));
			}
		}
	}
}

class AlreadyExportingException extends Exception { }

?>