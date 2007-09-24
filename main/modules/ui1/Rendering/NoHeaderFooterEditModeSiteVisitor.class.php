<?php
/**
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NoHeaderFooterEditModeSiteVisitor.class.php,v 1.1 2007/09/24 20:49:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/EditModeSiteVisitor.class.php");

/**
 * This Edit-Mode SiteVisitor will not display editing components for headers and
 * footers
 * 
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NoHeaderFooterEditModeSiteVisitor.class.php,v 1.1 2007/09/24 20:49:09 adamfranco Exp $
 */
class NoHeaderFooterEditModeSiteVisitor
	extends EditModeSiteVisitor
{

	/**
	 * Visit a Fixed organizer
	 * 
	 * @param FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 9/24/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		if ($this->isHeaderOrFooter($organizer)) {
			$viewModeVisitor = new ViewModeSiteVisitor();
			return $organizer->acceptVisitor($viewModeVisitor);
		} else {
			return parent::visitFixedOrganizer($organizer);
		}
	}
		
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 1/15/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		if ($this->isHeaderOrFooter($organizer)) {
			$viewModeVisitor = new ViewModeSiteVisitor();
			return $organizer->acceptVisitor($viewModeVisitor);
		} else {
			return parent::visitFlowOrganizer($organizer);
		}
	}
	
}

?>