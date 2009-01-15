<?php
/**
 * @since 1/15/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/addComponent.act.php');

/**
 * Create a new page based from a wiki-link
 * 
 * @since 1/15/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class process_add_wiki_componentAction
	extends addComponentAction
{
		
	/**
	 * Answer the organizer that we will be inserting into.
	 * 
	 * @return object OrganizerSiteComponent
	 * @access protected
	 * @since 1/15/09
	 */
	protected function getTargetOrganizer () {
		if (!isset($this->_organizer)) {
			$director = SiteDispatcher::getSiteDirector();
			$refNode = $director->getSiteComponentById(RequestContext::value('refNode'));
			
			// Find the right organizer above our refNode to place the type of component we want
			$componentType = HarmoniType::fromString(RequestContext::value('componentType'));
			
			$this->_organizer = self::getOrganizerForComponentType($refNode, $componentType);
			
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
		// We will always be appending, so aways return null
		return null;
	}
	
	/**
	 * Answer the organizer above the reference node that can accept the component type given.
	 * 
	 * @param object SiteComponent $refNode
	 * @param object Type $componentType
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 1/15/09
	 * @static
	 */
	public static function getOrganizerForComponentType (SiteComponent $refNode, Type $componentType) {
		// For pages and sections, find the menu above our component.
		if ($componentType->getDomain() == 'segue-multipart') {
			$parentComponent = $refNode->getParentComponent();
			while ($parentComponent) {
				if ($parentComponent->getComponentClass() == 'MenuOrganizer') {
					return $parentComponent;
				}
				$parentComponent = $parentComponent->getParentComponent();
			}
			
			// If we didn't find a menu above our ref node, maybe we started in a heading.
			// Search down for a menu.
			$director = SiteDispatcher::getSiteDirector();
			$rootNode = $director->getRootSiteComponent($refNode->getId());
			$result = $rootNode->acceptVisitor(new GetMenuBelowSiteVisitor);
			if ($result) {
				return $result;
			}
			
			// If we still haven't found a menu, then there isn't one in this site.
			// Nothing more we can do.
			throw new OperationFailedException("Cannot create a ".$componentType->getKeyword().". Site ".$rootNode->getSlot()->getShortname()." - '".$rootNode->getDisplayName()."' does not have any menus to add this component to.");
		}
		// Otherwise, use the content organizer above our component
		else {
			$parentComponent = $refNode->getParentComponent();
			while ($parentComponent) {
				if ($parentComponent->getComponentClass() == 'FlowOrganizer' || $parentComponent->getComponentClass() == 'MenuOrganizer') 
				{
					return $parentComponent;
				}
			}
			
			// If we haven't found a flow organizer above the refNode, something is wrong.
			throw new OperationFailedException("Cannot create a ".$componentType->getKeyword().". A ContentOrganizer was not found above reference node ".$refNode->getId());
		}
	}
	
}

?>