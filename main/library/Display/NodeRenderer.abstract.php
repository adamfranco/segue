<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NodeRenderer.abstract.php,v 1.24 2006/02/01 17:18:49 adamfranco Exp $
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
 * @version $Id: NodeRenderer.abstract.php,v 1.24 2006/02/01 17:18:49 adamfranco Exp $
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
		if (!isset($GLOBALS['active_traversed'])) {
			$GLOBALS['active_traversed'] = true;
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));			
			
			$asset =& $repository->getAsset($idManager->getId($_REQUEST['node']));
			$renderer =& NodeRenderer::forAsset($asset, $null = null);
			$renderer->traverseActiveUp();
			$renderer->traverseActiveDown();
		}
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
		
		return NodeRenderer::forAsset($asset, $this);
	}
	
	/**
	 * Add parents to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveUp () {
		$this->setActive();
		
		$parents =& $this->_asset->getParents();
		while ($parents->hasNext()) {
			$parentRenderer =& NodeRenderer::forAsset($parents->next(), $null = null);
			$parentRenderer->traverseActiveUp();
		}
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
		return false;
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
		NodeRenderer::getActiveNodes();
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
					array('node' => $id->getIdString()));
	}
	
	/**
	 * Answer the GUI component for the contents of the site that this node
	 * is in.
	 * 
	 * @return object Component
	 * @access public
	 * @since 1/26/06
	 */
	function &renderSite () {
		return $this->_parent->renderSite();
	}
	
	/**
	 * Answer the site title is in.
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function getSiteTitle () {
		return $this->_parent->getSiteTitle();
	}
	
	/**
	 * Answer the top-level NodeRenderer
	 * 
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/26/06
	 */
	function &getSiteRenderer () {
		$siteRenderer =& $this->_parent->getSiteRenderer();
		return $siteRenderer;
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
		
		$this->printOptionJS();
		
		print "\n<div id='options:".$id->getIdString()."'";
		print " style='text-align: right;'>";
		
		print "\n\t<div id='options_button:".$id->getIdString()."'";
		print " style='text-align: right; padding: 3px; position: relative'>";
		print "\n\t\t<div";
		print " onclick='showOptions(\"".$id->getIdString()."\")'";
		print  " style='border: 1px solid; text-align: center; width: 15px; height: 15px; position: absolute; right: 2px; cursor: pointer; cursor: hand;'";
		print " title='"._('show options')."'";
		print ">";
		print _('+');
		print "</div>\n\t\t&nbsp;";
		print "\n\t</div>";
		
		print "\n\t<div id='options_form:".$id->getIdString()."'";
		print " style='border: 1px solid; padding: 2px; text-align: left; display: none; position: relative'>";
		print "\n\t\t<div style='border: 1px solid; text-align: center; width: 15px; height: 15px; position: absolute; right: 2px; cursor: pointer; cursor: hand;'";
		print " onclick='hideOptions(\"".$id->getIdString()."\")'";
		print " title='"._('close options')."'";
		print ">";
		print _('x');
		print "</div> ";
		print "\n\t\t<div >";
		
		
		$this->printOptionOrderForm();
		
		$this->printOptionCellPositionForm();
		
		$this->printOptionAddChildForm();
		
		/*********************************************************
		 * Other links
		 *********************************************************/
		$links[_('settings')] = $harmoni->request->quickURL('site', 'edit', 
								array('node' => $id->getIdString(),
									'return_node' => RequestContext::value('node')));
		array_walk($links, 
			create_function('&$url,$name', '$url = "<a href=\'$url\'>$name</a>";'));
		
		print "\n\t\t\t<div>";
		print implode("\n\t\t | ", $links);
		print "\n\t\t\t<div>";
		print "\n\t\t</div>";
		print "\n\t</div>";
		
		print "\n</div>";
		return ob_get_clean();
	}
	
	/**
	 * print the Javascript for the Options form
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionJS () {
		$cellConfirmMessage = _('Do you want to move this item to this cell?');
		print<<<END

			<script type='text/javascript'>
			/* <![CDATA[ */
			
				function showOptions (id) {
					var optionsForm = getElementFromDocument('options_form:' + id);
					var optionsButton = getElementFromDocument('options_button:' + id);
// 					alert('optionsForm.style.display = ' + optionsForm.style.display);
// 					alert('optionsForm.innerHTML = ' + optionsForm.innerHTML);
					optionsForm.style.display = 'block';
// 					alert('optionsForm.style.display = ' + optionsForm.style.display);
					optionsButton.style.display = 'none';
				}
				
				function hideOptions (id) {
					var optionsForm = getElementFromDocument('options_form:' + id);
					var optionsButton = getElementFromDocument('options_button:' + id);
					optionsForm.style.display = 'none';
					optionsButton.style.display = 'block';
				}
				
				function goToValueInserted(url, value) {
					var url = url.replace(/&amp;/gi, '&');
					url = url.replace(/______/gi, escape(value));
					window.location = url;
				}
				
				function changeCell(url, selectElement, currentCell, parentId) {
					var destinationId = parentId + '-cell-' + selectElement.value;
					var destinationElement = getElementFromDocument(destinationId);
					var flash = new BorderFlash(destinationElement);
					flash.start();
					
					if (confirm("$cellConfirmMessage")) {
						//flash.stop();
						goToValueInserted(url, selectElement.value);
					} else {
						flash.stop();
						for (var i = 0; i < selectElement.options.length; i++) {
							if (selectElement.options[i].value == currentCell) {
								selectElement.selectedIndex = i;
								break;
							}
						}
					}
				}
				
				function BorderFlash (element) {
					this.element = element;
					this.oldBorder = this.element.style.border;
					
					BorderFlash.prototype.start = function () {
						BorderFlash.doFlash(this.element.id);
						this.intervalId = setInterval(
							'BorderFlash.doFlash("' + this.element.id + '");',
							500);
					}
					
					BorderFlash.doFlash = function (elementId) {
						var element = getElementFromDocument(elementId);
						switch (element.flashStep) {
							case 1:
								element.style.border = '2px dotted red';
								element.flashStep = 2;
								break;
							case 2:
								element.style.border = '2px dashed red';
								element.flashStep = 1;
								break;
							default:
								element.style.border = '2px solid red';
								element.flashStep = 1;
						}
					}
					
					BorderFlash.prototype.stop  = function () {
						clearInterval(this.intervalId);
						if (this.oldBorder)
							this.element.style.border = this.oldBorder;
						else
							this.element.style.border = '0px';
						
						element.flashStep = null;
					}
				}
				
				/**
				 * Answer the element of the document by id.
				 * 
				 * @param string id
				 * @return object The html element
				 * @access public
				 * @since 8/25/05
				 */
				function getElementFromDocument(id) {
					// Gecko, KHTML, Opera, IE6+
					if (document.getElementById) {
						return document.getElementById(id);
					}
					// IE 4-5
					if (document.all) {
						return document.all[id];
					}			
				}
				
			/* ]]> */
			</script>
			
