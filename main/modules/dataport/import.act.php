<?php
/**
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.3 2008/01/24 17:07:28 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(dirname(__FILE__)."/Rendering/DomImportSiteVisitor.class.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");

/**
 * This action will import a site into the slot-name given.
 * 
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.3 2008/01/24 17:07:28 adamfranco Exp $
 */
class importAction
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/22/08
	 */
	public function isAuthorizedToExecute () {
		$slotMgr = SlotManager::instance();
		$slot = $slotManager->getSlotByShortname(RequestContext::value('site'));
		if ($slot->isUserOwner())
			return true;
		else
			return false;
	}
	
	/**
	 * Execute this action
	 *
	 * @return mixed
	 * @access public
	 * @since 1/22/08
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
		
		$director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));
		
		$doc = new Harmoni_DOMDocument;
		$doc->load(MYDIR."/main/modules/dataport/test/afranco-test/site.xml");
		$mediaDir = MYDIR."/main/modules/dataport/test/afranco-test";
		
		$importer = new DomImportSiteVisitor($doc, $mediaDir, $director);
		$importer->importAtSlot(RequestContext::value('site'));
	}
	
}

?>