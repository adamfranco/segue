<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.13 2006/01/25 20:03:23 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/NavigationNodeRenderer.class.php");
require_once(dirname(__FILE__)."/SiteNodeRenderer.class.php");
require_once(dirname(__FILE__)."/PluginNodeRenderer.class.php");
require_once(dirname(__FILE__)."/GenericNodeRenderer.class.php");
require_once(HARMONI."GUIManager/Components/MenuItem.class.php");


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
 * @version $Id: NodeRenderer.abstract.php,v 1.13 2006/01/25 20:03:23 adamfranco Exp $
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
	 * @param object NavigationNodeRenderer $parent
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/19/06
	 */
	function &forAsset ( &$asset, &$parent ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		ArgumentValidator::validate($parent, OptionalRule::getRule(
			ExtendsValidatorRule::getRule("NavigationNodeRenderer")));

		$id =& $asset->getId();
		if (!isset($GLOBALS['node_renderers'][$id->getIdString()])) {
			$plugs =& Services::getService("Plugs");
			
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
			else if ($plugs->isPluginDomain(strtolower($type->getDomain())))
				$renderer =& new PluginNodeRenderer;
			else
				$renderer =& new GenericNodeRenderer;
			
			$renderer->_setAsset($asset);
			
			// Get the parent if we weren't passed it.
			// Note that this implies a single-parent hierarchy
			if ($parent === null && !$type->isEqual($siteType)) {
				$parents =& $asset->getParents();
				if ($parents->hasNext())
					$parent =& NodeRenderer::forAsset($parents->next(), $null = null);
			}
			
			if ($parent !== null)
				$renderer->_setParent($parent);
			
			if (!isset($GLOBALS['node_renderers']))
				$GLOBALS['node_renderers'] = array();
				
			$GLOBALS['node_renderers'][$id->getIdString()] =& $renderer;
			
			
			if (in_array($id->getIdString(), NodeRenderer::getActiveNodes()))
				$renderer->setActive(true);
		}
		
		return $GLOBALS['node_renderers'][$id->getIdString()];
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
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
			
			
			$GLOBALS['active_nodes'] = array();
			if (isset($_REQUEST['node']) && $_REQUEST['node']) {
				$GLOBALS['active_nodes'][] = $_REQUEST['node'];
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['node']));
				NodeRenderer::traverseActiveUp($asset);
				$renderer =& NodeRenderer::forAsset($asset, $null = null);
				$renderer->traverseActiveDown();
			} else {
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['site_id']));
				$renderer =& NodeRenderer::forAsset($asset, $null = null);
				$renderer->traverseActiveDown();
			}
			
			$GLOBALS['active_nodes'] = array_unique($GLOBALS['active_nodes']);
			
// 			printpre($GLOBALS['active_nodes']);
		}
		
		return $GLOBALS['active_nodes'];
	}
	
	/**
	 * Add parents to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 * @static
	 */
	function traverseActiveUp ($asset) {
		$type =& $asset->getAssetType();
		$siteType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'site');
		$id =& $asset->getId();
		$GLOBALS['active_nodes'][] = $id->getIdString();
		
		if ($type->isEqual($siteType))
			return;
		
		$parents =& $asset->getParents();
		while ($parents->hasNext())
			NodeRenderer::traverseActiveUp($parents->next());
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
	
	/**
	 * @var array $_childRenderers;  
	 * @access private
	 * @since 1/25/06
	 */
	var $_childRenderers = array();

