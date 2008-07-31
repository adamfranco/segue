<?php
/**
 * @since 3/31/08
 * @package segue.modules.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteDispatcher.class.php,v 1.6 2008/04/11 17:11:46 adamfranco Exp $
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
 * @version $Id: SiteDispatcher.class.php,v 1.6 2008/04/11 17:11:46 adamfranco Exp $
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
			
			if (RequestContext::value("node")) {
				$nodeId = RequestContext::value("node");
			} else if (RequestContext::value("site")) {
				$slotManager = SlotManager::instance();
				$slot = $slotManager->getSlotByShortname(RequestContext::value("site"));
				if ($slot->siteExists())
					$nodeId = $slot->getSiteId()->getIdString();
				else {
					$harmoni->request->endNamespace();
					throw new UnknownIdException("A Site has not been created for the slotname '".$slot->getShortname()."'.");
				}
			}
			
			if (!isset($nodeId) || !$nodeId) {
				$harmoni->request->endNamespace();
				throw new NullArgumentException('No site node specified.');
			}
				
			self::$currentNodeId = $nodeId;
			
			$harmoni->request->endNamespace();
		}
		
		return self::$currentNodeId;
	}
	
	/**
	 * Answer a url string with the parameters passed, ensuring that the current node
	 * id is passed as well.
	 * 
	 * @param optional string $module
	 * @param optional string $action
	 * @param optional array $params
	 * @return string
	 * @access public
	 * @since 4/9/08
	 * @static
	 */
	public static function quickURL ($module = null, $action = null, array $params = null) {
		$url = self::mkURL($module, $action, $params);
		return $url->write();
	}
	
	/**
	 * Answer a URL writer object with the parameters passed, ensuring that the current node
	 * id is passed as well.
	 * 
	 * @param optional string $module
	 * @param optional string $action
	 * @param optional array $params
	 * @return object URLWriter
	 * @access public
	 * @since 4/9/08
	 * @static
	 */
	public static function mkURL ($module = null, $action = null, array $params = null) {
		if (is_null($params))
			$params = array();
		
		$context = self::getContext($params);
		unset($params['node'], $params['site']);
		$harmoni = Harmoni::instance();
		
		if (!count($params))
			$params = null;
		
		$url = $harmoni->request->mkURL($module, $action, $params);
		
		$harmoni->request->startNamespace(null);
		foreach ($context as $key => $val) {
			$url->setValue($key, $val);
		}
		$harmoni->request->endNamespace();
		return $url;
	}
	
	/**
	 * Set the context data to pass-through to subsequent urls.
	 * 
	 * @return void
	 * @access public
	 * @since 4/9/08
	 * @static
	 */
	public static function passthroughContext () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$harmoni->request->set('node', self::getCurrentNodeId());
		$harmoni->request->endNamespace();


// 		$context = self::getContext();
// 		$harmoni = Harmoni::instance();
// 		$harmoni->request->startNamespace(null);
// 		foreach ($context as $key => $val)
// 			$harmoni->request->passthrough($key);
// 		$harmoni->request->endNamespace();
	}
	
	/**
	 * Unset the context data from being passed-through to subsequent urls.
	 * 
	 * @return void
	 * @access public
	 * @since 4/9/08
	 * @static
	 */
	public static function forgetContext () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$harmoni->request->forget('node');
		$harmoni->request->endNamespace();
		
// 		$context = self::getContext();
// 		$harmoni = Harmoni::instance();
// 		$harmoni->request->startNamespace(null);
// 		foreach ($context as $key => $val)
// 			$harmoni->request->forget($key);
// 		$harmoni->request->endNamespace();
	}
	
	/**
	 * Answer an array of key/value pairs that define the current context.
	 * 
	 * @return array
	 * @access private
	 * @since 4/9/08
	 * @public
	 */
	public static function getContext (array $params = null) {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		// Determine the node id or site id to use
		if (isset($params['node'])) {
			$nodeKey = 'node';
			$nodeVal = $params['node'];
		} else if (isset($params['site'])) {
			$nodeKey = 'site';
			$nodeVal = $params['site'];
		} else if (RequestContext::value("site")) {
			$nodeKey = 'site';
			$nodeVal = RequestContext::value("site");
		} else {
			$nodeKey = 'node';
			$nodeVal = self::getCurrentNodeId();
		}
		$harmoni->request->endNamespace();
		
		return array($nodeKey => $nodeVal);
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
	
	/**
	 * Answer the shortened /sites/slotname url for a site id.
	 * 
	 * @param string $siteId
	 * @return string
	 * @access public
	 * @since 7/30/08
	 * @static
	 */
	public static function getSitesUrlForSiteId ($siteId) {
		$slotMgr = SlotManager::instance();
		try {
			$slot = $slotMgr->getSlotBySiteId($siteId);
			return rtrim(MYURL, '/').'/sites/'.$slot->getShortname();
		} catch (UnknownIdException $e) {
			$harmoni = Harmoni::instance();
			return $harmoni->request->quickURL('view', 'html', array('node' => $siteId));
		}
	}
}

?>