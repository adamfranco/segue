<?php
/**
 * @since 8/30/07
 * @package 
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ContentRssSiteVisitor.class.php,v 1.1 2007/08/31 16:43:52 achapin Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");

/**
 * <##>
 * 
 * @since 8/30/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ContentRssSiteVisitor.class.php,v 1.1 2007/08/31 16:43:52 achapin Exp $
 */
class ContentRssSiteVisitor
	extends ViewModeSiteVisitor
{

	/**
	 * @var object RSSAction $rssFeed
	 * @access private
	 * @since 8/30/07
	 */
	private $rssFeed;
	
	/**
	 * constructor
	 * 
	 * @param object RSSAction $rssFeed
	 * @return void
	 * @access public
	 * @since 8/30/07
	 */
	public function __construct (RSSAction $rssFeed) {
		$this->rssFeed = $rssFeed;
		
		$this->ViewModeSiteVisitor();
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function visitBlock ( BlockSiteComponent $block ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");	
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$item = $this->rssFeed->addItem(new RSSItem);
		
			$item->setTitle($block->getDisplayName());
			$item->setDescription($this->getPluginContent($block));
			
			return;
		} else {		
			return;
		}
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return ref array
	 * @access public
	 * @since 4/3/06
	 */
	function visitNavBlock ( NavBlockSiteComponent $navBlock ) {
		$childOrganizer = $navBlock->getOrganizer();
		$childOrganizer->acceptVisitor($this);
			
		$nestedMenuOrganizer = $navBlock->getNestedMenuOrganizer();
		if (!is_null($nestedMenuOrganizer)) {
			$nestedMenuOrganizer->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return ref array
	 * @access public
	 * @since 4/3/06
	 */
	function visitSiteNavBlock ( SiteNavBlockSiteComponent $navBlock ) {
		$this->visitNavBlock($navBlock);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function visitFlowOrganizer( FlowOrganizerSiteComponent $organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			$child->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function visitMenuOrganizer( MenuOrganizerSiteComponent $organizer ) {
		$this->visitFlowOrganizer($organizer);
	}
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$child->acceptVisitor($this);
			}
		}
	}
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		$this->visitFixedOrganizer($organizer);
	}
	
}

?>