/*********************************************************
 * Instance Methods - Public
 *********************************************************/
 	
 	/**
	 * Create a NodeRenderer instance for a child asset
	 * 
	 * @param object Asset $asset
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/19/06
	 */
	function &getRendererForChildAsset ( &$asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		
		$id =& $asset->getId();
// 		if (!isset($this->_childRenderers[$id->getIdString()]))
			$this->_childRenderers[$id->getIdString()] =& NodeRenderer::forAsset($asset, $this);
		
		return $this->_childRenderers[$id->getIdString()];
	}
	
	/**
	 * Add first children to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return boolean true if this node is a navigation node.
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveDown () {
		$type =& $this->_asset->getAssetType();
		$navType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'navigation');
		$siteType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'site');
								
		if (!$type->isEqual($navType) && !$type->isEqual($siteType))
			return false;
			
		$id =& $this->getId();
		$GLOBALS['active_nodes'][] = $id->getIdString();
		
		// Traverse down just the first children
		$orderedChildren =& $this->getOrderedChildren();
		$childNavFound = false;
		foreach (array_keys($orderedChildren) as $key) {
			$childNavFound = $orderedChildren[$key]->traverseActiveDown();
			if ($childNavFound)
				break;
		}
		return true;
	}
	
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
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
	 * Answer the title that should be displayed for this node.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getTitle () {
		return $this->_asset->getDisplayName();
	}
	
	/**
	 * Answer the Id of this NodeRenderer
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/06
	 */
	function &getId () {
		$id =& $this->_asset->getId();
		return $id;
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
	 * Answer a html block to modify this node. Pass array of extra links to print
	 * 
	 * @param array $links Name => URL
	 * @return string
	 * @access public
	 * @since 1/23/06
	 */
	function getSettingsForm ($links = array()) {
		$harmoni = Harmoni::instance();
		$id =& $this->getId();
		$idString = $id->getIdString();
		$parentId =& $this->_parent->getId();
		$parentIdString = $parentId->getIdString();
			
		
		ob_start();
		print "\n<div id='options:".$id->getIdString()."'";
		print " style='text-align: right;'>";
		
		print "\n\t<div";
		print " onclick='this.nextSibling.nextSibling.style.display=\"block\"; this.style.display=\"none\";'";
		print "style='text-align: right; padding: 3px; position: relative'>";
		print "\n\t\t<div style='border: 1px solid; text-align: center; width: 15px; height: 15px; position: absolute; right: 2px; cursor: pointer; cursor: hand;'";
		print " title='"._('show options')."'";
		print ">";
		print _('+');
		print "</div> &nbsp;";
		print "</div>";
		
		print "\n\t<div style='border: 1px solid; padding: 2px; text-align: left; display: none; position: relative'>";
		print "\n\t\t<div style='border: 1px solid; text-align: center; width: 15px; height: 15px; position: absolute; right: 2px; cursor: pointer; cursor: hand;'";
		print " onclick='this.parentNode.previousSibling.previousSibling.style.display=\"block\"; this.parentNode.style.display=\"none\";'";
		print " title='"._('close options')."'";
		print ">";
		print _('x');
		print "</div> ";
		print "\n\t\t<div >";
		
		/*********************************************************
		 * Order buttons
		 *********************************************************/
		printpre(get_class($this));
		printpre('parent: '.get_class($this->_parent));
		$siblingIds = $this->_parent->getOrderedChildIds();
		$myPosition = array_search($idString, $siblingIds);
		print "\n\t\t\t"._('Order: ')." ";
		print "\n\t\t<span style='white-space: nowrap; padding-left: 5px; padding-right: 5px;'>";
		// Move 1 previous
		if ($myPosition > 0) {
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('site', 'reorder', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'before' => $siblingIds[$myPosition - 1]));
			print "'>&lt;--</a>";
		} else {
			print "\n\t\t\t&lt;--";
		}
		
		// Reorder select field
		print "\n\t\t\t<select onchange='if (this.value) {alert(this.value);} else {alert(\""._("Already in this position.")."\");}'>";
		print "\n\t\t\t\t<option value=''>"._("Position Before...")."</option>";
		$parentsChildren =& $this->_parent->getOrderedChildren();
		$i = 1;
		$thisNum = false;
		foreach (array_keys($parentsChildren) as $key) {
			$renderer =& $parentsChildren[$key];
			$assetId =& $renderer->getId();
			$idString = $assetId->getIdString();
			
			if ($assetId->isEqual($this->getId()))
				$thisNum = $i;
			
			print "\n\t\t\t\t<option";
			if ($thisNum === $i || $thisNum === $i-1)
				print " value='' style='background-color: #ddd;'";
			else
				print " value='".$idString."'";
			print ">";
			if ($thisNum === $i) 
				print "*";			
			print $i.": ".$renderer->getTitle();
			print "</option>";
			$i++;
		}
		if ($thisNum === $i || $thisNum === $i-1)
			print "\n\t\t\t\t<option value='' style='background-color: #ddd;'>"._("At End")."</option>";
		else
			print "\n\t\t\t\t<option value='end'>"._("At End")."</option>";
		print "\n\t\t\t</select>";
		
		// Move 1 next
		if ($myPosition < count($siblingIds) - 1) {
			if (isset($siblingIds[$myPosition + 2]))
				$nextId = $siblingIds[$myPosition + 2];
			else
				$nextId = 'end';
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('site', 'reorder', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'before' => $nextId));
			print "'>--&gt;</a>";
		} else {
			print "\n\t\t\t--&gt;";
		}
		print "\n\t\t</span>";
		
		/*********************************************************
		 * Cell position
		 *********************************************************/
		$layout = $this->_parent->getLayoutArrangement();
		if ($layout == 'columns')
			print "\n\t\t\t<br/>Column: &nbsp;";
		if ($layout == 'rows')
			print "\n\t\t\t<br/>Row: &nbsp;";
			
		if ($layout == 'columns' || $layout == 'rows') {
			$currentColumn = $this->_parent->getDestinationCell($id);
			print "\n\t\t\t<select onchange='if (this.value != ".$currentColumn.") {alert(this.value);} else {alert(\""._("Already in this position.")."\");}'>";
			for ($i = 1; $i <= $this->_parent->getNumCells(); $i++) {
				print "\n\t\t\t\t<option";
				if ($currentColumn == $i)
					print " style='background-color: #ddd;'";
				print " value='".$i."'>".$i."</option>";
			}
			print "\n\t\t\t</select>";
		}
		
		/*********************************************************
		 * Other links
		 *********************************************************/
		$links[_('settings')] = $harmoni->request->quickURL('site', 'edit', 
								array('site_id' => RequestContext::value('site_id'),
									'node' => $id->getIdString()));
		array_walk($links, 
			create_function('&$url,$name', '$url = "<a href=\'$url\'>$name</a>";'));
		
		print "\n\t\t\t<br/>";
		print implode("\n\t\t | ", $links);
		print "\n\t\t</div>";
		print "\n\t</div>";
		
		print "\n</div>";
		return ob_get_clean();
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
	
	/**
	 * Set the parent of this renderer
	 * 
	 * @param object NodeRenderer $parent
	 * @return void
	 * @access private
	 * @since 1/19/06
	 */
	function _setParent ( &$parent ) {
		$this->_parent =& $parent;
	}
}

?>