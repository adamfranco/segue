<?php
/**
 * @since 8/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: 
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");

/**
 * A visitor that can traverse the site hierarchy.
 * 
 * @since 8/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: 
 */
class TagModeSiteVisitor 
	extends ViewModeSiteVisitor
{
			
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		ob_start();
		
		$menuItem = new MenuItem(ob_get_clean(), 0);
		return $menuItem;		
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
		$menuItems = array();
		return $menuItems;
	}
	
}

?>