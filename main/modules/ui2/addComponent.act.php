<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addComponent.act.php,v 1.2 2007/05/22 17:05:28 adamfranco Exp $
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
 * @version $Id: addComponent.act.php,v 1.2 2007/05/22 17:05:28 adamfranco Exp $
 */
class addComponentAction 
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
		$targetOrgId = RequestContext::value('organizerId');
		$targetCell = RequestContext::value('cellIndex');
		
		$organizer =& $director->getSiteComponentById($targetOrgId);
		$director->getRootSiteComponent($targetOrgId);
		
		$componentType =& Type::fromString(RequestContext::value('componentType'));
		if ($componentType->getDomain() == 'segue-multipart')
			$component =& $this->createMultipartComponent($director, $componentType, $organizer);
		else
			$component =& $director->createSiteComponent($componentType, $organizer);
		
		$oldCellId = $organizer->putSubcomponentInCell($component, $targetCell);
		
		if (RequestContext::value('displayName'))
			$component->updateDisplayName(RequestContext::value('displayName'));
		
		if ($componentType->isEqual(new Type('segue', 'edu.middlebury', 'MenuOrganizer'))) {
			$menuTarget = RequestContext::value('menuTarget');
			if ($menuTarget == 'NewCellInNavOrg') {
				$navOrganizer =& $organizer->getParentNavOrganizer();
				$navOrganizer->updateNumColumns($navOrganizer->getNumColumns() + 1);
				$menuTarget = $navOrganizer->getId()."_cell:".($navOrganizer->getLastIndexFilled() + 1);
			}
			
			$component->updateTargetId($menuTarget);
		}
	}
	
	/**
	 * Assemble a multipart component
	 * 
	 * @param object SiteDirector $director
	 * @param object Type $componentType
	 * @param object OrganizerSiteComponent $organizer
	 * @return SiteComponent
	 * @access public
	 * @since 1/15/07
	 */
	function &createMultipartComponent ( &$director, &$componentType, &$organizer ) {
		
		switch ($componentType->getKeyword()) {
			case 'SubMenu_multipart':
				$component =& $director->createSiteComponent($director->NavBlockType, $organizer);
				$subMenu =& $director->createSiteComponent($director->MenuOrganizerType, $component);
				$navOrganizer =& $component->getOrganizer();
				
				// If the parent menu is vertical, nest the sub-menu by default.
				if (preg_match('/^(Top-Bottom|Bottom-Top)\//i', $organizer->getDirection())) {
					$component->makeNested($subMenu);
					$targetIndex = 0;
				}
				// If the parent is horizontal, put the sub-menu in the first cell
				// of the parent's nav-organizer
				else {
					$navOrganizer->addSubcomponentToCell($subMenu, 0);
					$navOrganizer->updateNumColumns(2);
					$targetIndex = 1;
				}
				
				$subMenu->updateTargetId($navOrganizer->getId()."_cell:".$targetIndex);
				$subMenu->updateDirection('Top-Bottom/Left-Right');
				break;
			case 'SidebarSubMenu_multipart':
				$component =& $director->createSiteComponent($director->NavBlockType, $organizer);
				$subMenu =& $director->createSiteComponent($director->MenuOrganizerType, $component);
				$navOrganizer =& $component->getOrganizer();
				
				// If the parent menu is vertical, nest the sub-menu by default.
				if (preg_match('/^(Top-Bottom|Bottom-Top)\//i', $organizer->getDirection())) {
					$component->makeNested($subMenu);
					$navOrganizer->updateNumColumns(2);
					$targetIndex = 0;
				}
				// If the parent is horizontal, put the sub-menu in the first cell
				// of the parent's nav-organizer
				else {
					$navOrganizer->addSubcomponentToCell($subMenu, 0);
					$navOrganizer->updateNumColumns(3);
					$targetIndex = 1;
				}
				
				$subMenu->updateTargetId($navOrganizer->getId()."_cell:".$targetIndex);
				$contentOrganizer =& $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, $targetIndex + 1);
				$subMenu->updateDirection('Top-Bottom/Left-Right');
				break;		
			case 'ContentPage_multipart':
				$component =& $director->createSiteComponent($director->NavBlockType, $organizer);
				$navOrganizer =& $component->getOrganizer();
				$contentOrganizer =& $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 0);
				break;
			case 'SidebarContentPage_multipart':
				$component =& $director->createSiteComponent($director->NavBlockType, $organizer);
				$navOrganizer =& $component->getOrganizer();
				$contentOrganizer =& $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 0);
				
				$navOrganizer->updateNumColumns(2);
				$contentOrganizer =& $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 1);
				break;
			default:
				throwError(new Error("Unknown multipart component: '".$componentType->asString()."'", __CLASS__));
		}
		
		return $component;
	}
}

?>