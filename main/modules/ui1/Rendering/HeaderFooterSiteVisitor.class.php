<?php
/**
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeaderFooterSiteVisitor.class.php,v 1.2 2007/11/14 17:09:13 adamfranco Exp $
 */ 

/**
 * This site visitor determines the ids of the header and footer of a site if
 * such elements can be determined.
 * 
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeaderFooterSiteVisitor.class.php,v 1.2 2007/11/14 17:09:13 adamfranco Exp $
 */
class HeaderFooterSiteVisitor
	implements SiteVisitor
{
		
	/**
	 * @var mixed $headerId; null or a string Id 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerId = null;
	
	/**
	 * @var mixed $footerId; null or a string Id 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerId = null;
	
	/**
	 * @var mixed $headerCellId; null or a string Id 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerCellId = null;
	
	/**
	 * @var mixed $footerCellId; null or a string Id 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerCellId = null;
	
	/**
	 * @var boolean $inHeaderSearch;  
	 * @access private
	 * @since 11/14/07
	 */
	private $inHeaderSearch = false;
	
	/**
	 * @var boolean $inFooterSearch;  
	 * @access private
	 * @since 11/14/07
	 */
	private $inFooterSearch = false;
	
	/**
	 * @var array $headerChildCellIds;  
	 * @access private
	 * @since 11/14/07
	 */
	private $headerChildCellIds = array();
	
	/**
	 * @var array $footerChildCellIds;  
	 * @access private
	 * @since 11/14/07
	 */
	private $footerChildCellIds = array();
	
	/**
	 * @var array $navTargets;  
	 * @access private
	 * @since 11/14/07
	 */
	private $navTargets = array();
	
	/**
	 * Constructor. Pass the root site element.
	 * 
	 * @param SiteNavBlockSiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/24/07
	 */
	public function __construct (SiteNavBlockSiteComponent $siteComponent) {
		$siteComponent->acceptVisitor($this);
	}
	
	/**
	 * Answer the header Id or null if not found
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderId () {
		if (in_array($this->headerCellId, $this->navTargets))
			return null;
		
		foreach ($this->navTargets as $targetId)
			if (in_array($targetId, $this->headerChildCellIds))
				return null;
		
		return $this->headerId;
	}
	
	/**
	 * Answer the footer Id or null if not found
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 9/24/07
	 */
	public function getFooterId () {
		if (in_array($this->footerCellId, $this->navTargets))
			return null;
		
		foreach ($this->navTargets as $targetId)
			if (in_array($targetId, $this->footerChildCellIds))
				return null;
		
		return $this->footerId;
	}
	
	/**
	 * Answer the header cell Id or null if not found
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderCellId () {
		if (in_array($this->headerCellId, $this->navTargets))
			return null;
		
		foreach ($this->navTargets as $targetId)
			if (in_array($targetId, $this->headerChildCellIds))
				return null;
		
		return $this->headerCellId;
	}
	
	/**
	 * Answer the footer cell Id or null if not found
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 9/24/07
	 */
	public function getFooterCellId () {
		if (in_array($this->footerCellId, $this->navTargets))
			return null;
		
		foreach ($this->navTargets as $targetId)
			if (in_array($targetId, $this->footerChildCellIds))
				return null;
			
		return $this->footerCellId;
	}
	
	/*********************************************************
	 * Traversal
	 *********************************************************/
	
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		return true;
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
		return true;
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
		$this->navTargets[] = $siteComponent->getTargetId();
		return false;
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
		$childOrganizer = $siteComponent->getOrganizer();
		$childOrganizer->acceptVisitor($this);
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
		$numCells = $siteComponent->getTotalNumberOfCells();
		
		$isHeaderOrFooter = true;
		for ($i = 0; $i < $numCells; $i++) {
		
			// If we are traversing down through what we think might be a header or 
			// footer, store all of the fixed organizer cell ids so that we can 
			// match them against any nav targets.
			if ($this->inHeaderSearch)
				$this->headerChildCellIds[] = $siteComponent->getId()."_cell:".$i;
			if ($this->inFooterSearch)
				$this->footerChildCellIds[] = $siteComponent->getId()."_cell:".$i;
			
			$child = $siteComponent->getSubcomponentForCell($i);
			
			// If any of our children return false because they are menus or nav
			// items, then we can't be a header or footer.
			if (is_object($child) && !$child->acceptVisitor($this))
				$isHeaderOrFooter = false;
		}
		
		return $isHeaderOrFooter;
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
		$numCells = $siteComponent->getTotalNumberOfCells();
		
		// Check for a header in the first cell
		if ($numCells) {
			$child = $siteComponent->getSubcomponentForCell(0);
			
			$this->inHeaderSearch = true;
			if (is_object($child)) {
				if ($child->acceptVisitor($this)) {
					$this->headerId = $child->getId();
					$this->headerCellId = $siteComponent->getId()."_cell:0";
				}
			} else {
				$this->headerCellId = $siteComponent->getId()."_cell:0";
			}
			$this->inHeaderSearch = false;
		}
		
		// Traverse the other cells to look for nav targets
		for ($i = 1; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$child->acceptVisitor($this);
			}
		}
		
		// Check for a footer in the last cell
		if ($numCells > 2 || (is_null($this->headerId) && $numCells == 2)) {
			$child = $siteComponent->getSubcomponentForCell($numCells - 1);
			
			$this->inFooterSearch = true;
			if (is_object($child)) {
				if ($child->acceptVisitor($this)) {
					$this->footerId = $child->getId();
					$this->footerCellId = $siteComponent->getId()."_cell:".($numCells - 1);
				}
			} else {
				$this->footerCellId = $siteComponent->getId()."_cell:".($numCells - 1);
			}
			$this->inFooterSearch = false;
		}
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
		return true;
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
		$this->navTargets[] = $siteComponent->getTargetId();
		return false;
	}
	
}

?>