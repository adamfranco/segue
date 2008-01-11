<?php
/**
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailEditModeSiteVisitor.class.php,v 1.9 2008/01/11 20:03:04 adamfranco Exp $
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
 * @version $Id: DetailEditModeSiteVisitor.class.php,v 1.9 2008/01/11 20:03:04 adamfranco Exp $
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
	function __construct ( $node ) {
		parent::__construct();
		
		$this->_node = $node;
		$this->_flowOrg = $node->getParentComponent();
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
	function visitTargetBlock () {
		$block = $this->_node;
		
		$guiContainer = parent::visitBlock($block);
		
		if ($guiContainer && $block->showComments()) {
			$commentManager = CommentManager::instance();
			
			$guiContainer->add(
					new Heading(
						$commentManager->getHeadingMarkup($block->getAsset()),
						3),
				$block->getWidth(), null, null, TOP);
			
			$guiContainer->add(
				new Block($commentManager->getMarkup($block->getAsset()), STANDARD_BLOCK),
				$block->getWidth(), null, null, TOP);
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
	function getPluginContent ( $block ) {
		ob_start();
		$harmoni = Harmoni::instance();
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($block->getAsset());
		
		$harmoni->request->passthrough('node');
		print $plugin->executeAndGetExtendedMarkup(true);
		$harmoni->request->forget('node');
		
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
	function showBlockTitle ( $block ) {
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
	function getBlockTitle ( $block ) {
		ob_start();		
		if ($block->getId() == $this->_node->getId()) {
			print "\n<div class='ui2_reorder'>";
			print "\n\t<a href='".$this->getHistoryUrl($block->getId())."'>";
			print _("history");
			print "</a>";
			print "\n</div>";
			
			print $block->getDisplayName()." &raquo; "._("Detail");
		} else
			print parent::getBlockTitle($block);
		
		return ob_get_clean();
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 5/18/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		if ($organizer->getId() == $this->_flowOrgId) {
			return $this->visitTargetBlock();
		} else {
			return parent::visitFlowOrganizer($organizer);
		}
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 5/18/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {
		if ($organizer->getId() == $this->_flowOrgId) {
			return $this->visitTargetBlock();
		} else {
			return parent::visitMenuOrganizer($organizer);
		}
	}
}

?>