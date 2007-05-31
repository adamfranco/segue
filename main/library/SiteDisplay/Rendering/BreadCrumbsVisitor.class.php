<?php
/**
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.1 2007/05/31 19:49:20 adamfranco Exp $
 */ 

/**
 * Return a bread-crumbs string
 * 
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.1 2007/05/31 19:49:20 adamfranco Exp $
 */
class BreadCrumbsVisitor {

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function BreadCrumbsVisitor () {
		$this->_links = array();
		$this->_separator = " &raquo; ";
	}
	
	/**
	 * Add a link for a node
	 * 
	 * @param object SiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function addLink ( &$node ) {
		$harmoni =& Harmoni::instance();
		$this->_links[] = "<a href='"
							.$harmoni->request->quickUrl(
								$harmoni->request->getRequestedModule(),
								$harmoni->request->getRequestedAction(),
								array('node' => $node->getId()))
							."'>".$node->getDisplayName()."</a>";
	}
		
	/**
	 * Visit a block 
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitBlock ( &$block ) {
		$this->addLink($block);
		
		$parent =& $block->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a nav block
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitNavBlock ( &$navBlock ) {		
		return $this->visitBlock($navBlock);
	}
	
	/**
	 * Visit a SiteNavBlock
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitSiteNavBlock ( &$siteNavBlock ) {
		$this->addLink($siteNavBlock);
		
		$val = implode(
					$this->_separator,
					array_reverse($this->_links));
		
		return $val;
	}

	/**
	 * Visit a fixed organizer
	 *
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitFixedOrganizer ( &$organizer ) {		
		$parent =& $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a fixed organizer 
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitNavOrganizer ( &$organizer ) {
		$parent =& $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitFlowOrganizer( &$organizer ) {
		$parent =& $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a menu organizer
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	function &visitMenuOrganizer ( &$organizer ) {	
		$parent =& $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
}

?>