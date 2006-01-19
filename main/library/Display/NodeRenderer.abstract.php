<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.2 2006/01/19 21:31:50 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/NavigationNodeRenderer.class.php");
require_once(dirname(__FILE__)."/SiteNodeRenderer.class.php");
require_once(dirname(__FILE__)."/PluginNodeRenderer.class.php");
require_once(dirname(__FILE__)."/GenericNodeRenderer.class.php");


/**
 * The Node Render class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.2 2006/01/19 21:31:50 adamfranco Exp $
 */
class NodeRenderer {


/*********************************************************
 * Class Methods - instance Creation
 *********************************************************/ 
	/**
	 * Create a NodeRenderer instance for an asset. Use this instead of
	 * '$obj =& new nodeRenderer()'
	 * 
	 * @param object Asset $asset
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/19/06
	 */
	function &forAsset ( &$asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		
		$type =& $asset->getAssetType();
		$siteType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'site');
		$navType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'navigation');
		
		if ($type->isEqual($siteType))
			$renderer =& new SiteNodeRenderer;
		else if ($type->isEqual($navType))
			$renderer =& new NavigationNodeRenderer;
		else if (strtolower($type->getDomain()) == 'plugins')
			$renderer =& new PluginNodeRenderer;
		else
			$renderer =& new GenericNodeRenderer;
		
		$renderer->_setAsset($asset);
		
		$assetId =& $asset->getId();
		if (in_array($assetId->getIdString(), NodeRenderer::getActiveNodes()))
			$renderer->setActive(true);
		
		return $renderer;
	}

/*********************************************************
 * Class Methods - other
 *********************************************************/
	
	/**
	 * Answer an array of active Nodes
	 * 
	 * @return array
	 * @access public
	 * @since 1/19/06
	 */
	function getActiveNodes () {
		if (!isset($GLOBALS['active_nodes'])) {
			$GLOBALS['active_nodes'] = array();
			if (isset($_REQUEST['node']) && $_REQUEST['node']) {
				$GLOBALS['active_nodes'][] = $_REQUEST['node'];
			} else {
				
			}
		}
		
		return $GLOBALS['active_nodes'];
	}

/*********************************************************
 * Object properties
 *********************************************************/
	
	/**
	 * @var object Asset $_asset;  
	 * @access private
	 * @since 1/19/06
	 */
	var $_asset;
	
	/**
	 * @var boolean $_active; 
	 * @access private
	 * @since 1/19/06
	 */
	var $_active = false;

/*********************************************************
 * Instance Methods - Public
 *********************************************************/
	
	/**
	 * Set the active-state of the Renderer.
	 * 
	 * @param optional boolean $isActive
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function setActive ( $isActive = true ) {
		$this->_active = $isActive;
	}
	
	/**
	 * Get the active-state of the Renderer.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/19/06
	 */
	function isActive () {
		return $this->_active;
	}
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		$component =& new MenuItemLink(
						$this->_asset->getDisplayName(), 
						$this->getMyUrl(), 
						$this->_active,
						$level);
						
		return $component;
	}
	
	/**
	 * Answer the GUI component for target area
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderTargetComponent ($level = 1) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Answer the url to this Node
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getMyUrl () {
		$id =& $this->_asset->getId();
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL('site', 'view', 
					array(	'site_id' => RequestContext::value('site_id'),
							'node' => $id->getIdString()));
	}
	
	/**
	 * Answer the desired cell in which to place this asset's navegation component
	 * 
	 * @return integer
	 * @access public
	 * @since 1/19/06
	 */
	function getDestination () {
		return 1;
	}
	
/*********************************************************
 * Instance Methods - Private
 *********************************************************/
	/**
	 * Set the asset of this renderer
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access private
	 * @since 1/19/06
	 */
	function _setAsset ( &$asset ) {
		$this->_asset =& $asset;
	}
}

?>