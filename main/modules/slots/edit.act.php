<?php
/**
 * @since 12/7/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit.act.php,v 1.2 2007/12/14 19:41:04 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/roles/AgentSearchSource.class.php");

/**
 * Create a new custom slot or edit an existing slot with the name specified
 * 
 * @since 12/7/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit.act.php,v 1.2 2007/12/14 19:41:04 adamfranco Exp $
 */
class editAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 12/04/07
	 */
	function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.add_children"),
 					$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 12/7/07
	 */
	public function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("slots");
		$name = strtolower(RequestContext::value("name"));
		$harmoni->request->passthrough("name");
		$harmoni->request->endNamespace();
		
		if (!preg_match('/^[a-z0-9_\-]+$/i', $name)) {
			ob_start();
			print _("Placeholder names can only contain letters, numbers, underscores, and dashes.");
			$this->getActionRows()->add(new Block(ob_get_clean(), STANDARD_BLOCK));
			return;
		}
		
		$this->runWizard (get_class($this).'_'.$name, $this->getActionRows());
	}
	
	/**
	 * Create the wizard
	 * 
	 * @return Wizard
	 * @access public
	 * @since 12/7/07
	 */
	public function createWizard () {
		$wizard = SingleStepWizard::withDefaultLayout();
		$step = $wizard->addStep("slot", new WizardStep);
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("slots");
		$name = strtolower(RequestContext::value("name"));
		$harmoni->request->endNamespace();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname($name);
		$wizard->slot = $slot;
		
		$property = $step->addComponent('name', new WTextField);
		$property->setValue($slot->getShortname());
		$property->setEnabled(false, true);
		
		$property = $step->addComponent('category', new WSelectList);
		$property->setValue($slot->getLocationCategory());
		foreach (Slot::getLocationCategories() as $category)
			$property->addOption($category, ucfirst($category));
			
		$property = $step->addComponent('owners', new WSearchList);
		$property->setSearchSource(new AgentSearchSource);
		
		$agentMgr = Services::getService("Agent");
		foreach($slot->getOwners() as $ownerId) {
			$property->addValue(new AgentSearchResult($agentMgr->getAgentOrGroup($ownerId)));
		}
		
		
		ob_start();
		
		print "\n<h4>"._("Edit Placeholder")."</h4>";
		print "\n<p><strong>"._("Placeholder Name").":</strong> [[name]]<p>";
		print "\n<p><strong>"._("Location Category").":</strong> [[category]]<p>";
		
		print "\n<p><strong>"._("Owners").":</strong> ";
		print _("Placeholder owners are people who can create a site for the placeholder and/or will be given full access to the site at the time of its creation. After the site has been created, changes to placeholder ownership will not change any roles or privileges.");
		print "[[owners]]<p>";
		
		$step->setContent(ob_get_clean());
		
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 12/7/07
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		$slot = $wizard->slot;
		
		$values = $wizard->getAllValues();
		
		try {
			$slot->setLocationCategory($values['slot']['category']);
			
			$idMgr = Services::getService("Id");
			
			$oldOwners = array();
			foreach($slot->getOwners() as $ownerId) {
				$oldOwners[] = $ownerId->getIdString();
			}
			$newOwners = $values['slot']['owners'];
			
			// Remove any needed existing owners
			foreach ($oldOwners as $idString) {
				if (!in_array($idString, $newOwners))
					$slot->removeOwner($idMgr->getId($idString));
			}
			
			// Add an needed new owners
			foreach ($newOwners as $idString) {
				if (!in_array($idString, $oldOwners))
					$slot->addOwner($idMgr->getId($idString));
			}
			
		} catch (Exception $e) {
			print $e->getMessage();
			return false;
		}
		
		return true;
	}
	
	/**
	 * Answer the return URL
	 * 
	 * @return string
	 * @access public
	 * @since 12/7/07
	 */
	public function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$harmoni->request->forget("name");
		return $harmoni->request->quickURL('slots', 'browse');
	}
	
}

?>