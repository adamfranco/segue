<?php
/**
 * @since 12/7/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: edit.act.php,v 1.3 2008/01/04 19:47:55 adamfranco Exp $
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
 * @version $Id: edit.act.php,v 1.3 2008/01/04 19:47:55 adamfranco Exp $
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
		
		$property = $step->addComponent('type', new WSelectList);
		$property->setValue($slot->getType());
		foreach (array(Slot::custom, Slot::course, Slot::personal) as $type)
			$property->addOption($type, ucfirst($type));
		
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
		print "\n<p><strong>"._("Owner Definition Type").":</strong> [[type]]</p>";
		print "<div style='margin-left: 10px;'>";
		print _("The 'Owner Definition Type' indicates to the system where to search for placeholder owners. 'Course' will force a lookup in the course information system. 'Personal' will match against a user's email address. 'Custom' will not do an external lookup. <br/><br/>Note: If there is a name collision between a 'Custom' placeholder and a 'Course' placeholder. Valid 'Course' owners will still have access to the placeholder.");
		print "</div>";
		print "\n<p><strong>"._("Placeholder Name").":</strong> [[name]]</p>";
		print "<div style='margin-left: 10px;'>";
		print _("The placeholder name will be an identifier for the site. It must be globally unique. Choose wisely to avoid collisions between system-generated personal names and course names.");
		print "</div>";
		print "\n<p><strong>"._("Location Category").":</strong> [[category]]</p>";
		print "<div style='margin-left: 10px;'>";
		print _("The 'Location Category' is the Segue location in which this site will be made available. In the default installation this is disregarded, however some installations will be divided into 'main' and 'community', or other combinations. This flag is what is used in that determination.");
		print "</div>";
		
		print "\n<p><strong>"._("Owners").":</strong> </p>";
		print "<div style='margin-left: 10px;'>";
		print _("Placeholder owners are people who can create a site for the placeholder and/or will be given full access to the site at the time of its creation. After the site has been created, changes to placeholder ownership will not change any roles or privileges.");
		print "</div>";
		print "[[owners]]";
		
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
			if ($slot->getType() != $values['slot']['type']) {
				$slotMgr = SlotManager::instance();
				$slot = $slotMgr->convertSlotToType($slot, $values['slot']['type']);
			}
			
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