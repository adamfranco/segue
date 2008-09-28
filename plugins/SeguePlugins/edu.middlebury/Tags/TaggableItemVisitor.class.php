<?php
/**
 * @since 4/7/08
 * @package segue.plugins.SeguePlugins
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TaggableItemVisitor.class.php,v 1.3 2008/04/10 20:49:02 adamfranco Exp $
 */

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php"); 
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsHeaderFooterSiteVisitor.class.php");
require_once(POLYPHONY."/main/modules/tags/TagAction.abstract.php");

/**
 * The TaggableItemVisitor traverses the site hierarchy and gets taggable item.
 * 
 * @since 4/7/08
 * @package 
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TaggableItemVisitor.class.php,v 1.3 2008/04/10 20:49:02 adamfranco Exp $
 */ 

class TaggableItemVisitor 
	implements SiteVisitor
{

	/**
	 * Answer an array of tag objects from
	 * 
	 * @var object $tags; 
	 * @access private
	 * @since 4/7/08
	 */
	private $taggableItems = null;
	
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/7/08
	 */
	function __construct () {
		$this->taggableItems = array();
	
	}

		
	/**
	 * Visit a Block and add to 
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return object
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $block ) {
		$this->taggableItems[] = HarmoniNodeTaggedItem::forId($block->getId(), 'segue');
		return $this->taggableItems;
		
		//return visitTaggableComponents($block);	
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
		return $this->taggableItems;
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {
			$childOrganizer = $navBlock->getOrganizer();
			$childOrganizer->acceptVisitor($this);
			
			$nestedMenuOrganizer = $navBlock->getNestedMenuOrganizer();
			if (!is_null($nestedMenuOrganizer))
				$nestedMenuOrganizer->acceptVisitor($this);	
		return $this->taggableItems;	
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		$childOrganizer = $siteNavBlock->getOrganizer();
		$childOrganizer->acceptVisitor($this);

		return $this->taggableItems;
	}

	/**
	 * Visit a organizer and if has children visit those
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object OrganizerSiteComponent $organizer
	 * @return object Component
	 * @access private
	 * @since 4/3/06
	 */
	private function visitOrganizer ( OrganizerSiteComponent $organizer ) {		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child))
				$child->acceptVisitor($this);
		}
		return $this->taggableItems;
	}

	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
}

?>
