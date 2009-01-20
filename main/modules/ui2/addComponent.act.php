<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addComponent.act.php,v 1.12 2008/03/25 13:49:47 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/ui2/EditModeSiteAction.abstract.php");
require_once(MYDIR."/main/library/Roles/SegueRoleManager.class.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addComponent.act.php,v 1.12 2008/03/25 13:49:47 adamfranco Exp $
 */
class addComponentAction 
	extends EditModeSiteAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$organizer = $this->getTargetOrganizer();
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$organizer->getQualifierId());
	}
	
	/**
	 * Answer the organizer that we will be inserting into.
	 * 
	 * @return object OrganizerSiteComponent
	 * @access protected
	 * @since 1/15/09
	 */
	protected function getTargetOrganizer () {
		if (!isset($this->_organizer)) {
			$director = $this->getSiteDirector();
			$this->_organizer = $director->getSiteComponentById(RequestContext::value('organizerId'));
		}
		
		return $this->_organizer;
	}
	
	/**
	 * Answer the cell that we will be inserting into
	 * 
	 * @return int
	 * @access protected
	 * @since 1/15/09
	 */
	protected function getTargetCell () {
		if (is_null(RequestContext::value('cellIndex')))
			return null;
		else
			return intval(RequestContext::value('cellIndex'));
	}
	
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function processChanges ( SiteDirector $director ) {
		$targetCell = $this->getTargetCell();
		$organizer = $this->getTargetOrganizer();
		
		$componentType = HarmoniType::fromString(RequestContext::value('componentType'));
		if ($componentType->getDomain() == 'segue-multipart') {
			$component = self::createMultipartComponent($director, $componentType, $organizer);
			$this->newIdToSendTo = $component->getId();
		} else
			$component = $director->createSiteComponent($componentType, $organizer);
		
		if (!is_null($targetCell))
			$oldCellId = $organizer->putSubcomponentInCell($component, $targetCell);
		
		if (RequestContext::value('displayName'))
			$component->updateDisplayName(RequestContext::value('displayName'));
		
		if ($componentType->isEqual(new Type('segue', 'edu.middlebury', 'MenuOrganizer'))) {
			$menuTarget = RequestContext::value('menuTarget');
			if ($menuTarget == 'NewCellInNavOrg') {
				$navOrganizer = $organizer->getParentNavOrganizer();
				$navOrganizer->updateNumColumns($navOrganizer->getNumColumns() + 1);
				$menuTarget = $navOrganizer->getId()."_cell:".($navOrganizer->getLastIndexFilled() + 1);
			}
			
			$component->updateTargetId($menuTarget);
		}
		
		// Check the Role of the user. If it is less than 'Editor', make them an editor
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getUsersRole($component->getQualifierId(), true);
		$editor = $roleMgr->getRole('editor');
		if ($role->isLessThan($editor))
			$editor->applyToUser($component->getQualifierId(), true);
	}
	
	/**
	 * Assemble a multipart component
	 * 
	 * @param object SiteDirector $director
	 * @param object Type $componentType
	 * @param object OrganizerSiteComponent $organizer
	 * @return SiteComponent
	 * @access public
	 * @static
	 * @since 1/15/07
	 */
	static function createMultipartComponent ( $director, $componentType, $organizer ) {
		
		switch ($componentType->getKeyword()) {
			case 'SubMenu_multipart':
				$component = $director->createSiteComponent($director->NavBlockType, $organizer);
				$subMenu = $director->createSiteComponent($director->MenuOrganizerType, $component);
				$navOrganizer = $component->getOrganizer();
				
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
				$component = $director->createSiteComponent($director->NavBlockType, $organizer);
				$subMenu = $director->createSiteComponent($director->MenuOrganizerType, $component);
				$navOrganizer = $component->getOrganizer();
				
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
				$contentOrganizer = $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, $targetIndex + 1);
				$subMenu->updateDirection('Top-Bottom/Left-Right');
				$contentOrganizer->updateWidth("200px");
				break;		
			case 'ContentPage_multipart':
				$component = $director->createSiteComponent($director->NavBlockType, $organizer);
				$navOrganizer = $component->getOrganizer();
				$contentOrganizer = $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 0);
				break;
			case 'SidebarContentPage_multipart':
				$component = $director->createSiteComponent($director->NavBlockType, $organizer);
				$navOrganizer = $component->getOrganizer();
				$contentOrganizer = $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 0);
				
				// Sidebar
				$navOrganizer->updateNumColumns(2);
				$contentOrganizer = $director->createSiteComponent($director->FlowOrganizerType, $navOrganizer);
				$navOrganizer->putSubcomponentInCell($contentOrganizer, 1);
				$contentOrganizer->updateWidth("200px");
				break;
			default:
				throwError(new Error("Unknown multipart component: '".$componentType->asString()."'", __CLASS__));
		}
		
		return $component;
	}
}

?>