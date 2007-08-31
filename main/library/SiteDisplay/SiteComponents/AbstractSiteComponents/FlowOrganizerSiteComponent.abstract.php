<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FlowOrganizerSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
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
 * @version $Id: FlowOrganizerSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */
interface FlowOrganizerSiteComponent
	extends OrganizerSiteComponent
{
	
	/**
	 * Get the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getOverflowStyle () ;
	
	/**
	 * Update the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @param string $overflowStyle
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateOverflowStyle ( $overflowStyle ) ;
	
	/**
	 * Add a subcomponent
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function addSubcomponent ( BlockSiteComponent $siteComponent ) ;
	
	/**
	 * Move the contents of cellOneIndex before cellTwoIndex
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function moveBefore ( $cellOneIndex, $cellTwoIndex ) ;
	
	/**
	 * Move the contents of cellIndex to the end of the organizer
	 * 
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function moveToEnd ( $cellIndex ) ;

}

?>