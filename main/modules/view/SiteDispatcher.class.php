<?php
/**
 * @since 3/31/08
 * @package segue.modules.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteDispatcher.class.php,v 1.3 2008/03/31 23:03:54 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");

/**
 * This class includes a number of static accessor methods for getting the
 * current SiteDisplay Director and currently requested node. This functionality
 * is placed here as it is used by a number of different classes that work with sites
 * in the same way. All methods are static
 * 
 * @since 3/31/08
 * @package segue.modules.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteDispatcher.class.php,v 1.3 2008/03/31 23:03:54 adamfranco Exp $
 */
class SiteDispatcher {
		
	/**
	 * @var object SiteComponent $currentNode;  
	 * @access private
	 * @since 3/31/08
	 * @static
	 */
	private static $currentNode;
	
	/**
	 * @var string $currentNodeId;  
	 * @access private
	 * @since 3/31/08
	 * @static
	 */
	private static $currentNodeId;
	
	/**
	 * @var object SiteDirector $director;  
	 * @access private
	 * @since 3/31/08
	 * @static
	 */
	private static $director;
	
	/**
	 * @var object SiteNavBlockSiteComponent $rootSiteComponent;  
	 * @access private
	 * @since 3/31/08
	 * @static
	 */
	private static $rootSiteComponent;
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 * @static
	 */
	public static function getCurrentNodeId () {
		if (!isset(self::$currentNodeId)) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace(null);
			
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
			
			if (!isset($nodeId) || !$nodeId)
				throw new NullArgumentException('No site node specified.');
				
			self::$currentNodeId = $nodeId;
			
			$harmoni->request->endNamespace();
		}
		
		return self::$currentNodeId;
	}
	
	/**
	 * Answer the current node
	 *
	 * @return object SiteComponent
	 * @access public
	 * @since 3/31/08
	 */
	public static function getCurrentNode () {
		if (!isset(self::$currentNode)) {
			$nodeId = self::getCurrentNodeId();
				
			self::$currentNode = self::getSiteDirector()->getSiteComponentById(self::getCurrentNodeId());
		}
		
		return self::$currentNode;
	}
	
	/**
	 * Answer the site node above the current Node
	 *
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 3/31/08
	 */
	public static function getCurrentRootNode () {
		if (!isset(self::$rootSiteComponent))
			self::$rootSiteComponent = self::getSiteDirector()->getRootSiteComponent(self::getCurrentNodeId());
		
		return self::$rootSiteComponent;
	}
	
	/**
	 * Answer the Site Director
	 *
	 * @return object SiteDirector
	 * @access public
	 * @since 3/31/08
	 */
	public static function getSiteDirector () {
		if (!isset(self::$director)) {
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
			
			self::$director = new AssetSiteDirector(
				$repositoryManager->getRepository(
					$idManager->getId('edu.middlebury.segue.sites_repository')));	
		}
		
		return self::$director;
	}
}

?>