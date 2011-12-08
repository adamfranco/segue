<?php
/**
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueMenuGenerator.class.php,v 1.15 2008/03/20 20:48:48 adamfranco Exp $
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
 * @version $Id: SegueMenuGenerator.class.php,v 1.15 2008/03/20 20:48:48 adamfranco Exp $
 */

class SegueMenuGenerator {

	/**
	 * Generates a menu layout based on the current action.
	 * @param string $actionString A dotted-pair action string of the form
	 *		"module.action" .
	 * @return object MenuLayout
	 * @static
	 */
	static function generateMainMenu() {
		
		$harmoni = Harmoni::instance();
		
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		
		$mainMenu = new Menu(new YLayout(), 1);

	// :: Home ::
		$mainMenu_item = new MenuItemLink(
			_("Home"), 
			$harmoni->request->quickURL("home", "welcome"), 
			($module == "home" && $action == "welcome")?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item, "100%", null, LEFT, CENTER);


// 		$mainMenu_item = new MenuItemLink(
// 			_("Plugin Tests"),
// 			$harmoni->request->quickURL("plugin_manager", "test"), 
// 			($module == "plugin_manager")?TRUE:FALSE,1);
// 		$mainMenu->add($mainMenu_item, "100%", null, LEFT, CENTER);
		
		$mainMenu_item = new MenuItemLink(
			_("Portal"),
			$harmoni->request->quickURL('portal', "list"), 
			($module == "portal" && $action == 'list')?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item, "100%", null, LEFT, CENTER);		
		
		if (defined('DATAPORT_SEGUE1_URL') && defined('DATAPORT_SEGUE1_SECRET_KEY') && defined('DATAPORT_SEGUE1_SECRET_VALUE')) {
			$mainMenu_item = new MenuItemLink(
				_("Migrate Sites"),
				$harmoni->request->quickURL('dataport', "choose_site"), 
				($module == "dataport" && $action == 'choose_site')?TRUE:FALSE,1);
			$mainMenu->add($mainMenu_item, "100%", null, LEFT, CENTER);
		}
		
		$mainMenu_item8 = new MenuItemLink(
			_("User Tools"),
			$harmoni->request->quickURL("user", "main"), 
			(preg_match("/^user$/", $module))?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item8, "100%", null, LEFT, CENTER);
		
		$mainMenu_item7 = new MenuItemLink(_("Admin Tools"),
			$harmoni->request->quickURL("admin", "main"), 
			(preg_match("/^admin$/",$module))?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item7, "100%", null, LEFT, CENTER);
	
		return $mainMenu;
	}
}

?>