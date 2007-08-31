<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FixedOrganizerSiteComponent.abstract.php,v 1.3 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/OrganizerSiteComponent.abstract.php");

/**
 * The Organizer subdivides its bounding cell and arranges its subcomponents in
 * those subdivisions
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FixedOrganizerSiteComponent.abstract.php,v 1.3 2007/08/31 16:03:45 achapin Exp $
 */
interface FixedOrganizerSiteComponent
	extends OrganizerSiteComponent
{	
	/**
	 * Add a subcomponent to an empty cell
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function addSubcomponentToCell ( SiteComponent $siteComponent, $cellIndex ) ;
	
	/**
	 * Swap the contents of two cells
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function swapCells ( $cellOneIndex, $cellTwoIndex ) ;
	
}

?>