<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.7 2006/01/23 20:34:24 adamfranco Exp $
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
 * @version $Id: NodeRenderer.abstract.php,v 1.7 2006/01/23 20:34:24 adamfranco Exp $
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
	function &forAsset ( &$asset, &$parent ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		ArgumentValidator::validate($parentId, OptionalRule::getRule(
			ExtendsValidatorRule::getRule("Id")));
		
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
		if ($parent !== null) {
			$renderer->_setParent($parent);
		}
		
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
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
			
			
			$GLOBALS['active_nodes'] = array();
			if (isset($_REQUEST['node']) && $_REQUEST['node']) {
				$GLOBALS['active_nodes'][] = $_REQUEST['node'];
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['node']));
				NodeRenderer::traverseActiveUp($asset);
				NodeRenderer::traverseActiveDown($asset);
			} else {
				$asset =& $repository->getAsset($idManager->getId($_REQUEST['site_id']));
				NodeRenderer::traverseActiveDown($asset);
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
	
	/**
	 * Add first children to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return boolean true if this node is a navigation node.
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveDown ($asset) {
		$type =& $asset->getAssetType();
		$navType =&  new Type('site_components', 
								'edu.middlebury.segue', 
								'navigation');
								
		if (!$type->isEqual($navType))
			return false;
			
		$id =& $asset->getId();
		$GLOBALS['active_nodes'][] = $id->getIdString();
		
		// Traverse down just the first children
		$children =& $asset->getAssets();
		$childNavFound = false;
		while ($children->hasNext() && !$childNavFound) {
			$childNavFound = NodeRenderer::traverseActiveDown($children->next());
		}
		return true;
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
	 * Answer a string of links to modify this node
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/06
	 */
	function getSettingsForm () {
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
		$siblingIds = $this->_parent->getOrderedChildIds();
		$myPosition = array_search($idString, $siblingIds);
		print "\n\t\t\t"._('Order: ')." &nbsp;";
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
			$asset =& $parentsChildren[$key];
			$assetId =& $asset->getId();
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
			print $i.": ".$asset->getDisplayName();
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