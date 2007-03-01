<?php
/**
 * @since 9/22/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: createSubMenu.act.php,v 1.1 2007/03/01 20:12:58 adamfranco Exp $
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
 * @version $Id: createSubMenu.act.php,v 1.1 2007/03/01 20:12:58 adamfranco Exp $
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
	function processChanges ( &$director ) {
		// Get the target organizer's Id & Cell
		$parentNavBlockId = RequestContext::value('parent');
		
		$parentNavBlock =& $director->getSiteComponentById($parentNavBlockId);
		$director->getRootSiteComponent($parentNavBlockId);		
		$navOrganizer =& $parentNavBlock->getOrganizer();
		
		// See if there is an empty cell to use for the target.
		$numCells = $navOrganizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			if (!$navOrganizer->getSubComponentForCell($i)) {
				$subMenuTarget = $navOrganizer->getId()."_cell:".$i;
				break;
			}
		}
		
		// If not, expand the navOrganizer to make room for the new menu target.
		if (!isset($subMenuTarget)) {
			$navOrganizer->updateNumColumns($navOrganizer->getNumColumns() + 1);
			$subMenuTarget = $navOrganizer->getId()."_cell:"
									.($navOrganizer->getLastIndexFilled() + 1);
		}
		
		// Crete the submenu
		$subMenu =& $director->createSiteComponent(new Type('segue', 'edu.middlebury', "MenuOrganizer"), $parentNavBlock);
		$parentNavBlock->makeNested($subMenu);
		
		// Set its target.
		$subMenu->updateTargetId($subMenuTarget);
		
		// set the direction
		if (RequestContext::value('direction'))
			$subMenu->updateDirection(urldecode(RequestContext::value('direction')));
	}
	
}

?>