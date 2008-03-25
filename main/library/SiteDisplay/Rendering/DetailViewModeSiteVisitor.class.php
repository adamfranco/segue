<?php
/**
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailViewModeSiteVisitor.class.php,v 1.14 2008/03/25 16:11:07 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/ViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");

/**
 * Render the 'detail' view of a node and its discusions.
 * 
 * @since 5/18/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DetailViewModeSiteVisitor.class.php,v 1.14 2008/03/25 16:11:07 achapin Exp $
 */
class DetailViewModeSiteVisitor
	extends ViewModeSiteVisitor
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
		print $plugin->executeAndGetExtendedMarkup(false);
		$harmoni->request->forget('node');

		// print out attribution based on block settings
		$attribution = new AttributionPrinter($block);
		$attributionDisplay = $attribution->getAttributionMarkUp();
		if (!is_null($attributionDisplay) && strlen($attributionDisplay)) {			
			print $attributionDisplay;
		}

		
		if ($plugin->supportsVersioning() && $block->showHistory()) {	
			print "\n<div style='text-align: right;'>";
			print "\n\t<a href='".$this->getHistoryUrl($block->getId())."'>";
			print _("history");
			print "</a>";
			print "\n</div>";
		}
		print "\n<div style='clear: both'></div>";

		return ob_get_clean();
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
		if ($block->getId() == $this->_node->getId())
			return $block->getDisplayName()." &raquo; "._("Detail");
		else
			return parent::getBlockTitle($block);
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
		if ($block->getId() == $this->_node->getId())
			return true;
		else
			return $block->showDisplayName();
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