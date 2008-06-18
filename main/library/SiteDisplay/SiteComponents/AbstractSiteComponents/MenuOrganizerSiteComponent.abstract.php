<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MenuOrganizerSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/FlowOrganizerSiteComponent.abstract.php");


/**
 * The Menu organizer site component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MenuOrganizerSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */
interface MenuOrganizerSiteComponent 
	extends FlowOrganizerSiteComponent
{

	/**
	 * Answer the kind of menu Gui Component to display: Menu_Left, Menu_Right, Menu_Top, or Menu_Bottom
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getDisplayType ();
	
	/**
	 * Set the Gui Component display type for this menu, one of: 
	 * 		Menu_Left, Menu_Right, Menu_Top, or Menu_Bottom
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setDisplayType ($displayType);
	
	/**
	 * Answer true if this is the top-level menu.
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/6/08
	 */
	public function isRootMenu ();

}

?>