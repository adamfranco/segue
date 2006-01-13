<?php
/**
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueMenuGenerator.class.php,v 1.3 2006/01/13 19:51:17 adamfranco Exp $
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
 * @version $Id: SegueMenuGenerator.class.php,v 1.3 2006/01/13 19:51:17 adamfranco Exp $
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
			_("Collections"),
			$harmoni->request->quickURL("collections", "namebrowse"), 
			($module == "collections")?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item2, "100%", null, LEFT, CENTER);
		
		// Collection browse links.
		// Just show if we are not in a particular collection.
		if (ereg("collection(s)?|asset", $module)) {			
			// Collection root
			if (ereg("^(collection|asset)$", $module)) {
				// Name browse
				$mainMenu_item5 =& new MenuItemLink(
						_("Collection"),
						$harmoni->request->quickURL("collection", "browse",
							array('collection_id' => $harmoni->request->get('collection_id'))),
						($module == "collection")?TRUE:FALSE, 2
				);
				$mainMenu->add($mainMenu_item5, "100%", null, LEFT, CENTER);
			}
		}
		
		$mainMenu_item6 =& new MenuItemLink(
			_("Exhibitions"),
			$harmoni->request->quickURL("exhibitions", "browse"), 
			(ereg("^exhibition.*",$module))?TRUE:FALSE,1);
		$mainMenu->add($mainMenu_item6, "100%", null, LEFT, CENTER);
		
		foreach (array_keys($_SESSION) as $key) {
			if (ereg("^add_slideshow_wizard_(.+)", $key, $matches)) {
				$exhibitionAssetId = $matches[1];
	
				$item =& new MenuItemLink(
						_("SlideShow"),
						$harmoni->request->quickURL("exhibitions", "add_slideshow", 
							array("exhibition_id" => $exhibitionAssetId)), 
						($module == "exhibitions" && $action == "add_slideshow" && RequestContext::value("exhibition_id") == $exhibitionAssetId)?TRUE:FALSE, 2
				);
				$mainMenu->add($item, "100%", null, LEFT, CENTER);
			}
		}
		
		$mainMenu_item8 =& new MenuItemLink(
			_("User Tools"),
			$harmoni->request->quickURL("user", "main"), 
			(ereg("^user$", $module))?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item8, "100%", null, LEFT, CENTER);
		
		$mainMenu_item7 =& new MenuItemLink(_("Admin Tools"),
			$harmoni->request->quickURL("admin", "main"), 
			(ereg("^admin$",$module))?TRUE:FALSE, 1);
		$mainMenu->add($mainMenu_item7, "100%", null, LEFT, CENTER);
	
	
		return $mainMenu;
	}
}

?>