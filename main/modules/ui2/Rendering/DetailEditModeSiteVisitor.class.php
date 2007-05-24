<?php
/**
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailEditModeSiteVisitor.class.php,v 1.1 2007/05/24 19:32:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/EditModeSiteVisitor.class.php");

/**
 * Render the 'detail' view of a node and its discusions in edit mode.
 * 
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailEditModeSiteVisitor.class.php,v 1.1 2007/05/24 19:32:01 adamfranco Exp $
 */
class DetailEditModeSiteVisitor
	extends EditModeSiteVisitor
{
		
	/**
	 * Constructor.
	 * 
	 * @param object BlockSiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/18/07
	 */
	function DetailEditModeSiteVisitor ( &$node ) {
		$this->EditModeSiteVisitor();
		
		$this->_node =& $node;
		$this->_flowOrg =& $node->getParentComponent();
		$this->_flowOrgId = $this->_flowOrg->getId();
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitTargetBlock () {
		$block =& $this->_node;
		
		$guiContainer =& parent::visitBlock($block);
		
		if ($guiContainer) {
			$guiContainer->add(
					new Heading(
						_("Discussions:"),
						3),
				$block->getWidth(), null, null, TOP);
			
// 			$guiContainer->add(
// 				$this->getDiscussions($block),
// 				$block->getWidth(), null, null, TOP);
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the plugin content for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	function getPluginContent ( &$block ) {
		ob_start();
		$harmoni =& Harmoni::instance();
		$pluginManager =& Services::getService('PluginManager');
		$plugin =& $pluginManager->getPlugin($block->getAsset());
		
		print $plugin->executeAndGetExtendedMarkup(true);
		
		return ob_get_clean();
	}
	
	/**
	 * Answer true if the block title should be shown.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function showBlockTitle ( &$block ) {
		return true;
	}
	
	/**
	 * Answer the title of a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getBlockTitle ( &$block ) {
		if ($block->getId() == $this->_node->getId())
			return $block->getDisplayName()." &raquo; "._("Detail");
		else
			return parent::getBlockTitle($block);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 5/18/07
	 */
	function &visitFlowOrganizer( &$organizer ) {
		if ($organizer->getId() == $this->_flowOrgId) {
			return $this->visitTargetBlock();
		} else {
			return parent::visitFlowOrganizer($organizer);
		}
	}
}

?>