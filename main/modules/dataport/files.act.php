<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */ 

// Use a custom version of Archive/Tar if requested.
if (defined('ARCHIVE_TAR_PATH'))
	require_once(ARCHIVE_TAR_PATH);
else
	require_once("Archive/Tar.php");

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
class filesAction
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
		ob_start();
		$harmoni = Harmoni::instance();
				
		$component = SiteDispatcher::getCurrentNode();
		$site = SiteDispatcher::getCurrentRootNode();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($site->getId());
		
		$exportDir = DATAPORT_TMP_DIR."/".$slot->getShortname()."-files";
		mkdir($exportDir);
		
		try {
			// Do the export
			$visitor = new FileExportSiteVisitor($exportDir);
			$component->acceptVisitor($visitor);
			
			$archive = new Archive_Tar($exportDir.".tar.gz");
			$archive->createModify($exportDir, '', DATAPORT_TMP_DIR);
			
			// Remove the directory
			$this->deleteRecursive($exportDir);
			
			if ($output = ob_get_clean()) {
				print $output;
				throw new Exeception("Errors occurred, output wasn't clean.");
			}
			
			header("Content-Type: application/x-gzip;");
			header('Content-Disposition: attachment; filename="'
								.basename($exportDir.".tar.gz").'"');
			print file_get_contents($exportDir.".tar.gz");
			
			// Clean up the archive
			unlink($exportDir.".tar.gz");
		} catch (PermissionDeniedException $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($exportDir.".tar.gz"))
				unlink($exportDir.".tar.gz");
			
			return new Block(
				_("You are not authorized to export this component."),
				ALERT_BLOCK);
		} catch (Exception $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($exportDir.".tar.gz"))
				unlink($exportDir.".tar.gz");
			
			throw $e;
		}
		
		error_reporting(0);
		exit;
	}
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 */
	function getNodeId () {
		return SiteDispatcher::getCurrentNodeId();
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
}

?>