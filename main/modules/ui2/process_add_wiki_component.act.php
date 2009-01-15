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
			$director = $this->getSiteDirector();
			$refNode = $director->getSiteComponentById(RequestContext::value('refNode'));
			
			// Find the right organizer above our refNode to place the type of component we want
			$componentType = HarmoniType::fromString(RequestContext::value('componentType'));
			
			// For pages and sections, find the menu above our component.
			if ($componentType->getDomain() == 'segue-multipart') {
				$parentComponent = $refNode->getParentComponent();
				while ($parentComponent) {
					if ($parentComponent->getComponentClass() == 'MenuOrganizer') {
						$this->_organizer = $parentComponent;
						return $this->_organizer;
					}
					$parentComponent = $parentComponent->getParentComponent();
				}
				
				// If we didn't find a menu above our ref node, maybe we started in a heading.
				// Search down for a menu.
				$rootNode = $director->getRootSiteComponent($refNode->getId());
				$result = $rootNode->acceptVisitor(new GetMenuBelowSiteVisitor);
				if ($result) {
					$this->_organizer = $result;
					return $this->_organizer;
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
						$this->_organizer = $parentComponent;
						return $this->_organizer;
					}
				}
				
				// If we haven't found a flow organizer above the refNode, something is wrong.
				throw new OperationFailedException("Cannot create a ".$componentType->getKeyword().". A ContentOrganizer was not found above reference node ".$refNode->getId());
			}
			
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
	
}

?>