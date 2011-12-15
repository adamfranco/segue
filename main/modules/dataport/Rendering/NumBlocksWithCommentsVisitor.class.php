<?php
/**
 * @since 7/29/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * Answer the number of SiteComponents in a site.
 * 
 * @since 7/29/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class NumBlocksWithCommentsVisitor
	implements SiteVisitor
{
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function __construct ( NavBlockSiteComponent $siteComponent ) {
		$this->startingNavBlock = $siteComponent;
	}
	
	/**
	 * Answer the number of blocks directly under the nav item that have comments.
	 * 
	 * @return int
	 */
	public function getNumberOfBlocksWithComments () {
		$num = $this->startingNavBlock->getOrganizer()->acceptVisitor($this);
		
		$nestedMenu = $this->startingNavBlock->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$num += $nestedMenu->acceptVisitor($this);
		
		return $num;
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
		$cm = CommentManager::instance();
		if ($cm->getNumComments($siteComponent->getAsset()))
			return 1;
		else
			return 0;
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
		$num = $siteComponent->getOrganizer()->acceptVisitor($this);
		
		$nestedMenu = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$num += $nestedMenu->acceptVisitor($this);
		
		return $num;
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
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		$num = 0;
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				$num += $child->acceptVisitor($this);
			}
		}
		
		return $num;
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
		return $this->visitFixedOrganizer($siteComponent);
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
		$num = 0;
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				$num += $child->acceptVisitor($this);
			}
		}
		
		return $num;
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
		return $this->visitFlowOrganizer($siteComponent);
	}
	
}

?>