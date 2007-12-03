<?php
/**
 * @since 12/3/07
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TitleSearcher.class.php,v 1.1 2007/12/03 22:00:14 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * The Title searcher traverses a site and returns the NodeId that matches the
 * title specified. If no node is found, an UnknownTitleException is thrown
 * 
 * @since 12/3/07
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TitleSearcher.class.php,v 1.1 2007/12/03 22:00:14 adamfranco Exp $
 */
class TitleSearcher
	implements SiteVisitor
{
	
	/**
	 * @var array $visited; The nodes already visited 
	 * @access private
	 * @since 12/3/07
	 */
	private $visited;
	
	/**
	 * This is the primary method that clients will use
	 * 
	 * @param string $title
	 * @param object SiteComponent $startingComponent
	 * @return string The string Id.
	 * @access public
	 * @since 12/3/07
	 */
	public function getNodeId ($title, SiteComponent $startingComponent) {
		ArgumentValidator::validate($title, NonZeroLengthStringValidatorRule::getRule());
		
		$this->visited = array();
		$this->title = trim($title);
		
		$result = $this->searchDown($startingComponent);
		if (is_null($result))
			$result = $this->searchUp($startingComponent);
		
		if (is_null($result))
			throw new UnknownTitleException("Title '$title' was now found.");
		
		return $result;
	}
	
	/**
	 * Search for SiteComponents with the title passed going down the hierarchy
	 * 
 	 * @param object SiteComponent $startingComponent
	 * @return mixed string or null
	 * @access private
	 * @since 12/3/07
	 */
	private function searchDown (SiteComponent $startingComponent) {
		return $startingComponent->acceptVisitor($this);
	}
	
	/**
	 * Search for SiteComponents with the title passed going up the hierarchy, then down
	 * into siblings.
	 * 
 	 * @param object SiteComponent $startingComponent
	 * @return mixed string or null
	 * @access private
	 * @since 12/3/07
	 */
	private function searchUp (SiteComponent $startingComponent) {
		$parent = $startingComponent->getParentComponent();
		if ($parent) {
			$result = $this->searchDown($parent);
			if (!is_null($result))
				return $result;
			else
				return $this->searchUp($parent);
		}
		return null;	
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		if (in_array($siteComponent->getId(), $this->visited))
			return null;
		$this->visited[] = $siteComponent->getId();
		
		if ($this->title == trim($siteComponent->getDisplayName()))
			return $siteComponent->getId();
		
		return null;
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
		return $this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$result = $this->visitBlock($siteComponent);
		if (!is_null($result))
			return $result;
		
		return $siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		return $this->visitNavBlock($siteComponent);
	}
	
	/**
	 * Visit an organizer
	 * 
	 * @param object OrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access private
	 * @since 12/3/07
	 */
	private function visitOrganizer (OrganizerSiteComponent $siteComponent) {
		if (in_array($siteComponent->getId(), $this->visited))
			return null;
		$this->visited[] = $siteComponent->getId();
		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$result = $child->acceptVisitor($this);
				if (!is_null($result))
					return $result;
			}
		}
		
		return null;
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
}

/**
 * An Exception for unknown titles
 * 
 * @since 12/3/07
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TitleSearcher.class.php,v 1.1 2007/12/03 22:00:14 adamfranco Exp $
 */
class UnknownTitleException
	extends Exception
{
	
}

?>