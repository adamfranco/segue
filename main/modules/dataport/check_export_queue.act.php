<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * This is a command line script that will check the export queue and export the 
 * next site if needed.
 * 
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 */
class check_export_queueAction
	extends Action
{

	/**
	 * Constructor
	 * 
	 */
	public function __construct () {
		$this->usage = 
"Usage:
	".$_SERVER['argv'][0]." [-h|--help] -d=<destination directory>
	
	-h,--help  Print out this help text.
	-d         The directory in which to place the exported site archives.

";
	}
		
	/**
	 * Authorization. As existence is not sensitive information, allow anonymous access.
	 *
	 * @return boolean
	 * @access public
	 * @since 3/26/08
	 */
	public function isAuthorizedToExecute () {
		// Only allow execution from the command line for anonymous
		if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
			return true;
		
		return false;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException('This command can only be run by admins or from the command-line.');
		
		header("Content-Type: text/plain");
		
		if (RequestContext::value('help') || RequestContext::value('h') || RequestContext::value('?')) {
			throw new HelpRequestedException($this->usage);
		}
		
		$outDir = RequestContext::value('d');
		if (empty($outDir))
			throw new InvalidArgumentException("An output directory must be specified.\n\n".$this->usage);
		if (!is_dir($outDir) || !is_writable($outDir))
			throw new InvalidArgumentException("The output directory doesn't exist or is not writeable.\n\n".$this->usage);
		
		foreach (SlotAbstract::getLocationCategories() as $category) {
			$baseUrl = SiteDispatcher::getBaseUrlForLocationCategory($category);
			if (!preg_match('/^https?:\/\/.+/', $baseUrl))
				throw new ConfigurationErrorException('Please set a base URL for the \''.$category.'\' category with SiteDispatcher::setBaseUrlForLocationCategory($category, $url); in config/slots.conf.php');
		}
		while(ob_get_level())
			ob_end_flush();
		flush();
		
		/*********************************************************
		 * Check for a running export
		 *********************************************************/
		$dbc = Services::getService('DatabaseManager');
		$query = new SelectQuery;
		$query->addColumn('slot');
		$query->addColumn('pid');
		$query->addTable('site_export_queue');
		$query->addWhereNotEqual('pid', 0);
		$result = $dbc->query($query);
		
		// If we are exporting, check the status of the export process
		if ($result->hasMoreRows()) {
			// Don't start a new export if one is running.
			if ($this->isRunning($result->field('pid'))) {
				print "An export is already running\n";
				exit;
			}
			// Clean up if there is a pid entry, but the process died.
			else {
				$query = new UpdateQuery;
				$query->setTable('site_export_queue');
				$query->addValue('status', 'DIED');
				$query->addRawValue('pid', 'NULL');
				$query->addValue('info', 'Process '.$result->field('pid').' has died.');
				$query->addWhereEqual('slot', $result->field('slot'));
				$query->addWhereEqual('pid', $result->field('pid'));
				$dbc->query($query);
			}
		}
		
		/*********************************************************
		 * If there aren't any other exports happening, run our export
		 *********************************************************/
		// Find the next slot to update
		$query = new SelectQuery;
		$query->addColumn('slot');
		$query->addTable('site_export_queue', NO_JOIN, '', 'q');
		$query->addTable('segue_slot', INNER_JOIN, 'q.slot = s.shortname', 's');
		$query->addWhereNull('pid');
		$query->addWhereNull('status');
		$query->addWhereNull('alias_target');
		$query->addWhereNotEqual('site_id', '');
		$query->addOrderBy('priority', DESCENDING);
		$query->addOrderBy('slot', ASCENDING);
		$result = $dbc->query($query);
		// Exit if there is nothing to do.
		if (!$result->hasMoreRows()) {
			print "The queue is empty\n";
			exit;
		}
		
		$slot = $result->field('slot');
		$slotMgr = SlotManager::instance();
		$slotObj = $slotMgr->getSlotByShortname($slot);
		$baseUrl = SiteDispatcher::getBaseUrlForLocationCategory($slotObj->getLocationCategory());
				
		// Mark that we are running
		$query = new UpdateQuery;
		$query->setTable('site_export_queue');
		$query->addValue('pid', strval(getmypid()));
		$query->addWhereEqual('slot', $slot);
		$dbc->query($query);
		
		
		// Run the export
		$start = microtime(true);
		try {
			$exportDirname = $slot."-html";
			$exportDir = $outDir."/".$exportDirname;
			$archivePath = $outDir.'/'.$exportDirname.".zip";
			if (file_exists($exportDir)) {
				$this->deleteRecursive($exportDir);
			}
			mkdir($exportDir);
			if (file_exists($archivePath)) {
				unlink($archivePath);
			}
			
			// Set the user to be an admin.
			$idMgr = Services::getService("Id");
			$authType = new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB");
			$_SESSION['__AuthenticatedAgents']['Authentication::edu.middlebury.harmoni::Harmoni DB'] = $idMgr->getId('17008');
			$authZ = Services::getService("AuthZ");
			$isAuthorizedCache = $authZ->getIsAuthorizedCache();
			$isAuthorizedCache->dirtyUser();
			// Close the session. If we don't, a lock on the session file will 
			// cause the request initiated via wget to hang.
			session_write_close();
			
			// Do the export
			$urlParts = parse_url($baseUrl);
			$urlPrefix = rtrim($urlParts['path'], '/');
			$include = array(
				$urlPrefix.'/gui2', 
				$urlPrefix.'/images',
				$urlPrefix.'/javascript',
				$urlPrefix.'/polyphony',
				$urlPrefix.'/repository',
				$urlPrefix.'/plugin_manager',
				$urlPrefix.'/rss',
				$urlPrefix.'/dataport/html/site/'.$slot,
			);
			if (defined('WGET_PATH'))
				$wget = WGET_PATH;
			else
				$wget = 'wget';
			$command = $wget." -r --page-requisites --html-extension --convert-links --no-directories -e robots=off "
				."--directory-prefix=".escapeshellarg($exportDir.'/content')." "
				."--include=".escapeshellarg(implode(',', $include))." "
				."--header=".escapeshellarg("Cookie: ".session_name()."=".session_id())." "
				.escapeshellarg($baseUrl.'/dataport/html/site/'.$slot);
			
			print "Cookie: ".session_name()."=".session_id()."\n";
// 			throw new Exception($command);
			
			exec($command, $output, $exitCode);
			
			if ($exitCode) {
				throw new Exception('Wget Failed. '.implode("\n", $output));
			}
			
			// Copy the main HTML file to index.html
			copy($exportDir.'/content/'.$slot.'.html', $exportDir.'/content/index.html');
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
			
			// Mark our success
			$query = new UpdateQuery;
			$query->setTable('site_export_queue');
			$query->addRawValue('pid', 'NULL');
			$query->addValue('status', 'SUCCESS');
			$query->addValue('running_time', strval(round(microtime(true) - $start, 2)));
			$query->addWhereEqual('slot', $slot);
			$dbc->query($query);
			
		} catch (Exception $e) {
			$this->deleteRecursive($exportDir);
			
			if (file_exists($archivePath))
				unlink($archivePath);
			
			// Mark our failure
			$query = new UpdateQuery;
			$query->setTable('site_export_queue');
			$query->addRawValue('pid', 'NULL');
			$query->addValue('status', 'EXCEPTION');
			$query->addValue('info', $e->getMessage());
			$query->addValue('running_time', strval(round(microtime(true) - $start, 2)));
			$query->addWhereEqual('slot', $slot);
			$dbc->query($query);
			
			throw $e;
		}
		
		exit;
	}
	
	/**
	 * Check if a process is running.
	 * From: http://stackoverflow.com/a/45966/15872
	 * 
	 * @param int $pid
	 * @return boolean
	 */
	public function isRunning($pid){
		try{
			$result = shell_exec(sprintf("ps %d", $pid));
			if( count(preg_split("/\n/", $result)) > 2){
				return true;
			}
		}catch(Exception $e){}
		
		return false;
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
