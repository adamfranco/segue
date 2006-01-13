<?php
/**
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueMenuGenerator.class.php,v 1.4 2006/01/13 20:54:32 adamfranco Exp $
 */

/**
 * The MenuGenerator class is a static class used for the generation of Menus in
 * Segue.
 *
 * @author Adam Franco
 *
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueMenuGenerator.class.php,v 1.4 2006/01/13 20:54:32 adamfranco Exp $
 */

class SegueMenuGenerator {

	/**
	 * Generates a menu layout based on the current action.
	 * @param string $actionString A dotted-pair action string of the form
	 *		"module.action" .
	 * @return object MenuLayout
	 */
	function &generateMainMenu($harmoni) {
		
		$harmoni =& Harmoni::instance();
		
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		
		$mainMenu =& new Menu(new YLayout(), 1);

	// :: Home ::
		$mainMenu_item1 =& new MenuItemLink(
			_("Home"), 
			$harmoni->request->quickURL("home", "welcome"), 
			($module == "home" && $action == "welcome")?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item1, "100%", null, LEFT, CENTER);


		$mainMenu_item2 =& new MenuItemLink(
			_("Plugin Tests"),
			$harmoni->request->quickURL("plugin_manager", "test"), 
			($module == "plugin_manager")?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item2, "100%", null, LEFT, CENTER);
	
	
		return $mainMenu;
	}
}

?>