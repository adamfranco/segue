<?php
/**
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.3 2008/01/28 19:54:29 adamfranco Exp $
 */ 

require_once("Archive/Tar.php");
require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(dirname(__FILE__)."/Rendering/DomExportSiteVisitor.class.php");


/**
 * This action will export a site to an xml file
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.3 2008/01/28 19:54:29 adamfranco Exp $
 */
class exportAction
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/08
	 */
	public function isAuthorizedToExecute () {
		// Authorization checks are handled in the DOMExportSiteVisitor, so just
		// return true for the action as a whole.
		return true;
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 1/17/08
	 */
	public function execute () {
		$harmoni = Harmoni::instance();
		/*********************************************************
		 * XML Version
		 *********************************************************/
// 		$testDocument = new DOMIT_Document();
// 		$testDocument->setNamespaceAwareness(true);
// 		$success = $testDocument->loadXML(MYDIR."/main/library/SiteDisplay/test/testSite.xml");
// 
// 		if ($success !== true) {
// 			throwError(new Error("DOMIT error: ".$testDocument->getErrorCode().
// 				"<br/>\t meaning: ".$testDocument->getErrorString()."<br/>", "SiteDisplay"));
// 		}
// 
// 		$director = new XmlSiteDirector($testDocument);
// 		
// 		if (!$nodeId = RequestContext::value("node"))
// 			$nodeId = "1";

		/*********************************************************
		 * Asset version
		 *********************************************************/
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$this->_director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));			
		
		if (!$nodeId = $this->getNodeId())
			throwError(new Error('No site node specified.', 'SiteDisplay'));
		
		$component = $this->_director->getSiteComponentById($nodeId);
		$site = $this->_director->getRootSiteComponent($nodeId);
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($site->getId());
		
		$exportDir = DATAPORT_TMP_DIR."/".$slot->getShortname()."-".str_replace(':', '_', DateAndTime::now()->asString());
		mkdir($exportDir);
		
		try {
			$visitor = new DomExportSiteVisitor($exportDir);
			$component->acceptVisitor($visitor);
			$visitor->doc->save($exportDir."/site.xml");		
		
			$archive = new Archive_Tar($exportDir.".tar.gz");
			$archive->createModify($exportDir, '', DATAPORT_TMP_DIR);
			
			// Remove the directory
			$this->deleteRecursive($exportDir);
			
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
	 * @since 7/30/07
	 */
	function getNodeId () {
		if (RequestContext::value("site")) {
			$slotManager = SlotManager::instance();
			$slot = $slotManager->getSlotByShortname(RequestContext::value("site"));
			if ($slot->siteExists())
				$nodeId = $slot->getSiteId()->getIdString();
			else
				throw new UnknownIdException("A Site has not been created for the slotname '".$slot->getShortname()."'.");
		} else if (RequestContext::value("node")) {
			$nodeId = RequestContext::value("node");
		}
		
		if (!$nodeId)
			throwError(new Error('No site node specified.', 'SiteDisplay'));
		
		return $nodeId;
	}
	
	/**
	 * Recursively delete a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 * @since 1/18/08
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