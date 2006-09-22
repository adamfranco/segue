<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.8 2006/09/22 15:55:17 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.8 2006/09/22 15:55:17 adamfranco Exp $
 */
class moveComponentAction 
	extends EditModeSiteAction
{
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function processChanges ( &$director ) {
		// Get the target organizer's Id & Cell
		$targetId = RequestContext::value('destination');
		preg_match("/^(.+)_cell:(.+)$/", $targetId, $matches);
		$targetOrgId = $matches[1];
		$targetCell = $matches[2];
		
		$component =& $director->getSiteComponentById(RequestContext::value('component'));
		
		// If we are moving a navOrganizer, update the target of the menu
		if (strtolower(get_class($component)) == 'xmlnavorganizersitecomponent') {
			$menuOrganizer =& $component->getMenuOrganizer();
			$menuOrganizer->updateTargetId(RequestContext::value('destination'));
			return;
		} 
		// If we are moving a menu to a nav block, make the menu nested.
		else if (strtolower(get_class($component)) == 'xmlmenuorganizersitecomponent') {
			$newOrganizer =& $director->getSiteComponentById($targetOrgId);
			$currentComponentInCell =& $newOrganizer->getSubcomponentForCell($targetCell);
			printpre (strtolower(get_class($currentComponentInCell)));
			if (strtolower(get_class($currentComponentInCell)) == 'xmlnavblocksitecomponent') {
				printpre("Moving menu to: ".$currentComponentInCell->getId());
				$currentComponentInCell->makeNested($component);
				return;
			}
		}
		
		$newOrganizer =& $director->getSiteComponentById($targetOrgId);
		$oldCellId = $newOrganizer->putSubcomponentInCell($component, $targetCell);
		
		// If the targetCell was a target for any menus, change their targets
		// to the cell just vacated by the component we swapped with
		if (in_array($targetId, $director->getFilledTargetIds($targetOrgId))) {
			$menuIds = array_keys($director->getFilledTargetIds($targetOrgId), $targetId);
			foreach ($menuIds as $menuId) {
				$menuOrganizer =& $director->getSiteComponentById($menuId);
				printpre(get_class($menuOrganizer));
				
				$menuOrganizer->updateTargetId($oldCellId);
			}
		}
	}
}

?>