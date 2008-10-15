<?php
/**
 * @since 12/4/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.8 2008/03/21 18:28:21 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/ResultPrinter/TableIteratorResultPrinter.class.php");

/**
 * An action for browsing slots.
 * 
 * @since 12/4/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse.act.php,v 1.8 2008/03/21 18:28:21 adamfranco Exp $
 */
class browseAction
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 12/04/07
	 */
	function getHeadingText () {
		return _("Browse Placeholders");
	}
	
	/**
	 * Build the content of this action
	 * 
	 * @return object
	 * @access public
	 * @since 12/4/07
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		
		$actionRows->add($this->getCreateForm());
// 		$actionRows->add($this->getSearchForm());
		$actionRows->add($this->getSlotList());
		
		return $actionRows;
	}
	
	/**
	 * Answer a component containing a search form
	 *
	 * @return object Component
	 * @access private
	 * @since 12/4/07
	 */
	private function getSearchForm () {
		ob_start();
		
		
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * Answer the "create slot form"
	 * 
	 * @return object Component
	 * @access public
	 * @since 12/7/07
	 */
	public function getCreateForm () {
		ob_start();
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough('starting_number');
		$harmoni->request->startNamespace("slots");
		print "\n\t<form action='".$harmoni->request->quickURL('slots', 'edit')."' method='post'>";
		print "\n\t\t<div><strong>"._("Create/Edit a Placholder").": </strong></div>";
		print "\n\t\t<div>";
		print "\n\t\t\t"._("Placeholder Name").": ";
		print "<input type='text' name='".RequestContext::name('name')."'/>";
		print "\n\t\t\t<input type='submit' value='"._("Create/Edit &raquo;")."'/>";
		print "\n\t\t</div>";
		print "\n\t</form>";
		$harmoni->request->endNamespace();
		$harmoni->request->forget('starting_number');
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * Answer a component containing either all slots or the search results.
	 *
	 * @return object Component
	 * @access private
	 * @since 12/4/07
	 */
	private function getSlotList () {
		$slotMgr = SlotManager::instance();
		if ($this->getSearchTerm())
			$slots = $this->getSlotsBySearch($this->getSearchTerm());
		else
			$slots = $slotMgr->getAllSlots();
		
		$headRow = "
	<tr>
		<th>"._("Placeholder Name")."</th>
		<th>"._("Type")."</th>
		<th>"._("Category")."</th>
		<th>"._("Media Quota")."</th>
		<th>"._("Site Exists")."</th>
		<th>"._("Owners")."</th>
		<th>"._("Actions")."</th>
	</tr>";
		$printer = new TableIteratorResultPrinter($slots, $headRow, 50, array($this, 'getSlotComponent'));
		return new Block($printer->getTable(), STANDARD_BLOCK);
	}
	
	/**
	 * Answer the search term.
	 * 
	 * @return string
	 * @access private
	 * @since 12/4/07
	 */
	private function getSearchTerm () {
		return null;
	}
	
	/**
	 * Print out a slot
	 * 
	 * @param object $slot
	 * @return object Component
	 * @access public
	 * @since 12/4/07
	 */
	public function getSlotComponent (Slot $slot) {
		$harmoni = Harmoni::instance();
		ob_start();
		print "\n\t<tr>";
		print "\n\t\t<td>";
		if ($slot->siteExists()) {
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('ui1', 'view', array('site' => $slot->getShortname()));
			print "' target='_blank'>".$slot->getShortname()."</a>";
		} else {
			print $slot->getShortname();
		}
		print "</td>";
		print "\n\t\t<td>".$slot->getType()."</td>";
		print "\n\t\t<td>".$slot->getLocationCategory()."</td>";
		
		// Media Quota
		print "\n\t\t<td>";
		if ($slot->usesDefaultMediaQuota()) {
			$quota = SlotAbstract::getDefaultMediaQuota()->asString();
			print "<a href='#' title='$quota' onclick='alert(\"$quota\"); return false;'>";
			print _("Default");
			print "</a>";
		} else
			print $slot->getMediaQuota()->asString();
		print "</td>";
		
		print "\n\t\t<td style='text-align: center'>".(($slot->siteExists())?"yes":'')."</td>";
		print "\n\t\t<td>";
		$owners = $slot->getOwners();
		$ownerStrings = array();
		$agentMgr = Services::getService('Agent');
		foreach ($owners as $ownerId)
			$ownerStrings[] = $agentMgr->getAgent($ownerId)->getDisplayName();
			
		print implode("; ", $ownerStrings);
		print "</td>";
		
		$harmoni->request->passthrough('starting_number');
		$harmoni->request->startNamespace("slots");
		print "\n\t\t<td style='white-space: nowrap;'>";
		print "\n\t\t\t<a href='";
		print $harmoni->request->quickURL('slots', 'edit', array('name' => $slot->getShortname()));
		print "'>"._("edit")."</a>";
		if (!$slot->siteExists()) {
			$harmoni->request->startNamespace(null);
			print "\n\t\t\t| <a href='";
			print $harmoni->request->quickURL('dataport', 'import', array('site' => $slot->getShortname()));
			print "'>"._("import")."</a>";
			$harmoni->request->endNamespace();
			
			print "\n\t\t\t| <a href='";
			print $harmoni->request->quickURL('slots', 'delete', array('name' => $slot->getShortname()));
			print "' onclick=\"";
			print "return confirm('"._('Are you sure you want to delete this placeholder?')."');";
			print "\">"._("delete")."</a>";
		} else {
			$harmoni->request->startNamespace(null);
			print "\n\t\t\t| <a href='";
			print $harmoni->request->quickURL('dataport', 'export', array('node' => $slot->getSiteId()->getIdString()));
			print "'>"._("export")."</a>";
			$harmoni->request->endNamespace();
		}
		print "\n\t\t</td>";
		$harmoni->request->endNamespace();
		$harmoni->request->forget('starting_number');

		print "\n\t</tr>";
		return ob_get_clean();
	}
}

?>