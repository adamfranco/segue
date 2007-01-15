<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addComponent.act.php,v 1.6 2007/01/15 21:49:35 adamfranco Exp $
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
 * @version $Id: addComponent.act.php,v 1.6 2007/01/15 21:49:35 adamfranco Exp $
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
				$component->makeNested($subMenu);
				$navOrganizer =& $component->getOrganizer();
				$subMenu->updateTargetId($navOrganizer->getId()."_cell:0");
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
		}
		
		return $component;
	}
}

?>