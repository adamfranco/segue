<?php
/**
 * @since 8/30/07
 * @package 
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteMapSiteVisitor.class.php,v 1.1 2008/03/14 21:55:15 achapin Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * <##>
 * 
 * @since 8/30/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteMapSiteVisitor.class.php,v 1.1 2008/03/14 21:55:15 achapin Exp $
 */
class SiteMapSiteVisitor
	implements SiteVisitor
{

	/**
	 * @var object RSSAction $siteMap
	 * @access private
	 * @since 8/30/07
	 */
	//private $siteMap;
	
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 8/30/07
	 */
	public function __construct () {

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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			print "\n<div>".$block->getDisplayName()."</div>";
// 			$item->setDescription($this->getPluginContent($block));
// 			$item->setLink($this->getDetailUrl($block->getId()));


			
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

	/**
	 * Answer the detail url of a block
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getDetailUrl ($id) {
		$harmoni = Harmoni::instance();
		return SiteDispatcher::quickURL(
				$harmoni->request->getRequestedModule(),
				"view",
				array("node" => $id));
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
	
	}

	
}

?>