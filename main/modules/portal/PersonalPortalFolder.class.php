<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalPortalFolder.class.php,v 1.2 2008/04/02 17:20:36 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * The PersonalPortalFolder contains all personal sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalPortalFolder.class.php,v 1.2 2008/04/02 17:20:36 adamfranco Exp $
 */
class PersonalPortalFolder
	implements PortalFolder 
{
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Personal");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return "";
	}
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return "personal";
	}
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		$slotMgr = SlotManager::instance();
		return $slotMgr->getSlotsByType(Slot::personal);
	}
	
	/**
	 * Answer a string of controls html to go along with this folder. In many cases
	 * it will be empty, but some implementations may need controls for adding new slots.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getControlsHtml () {
		$message = '';
		try {
			$this->createNewSlotIfRequested();
		} catch (OperationFailedException $e) {
			$message = $e->getMessage();
		}
		
		// Form
		if (PersonalSlot::hasPersonal()) {
			ob_start();
			$harmoni = Harmoni::instance();
			$authN = Services::getService("AuthN");
			$harmoni->request->startNamespace('personal_slot');
			$url = $harmoni->request->quickURL();
			print "\n<form class='add_slot_form' method='post' action='$url'>";
			print "<strong>"._("Create a new placeholder:")."</strong><br/>";
			print PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			print "-";
			print "\n\t<input type='text' name='".RequestContext::name('slot_postfix')."' value='' size='10'/>";
			print "\n\t<input type='submit' value='"._('Create')."'/>";
			if (strlen($message))
				print "\n\t<div class='error'>".$message."</div>";
			print "\n</form>\n";
			print "\n\t<div style='clear: both;'></div>";
			$harmoni->request->endNamespace();
			return ob_get_clean();
		}
	}
	
	/**
	 * Answer true if the slots returned by getSlots() have already been filtered
	 * by authorization and authorization checks should only be done when printing.
	 * This method enables speed increases on long pre-sorted lists.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function slotsAreFilteredByAuthorization () {
		return false;
	}
	
	/**
	 * Answer true if the edit controls should be displayed for the sites listed.
	 * If true, this can lead to slowdowns as authorizations are checked on large
	 * lists of large sites.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function showEditControls () {
		return true;
	}
	
	/**
	 * Create a new personal slot if our form was submitted.
	 * 
	 * @return void
	 * @access private
	 * @since 4/1/08
	 */
	private function createNewSlotIfRequested () {
		$authN = Services::getService("AuthN");
		$harmoni = Harmoni::instance();
		
		// Creation of new personal slots.
		$harmoni->request->startNamespace('personal_slot');
		if (RequestContext::value('slot_postfix') && PersonalSlot::hasPersonal()) {
			try {
				$newSlotname = PersonalSlot::getPersonalShortname($authN->getFirstUserId())
					."-".RequestContext::value('slot_postfix');
				// Replace delimiting marks with an underscore
				$newSlotname = preg_replace('/[\s\/=+.,()]+/i', '_', $newSlotname);
				// Remove anything left over (other than letters/numbers/-/_)
				$newSlotname = preg_replace('/[^a-z0-9_-]/i', '', $newSlotname);
			
			
				$slot = new PersonalSlot(strtolower($newSlotname));
				$slot->addOwner($authN->getFirstUserId());
			} catch (OperationFailedException $e) {
				$harmoni->request->endNamespace();
				
				if ($e->getCode() == Slot::OWNER_EXISTS)
					throw new OperationFailedException("Placeholder '".strtolower($newSlotname)."' already exists.");
				else
					throw $e;
			}
			
			// Log this change.
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log = $loggingManager->getLogForWriting("Segue");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$item = new AgentNodeEntryItem("Create Placeholder", "New placeholder created:  '".$slot->getShortname()."'.");
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		}
		$harmoni->request->endNamespace();
	}
	
}

?>