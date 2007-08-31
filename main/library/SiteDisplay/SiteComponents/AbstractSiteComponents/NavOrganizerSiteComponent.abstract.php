<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavOrganizerSiteComponent.abstract.php,v 1.1 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/FixedOrganizerSiteComponent.abstract.php");

/**
 * The Organizer that is the direct child of a NavBlock.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavOrganizerSiteComponent.abstract.php,v 1.1 2007/08/31 16:03:45 achapin Exp $
 */
interface NavOrganizerSiteComponent
	extends FixedOrganizerSiteComponent 
{
	
	/**
	 * Answer the menu for this component
	 * 
	 * @return object MenuOrganizerSiteComponent
	 * @access public
	 * @since 7/28/06
	 */
	public function getMenuOrganizer () ;
}

?>