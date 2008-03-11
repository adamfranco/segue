<?php
/**
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.4 2008/03/11 17:49:17 achapin Exp $
 */ 
 
require_once(dirname(__FILE__)."/SiteVisitor.interface.php");

/**
 * Return a bread-crumbs string
 * 
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.4 2008/03/11 17:49:17 achapin Exp $
 */
class BreadCrumbsVisitor 
	implements SiteVisitor
{
	
	/**
	 * @var string $currentNodeId;  
	 * @access private
	 * @since 3/11/08
	 */
	private $currentNodeId;

	/**
	 * Constructor
	 * 
	 * @param string $currentNodeId
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function BreadCrumbsVisitor ($currentNodeId) {
		ArgumentValidator::validate($currentNodeId, NonzeroLengthStringValidatorRule::getRule());
		
		$this->_links = array();
		$this->_separator = " &raquo; ";
		$this->currentNodeId = $currentNodeId;
	}
	
	/**
	 * Add a link for a node
	 * 
	 * @param object SiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function addLink ( $node ) {
		$harmoni = Harmoni::instance();
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
	public function visitBlock ( BlockSiteComponent $block ) {
		$this->addLink($block);
		
		$parent = $block->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a nav block
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {		
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
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		$this->addLink($siteNavBlock);
		
		$harmoni = Harmoni::instance();
		$RssLink = "<a href='"
							.$harmoni->request->quickUrl(
								"rss",
								"content",
								array('node' => $this->currentNodeId))
							."'><img src='".MYPATH."/images/Rss.png' alt='rss' align='right' title='RSS feed of this node' border='0'/></a>";
		
		$nodeLinks = implode(
					$this->_separator,
					array_reverse($this->_links));
					
		$breadcrumbs = "<table style='width: 100%'><tr>";
		$breadcrumbs .= "<td>".$nodeLinks."</td>";
		$breadcrumbs .= "<td style='text-align: right'>".$RssLink."</td>";
		$breadcrumbs .= "</tr></table>";
		
		return $breadcrumbs;
	}

	/**
	 * Visit a fixed organizer
	 *
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {		
		$parent = $organizer->getParentComponent();
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
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
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
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
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
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
}

?>