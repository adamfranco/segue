<?php
/**
 * @since 8/4/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * This action will perform the move/copy operations requested.
 * 
 * @since 8/4/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class move_copyAction
	extends Action
{
		
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/28/08
	 */
	public function isAuthorizedToExecute () {
		$dest = $this->getDestinationComponent();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		return $authZ->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.add_children'), 
			$dest->getQualifierId());
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException(_("Your are not authorized to move/copy items here."));
		
		$director = SiteDispatcher::getSiteDirector();
		foreach (RequestContext::value('sourceIds') as $sourceId) {
			print "\n<hr/>";
			try {
				$sourceComponent = $director->getSiteComponentById($sourceId);
				switch (RequestContext::value('command')) {
					case 'copy':
						$successMessage = _("Successfully copied %1.");
						$this->copyComponent($sourceComponent);
						break;
					case 'move':
						$successMessage = _("Successfully moved %1.");
						$this->moveComponent($sourceComponent);
						break;
					case 'reference':
						$successMessage = _("Created a reference to %1.");
						$this->referenceComponent($sourceComponent);
						break;
					default:
						throw new InvalidArgumentException("Unknown command '".RequestContext::value('command')."'");
				}
				print "\n".str_replace(
					"%1", 
					htmlspecialchars($sourceComponent->getDisplayName()), 
					$successMessage);
				
				// Remove from selection?
				if (RequestContext::value('remove_after_use') == 'remove') {
					$selection = Segue_Selection::instance();
					$selection->removeSiteComponent($sourceComponent);
				}
			} catch (Exception $e) {
				print "\n".htmlspecialchars($e->getMessage());
			}
		}
		
		print "\n<br/><br/>"._("Done");
		exit;
	}
	
	/**
	 * Copy a component.
	 * 
	 * @param object SiteComponent
	 * @return void
	 * @access protected
	 * @since 8/4/08
	 */
	protected function copyComponent (SiteComponent $siteComponent) {
		throw new UnimplementedException();
	}
	
	/**
	 * Move a component.
	 * 
	 * @param object SiteComponent
	 * @return void
	 * @access protected
	 * @since 8/4/08
	 */
	protected function moveComponent (SiteComponent $siteComponent) {
		// Check that we are allow to remove the source component
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.remove_children"),
					$siteComponent->getParentComponent()->getQualifierId()))
			throw new PermissionDeniedException("You are not authorized to remove this node from its original location.");
		
		$oldParent = $siteComponent->getParentComponent();
		$oldParent->detatchSubcomponent($siteComponent);
		$this->getDestinationComponent()->addSubcomponent($siteComponent);
	}
	
	/**
	 * Reference a component.
	 * 
	 * @param object SiteComponent
	 * @return void
	 * @access protected
	 * @since 8/4/08
	 */
	protected function referenceComponent (SiteComponent $siteComponent) {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer the destination component.
	 * 
	 * @return object SiteComponent
	 * @access protected
	 * @since 8/4/08
	 */
	protected function getDestinationComponent () {
		if (!isset($this->destComponent)) {
			$director = SiteDispatcher::getSiteDirector();
			$this->destComponent = $director->getSiteComponentById(RequestContext::value('destId'));
			
			if (!$this->destComponent instanceof FlowOrganizerSiteComponent)
				throw new InvalidArgumentException("Can only move to a Pages Container or a Content Container.");
		}
		
		return $this->destComponent;
	}
}

?>