END;
	}
	
	/**
	 * print the order form elements
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionOrderForm () {
		$harmoni = Harmoni::instance();
		$id =& $this->getId();
		$idString = $id->getIdString();
		$parentId =& $this->_parent->getId();
		$parentIdString = $parentId->getIdString();
		
		$siblingSet = $this->_parent->getChildOrder();
		$myPosition = $siblingSet->getPosition($id);
		print "\n\t\t\t"._('Order: ')." ";
		print "\n\t\t<div style='white-space: nowrap; padding-left: 5px; padding-right: 5px;'>";
		// Move 1 previous
		if ($myPosition > 0) {
			$previousId =& $siblingSet->atPosition($myPosition - 1);
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('site', 'reorder', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'before' => $previousId->getIdString(),
									'return_node' => RequestContext::value('node')));
			print "'>&lt;--</a>";
		} else {
			print "\n\t\t\t&lt;--";
		}
		
		// Reorder select field
		$url = $harmoni->request->quickURL('site', 'reorder', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'before' => '______',
									'return_node' => RequestContext::value('node')));
		print "\n\t\t\t<select onchange='if (this.value) {goToValueInserted(\"".$url."\", this);} else {alert(\""._("Already in this position.")."\");}'>";
		print "\n\t\t\t\t<option value=''>"._("Position Before...")."</option>";
		$parentsChildren =& $this->_parent->getOrderedChildren();
		$i = 1;
		$thisNum = false;
		foreach (array_keys($parentsChildren) as $key) {
			$renderer =& $parentsChildren[$key];
			$assetId =& $renderer->getId();
			$beforeIdString = $assetId->getIdString();
			
			if ($assetId->isEqual($this->getId()))
				$thisNum = $i;
			
			print "\n\t\t\t\t<option";
			if ($thisNum === $i || $thisNum === $i-1)
				print " value='' style='background-color: #ddd;'";
			else
				print " value='".$beforeIdString."'";
			print ">";
			if ($thisNum === $i) 
				print "*";			
			print $i.": ".strip_tags($renderer->getTitle());
			print "</option>";
			$i++;
		}
		if ($thisNum === $i || $thisNum === $i-1)
			print "\n\t\t\t\t<option value='' style='background-color: #ddd;'>"._("At End")."</option>";
		else
			print "\n\t\t\t\t<option value='end'>"._("At End")."</option>";
		print "\n\t\t\t</select>";
		
		// Move 1 next
		if ($myPosition < ($siblingSet->count() - 1)) {
			$nextId =& $siblingSet->atPosition($myPosition + 2);
			if ($nextId)
				$nextId = $nextId->getIdString();
			else
				$nextId = 'end';
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('site', 'reorder', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'before' => $nextId,
									'return_node' => RequestContext::value('node')));
			print "'>--&gt;</a>";
		} else {
			print "\n\t\t\t--&gt;";
		}
		print "\n\t\t</div>";
	}
	
	/**
	 * print the cell position form elements
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionCellPositionForm () {
		$harmoni = Harmoni::instance();
		$id =& $this->getId();
		$idString = $id->getIdString();
		$parentId =& $this->_parent->getId();
		$parentIdString = $parentId->getIdString();
		
		if ($this->_parent->getNumCells() > 2) {
			print "\n\t\t<div>";
			$url = $harmoni->request->quickURL('site', 'change_column', 
								array('parent_id' => $parentIdString,
									'node' => $idString,
									'cell' => '______',
									'return_node' => RequestContext::value('node')));
									
			$layout = $this->_parent->getLayoutArrangement();
			if ($layout == 'rows')
				print "\n\t\t\tRow: &nbsp;";
			else
			print "\n\t\t\tColumn: &nbsp;";
		
			$currentColumn = $this->_parent->getDestinationCell($id);
			print "\n\t\t\t<select onchange='if (this.value == ".$currentColumn.") {alert(\""._("Already in this cell.")."\");} else {changeCell(\"".$url."\", this, ".$currentColumn.", \"".$parentIdString."\");}'>";
			for ($i = 1; $i < $this->_parent->getNumCells(); $i++) {
				print "\n\t\t\t\t<option";
				if ($currentColumn == $i)
					print " style='background-color: #ddd;' selected='selected'";
				print " value='".$i."'";
				print ">".$i."</option>";
			}
			print "\n\t\t\t</select>";
			print "\n\t\t</div>";
		}
	}
	
	/**
	 * Print the form for adding child nodes
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionAddChildForm () {
		
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