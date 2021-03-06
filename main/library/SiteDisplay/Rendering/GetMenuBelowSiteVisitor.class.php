<?php
/**
 * @since 4/10/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HasMenuBelowSiteVisitor.class.php,v 1.1 2008/04/10 20:49:02 adamfranco Exp $
 */ 

/**
 * This visitor determines if there is a menu below the site component passed.
 * 
 * @since 4/10/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HasMenuBelowSiteVisitor.class.php,v 1.1 2008/04/10 20:49:02 adamfranco Exp $
 */
class GetMenuBelowSiteVisitor
	extends HasMenuBelowSiteVisitor
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
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if ($siteComponent->getNestedMenuOrganizer())
			return $siteComponent->getNestedMenuOrganizer();
		else
			return $siteComponent->getOrganizer()->acceptVisitor($this);
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
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$result = $child->acceptVisitor($this);
				if ($result)
					return $result;
			}
		}
		
		return false;
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
		return $siteComponent;
	}

	
}

?>