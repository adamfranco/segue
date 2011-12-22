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
class check_html_exportAction
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

			if (!file_exists($exportDir)) {
				header('HTTP/1.1 404 Not Found');
				print "Not Found";
				exit;
			}
			
			header("Content-Type: text/html;");
			$this->printStatus($exportDir);
			
		} catch (PermissionDeniedException $e) {
			header('HTTP/1.1 403 Forbidden');
			header("Content-Type: text/plain;");
			print _("You are not authorized to monitor the export this component.");
			exit;
		}
		
		exit;
	}
	
	/**
	 * Print out status markers for each file.
	 * 
	 * @param $dir
	 * @return void
	 */
	protected function printStatus ($dir) {
		foreach (scandir($dir) as $file) {
			if (is_dir($dir.'/'.$file) && !preg_match('/^\./', $file))
				$this->printStatus($dir.'/'.$file);
			else
				print '&#8226; ';
			
		}
	}
}
