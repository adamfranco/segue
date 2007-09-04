<?php
/**
 * @since 9/22/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createSubMenu.act.php,v 1.4 2007/09/04 15:07:44 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");

/**
 * <##>
 * 
 * @since 9/22/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createSubMenu.act.php,v 1.4 2007/09/04 15:07:44 adamfranco Exp $
 */
class createSubMenuAction
	extends EditModeSiteAction
{
		
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 9/22/06
	 */
	function processChanges ( SiteDirector $director ) {
		// Get the target organizer's Id & Cell
		$parentNavBlockId = RequestContext::value('parent');
		$parentNavBlock = $director->getSiteComponentById($parentNavBlockId);
		$director->getRootSiteComponent($parentNavBlockId);		
		$navOrganizer = $parentNavBlock->getOrganizer();
		
		// Crete the submenu
		$subMenu = $director->createSiteComponent(new Type('segue', 'edu.middlebury', "MenuOrganizer"), $parentNavBlock);
		
		// If the parent menu is vertical, nest the sub-menu by default.
		$parentMenu = $parentNavBlock->getParentComponent();
		
		if (preg_match('/^(Top-Bottom|Bottom-Top)\//i', $parentMenu->getDirection())) {
			$parentNavBlock->makeNested($subMenu);
		}
		// If the parent is horizontal, put the sub-menu in the first cell
		// of the parent's nav-organizer
		else {
			$numCells = $navOrganizer->getTotalNumberOfCells();
			for ($i = 0; $i < $numCells; $i++) {
				if (!$navOrganizer->getSubComponentForCell($i)) {
					$firstEmpty = $i;
					break;
				}
			}
			if (!isset($firstEmpty)) {
				$navOrganizer->updateNumColumns($navOrganizer->getNumColumns() + 1);
				$firstEmpty = $navOrganizer->getLastIndexFilled() + 1;
			}
			$navOrganizer->putSubcomponentInCell($subMenu, $firstEmpty);
			for ($i = $firstEmpty; $i > 0; $i--) {
				$navOrganizer->swapCells($i, $i - 1);
			}
		}
		
		// See if there is an empty cell to use for the target.
		$numCells = $navOrganizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			if (!$navOrganizer->getSubComponentForCell($i)) {
				$subMenuTarget = $navOrganizer->getId()."_cell:".$i;
				break;
			}
		}
		// If not, expand the navOrganizer to make room for the new menu target in the second cell.
		if (!isset($subMenuTarget)) {
			$navOrganizer->updateNumColumns($navOrganizer->getNumColumns() + 1);
			for ($i = ($navOrganizer->getTotalNumberOfCells() - 1); $i > 1; $i--) {
				$navOrganizer->swapCells($i, $i - 1);
			}
			$subMenuTarget = $navOrganizer->getId()."_cell:1";
		}
		
		// Set its target.
		$subMenu->updateTargetId($subMenuTarget);
		
		// set the direction
		if (RequestContext::value('direction'))
			$subMenu->updateDirection(urldecode(RequestContext::value('direction')));
		
	}
	
}

?>