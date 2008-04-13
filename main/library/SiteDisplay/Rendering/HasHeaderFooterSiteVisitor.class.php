<?php
/**
 * @since 4/13/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HasHeaderFooterSiteVisitor.class.php,v 1.1 2008/04/13 18:43:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/IsHeaderFooterSiteVisitor.class.php');
require_once(dirname(__FILE__).'/SiteVisitor.interface.php');

/**
 * This visitor determines if a header or footer exists below the node passed to it
 * 
 * @since 4/13/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HasHeaderFooterSiteVisitor.class.php,v 1.1 2008/04/13 18:43:01 adamfranco Exp $
 */
class HasHeaderFooterSiteVisitor
	implements SiteVisitor
{
	/**
	 * @var object IsHeaderFooterSiteVisitor $isHeaderFooterVisitor;  
	 * @access private
	 * @since 4/13/08
	 */
	private $isHeaderFooterVisitor;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 4/13/08
	 */
	public function __construct () {
		$this->isHeaderFooterVisitor = new IsHeaderFooterSiteVisitor;
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 4/13/08
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		return $siteComponent->acceptVisitor($this->isHeaderFooterVisitor);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 4/13/08
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		return $siteComponent->acceptVisitor($this->isHeaderFooterVisitor);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 4/13/08
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		return false;
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 4/13/08
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		return $siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit an organizer
	 * 
	 * @param OrganizerSiteComponent $siteComponent
	 * @return boolean
	 * @access public
	 * @since 4/13/08
	 */
	public function visitOrganizer (OrganizerSiteComponent $siteComponent) {
		if ($siteComponent->acceptVisitor($this->isHeaderFooterVisitor))
			return true;
		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				if ($child->acceptVisitor($this))
					return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 4/13/08
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
	 * @since 4/13/08
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
	 * @since 4/13/08
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
	 * @since 4/13/08
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return false;
	}

	
}

?>