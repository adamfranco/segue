<?php
/**
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
// require_once(dirname(__FILE__)."/SiteVisitor.interface.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(dirname(__FILE__)."/Participation_LastEditAction.class.php");
require_once(dirname(__FILE__)."/Participation_CreateAction.class.php");

/**
 * transverse a site hierarchy getting information about participation
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class ParticipationSiteVisitor
	implements SiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	function __construct () {
		$this->_actions = array();
	}
	
	/**
	 * Answer the actions we've found
	 * 
	 * @return array of Action objects
	 * @access public
	 * @since 1/26/09
	 */
	public function getActions () {
		return $this->_actions;
	}

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		return $this->getModDateAction($siteComponent);
		
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->getModDateAction($siteComponent);
		
		$organizer = $siteComponent->getOrganizer();
		$organizer->acceptVisitor($this);
		
		$nestedMenuOrganizer = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenuOrganizer)) {
			$nestedMenuOrganizer->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->visitNavBlock($siteComponent);
	}

	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object OrganizerSiteComponent $organizer
	 * @return object Component
	 * @access private
	 * @since 1/26/09
	 */
	private function visitOrganizer ( OrganizerSiteComponent $siteComponent ) {		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child))
				$child->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		$this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}


	/**
	 * Adds modification date to action array
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 1/26/09
	 */
	public function getModDateAction ( BlockSiteComponent $siteComponent ) {
		$this->_actions[] = new Participation_CreateAction($siteComponent);
		
// 		if (!$siteComponent->getCreationDate()->isEqual(
// 				$siteComponent->getModificationDate()))
// 		{
// 			$this->_actions[] = new Participation_LastEditAction($siteComponent);
// 		}

	}
	
}

?>