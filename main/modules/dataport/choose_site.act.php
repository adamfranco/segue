<?php
/**
 * @since 3/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_site.act.php,v 1.6 2008/03/24 19:28:55 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__)."/Segue1Slot.class.php");


/**
 * Choose Segue 1 sites to import into Segue2
 * 
 * @since 3/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_site.act.php,v 1.6 2008/03/24 19:28:55 adamfranco Exp $
 */
class choose_siteAction
	extends MainWindowAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/4/08
	 */
	public function isAuthorizedToExecute () {
		$authN = Services::getService("AuthN");
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 3/4/08
	 */
	public function getHeadingText () {
		return _("Import Segue 1 Sites");
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 3/4/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		$actionRows->add($this->getMessage(), "100%", null, CENTER, CENTER);
		$actionRows->add($this->getSiteTable(), "100%", null, CENTER, CENTER);
	}
	
	/**
	 * Answer a component with help text
	 * 
	 * @return Component
	 * @access public
	 * @since 3/4/08
	 */
	public function getMessage () {
		ob_start();
		print "<p>";
		print _("On this screen you can choose to import your Segue 1 sites into Segue 2.");
		print " "._("This process will not change or delete your Segue 1 site.");
		print " "._("You can only import sites into empty placeholders.");
		print "</p>";
		print "<p>";
		print _("Please note that at this time Segue 2 does not support all of the features of Segue 1.");
		print " "._("As a result, if part of your site does not import properly please view these lists of <a href='https://sourceforge.net/tracker/?group_id=82171&amp;atid=565237'>outstanding features</a> and <a href='https://sourceforge.net/tracker/?group_id=82171&amp;atid=565234'>bugs</a> to check the status of support for that feature.");
		print " "._("If you do not find a listing there, please submit a report in one of the trackers listed or email <a href='mailto:afranco@middlebury.edu'>Adam Franco</a>");
		print "</p>";
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * Answer a component that has a listing of sites.
	 * 
	 * @return Component
	 * @access protected
	 * @since 3/4/08
	 */
	protected function getSiteTable () {
		ob_start();
		print "\n<table border='1' class='dataport_choose_table'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"._("Segue 1 Sites")."</th>";
// 		print "\n\t\t\t<th>&nbsp;</th>";
		print "\n\t\t\t<th>"._("Segue 2 Sites &amp; Placeholders")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		$slotNames = $this->getSlotNames();
		if (count($slotNames)) {
			foreach ($slotNames as $slotName)
				$this->printSlotRow($slotName);
		} else {
			print "\n\t\t<tr>";
			print "\n\t\t\t<td colspan='2'>"._("You have no sites to import.")."</td>";
			print "\n\t\t</tr>";
		}
		
		print "\n\t</tbody>";
		print "\n</table>";
		
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * Print a slot row
	 * 
	 * @param string $slotName
	 * @return void
	 * @access protected
	 * @since 3/4/08
	 */
	protected function printSlotRow ($slotName) {
		print "\n\t\t<tr>";
		
		// Segue 1 slot
		$this->printSegue1Slot($slotName);
		
		// Segue 2 slot
		$this->printSegue2Slot($slotName);
		
		print "\n\t\t</tr>";
	}
	
	/**
	 * Print out a line for a Segue1 slot if it exists
	 * 
	 * @param string $slotName
	 * @return void
	 * @access protected
	 * @since 3/12/08
	 */
	protected function printSegue1Slot ($slotName) {
		try {
			$slot = $this->getSegue1Slot($slotName);
			
			print "\n\t\t\t<td class='";
// 			if ($slot->siteExists())
// 				print 'filled';
// 			else 
// 				print 'open';
			print "'>";
			
			print "\n\t<div class='slotname'>";
			print $slot->getShortname();
			print "\n\t</div>";
			
			if ($slot->siteExists()) {
				$asset = $slot->getSiteAsset();
				
				$viewUrl = DATAPORT_SEGUE1_URL.'/index.php?action=site&amp;site='.$slot->getShortname();
			
				print "\n<div class='site_info'>";
				print "\n\t<div class='site_title'>";
				print "\n\t\t<a href='".$viewUrl."' target='_blank'>";
				print "\n\t\t\t<strong>".HtmlString::getSafeHtml($asset->getDisplayName())."</strong>";
				print "\n\t\t</a>";
				print "\n\t</div>";
				
				$description = HtmlString::withValue($asset->getDescription());
				$description->trim(25);
				print  "\n\t<div class='site_description'>".$description->asString()."</div>";
				print "\n</div>";
				
				$this->printControls($slot);
			}
			
		} catch (UnknownIdException $e) {
			print "\n\t\t\t<td class='nonexistant'>";
		}
		
		print "\n\t\t\t</td>";
	}
	
	/**
	 * print out the importing controls for a Segue1 slot.
	 * 
	 * @param object Slot $slot
	 * @return void
	 * @access protected
	 * @since 3/13/08
	 */
	protected function printControls (Slot $slot) {
		$harmoni = Harmoni::instance();
		$slotMgr = SlotManager::instance();
		$url = $harmoni->request->quickURL('dataport', 'convert');
		print "\n\t\t\t\t<form action='".$url."' method='post' ";
		print " onsubmit=\"if (!this.elements['".RequestContext::name('dest_slot')."'].value) {alert('"._("Please choose a destination")."'); return false;}\"";
		print ">";
		print "\n\t\t\t\t\t<input type='hidden' name='".RequestContext::name('source_slot')."' value='".$slot->getShortname()."'/>";
		print "\n\t\t\t\t\t<input type='submit' value='"._("Import into")."'/>";
		print "\n\t\t\t\t\t<select name='".RequestContext::name('dest_slot')."'>";
		print "\n\t\t\t\t\t\t<option value=''>"._("Choose Destination")."</option>";
		foreach ($this->getSlotNames() as $destSlotname) {
			print "\n\t\t\t\t\t\t<option value='".$destSlotname."'";
			$destSlot = $slotMgr->getSlotByShortname($destSlotname);
			if ($destSlot->siteExists())
				print " disabled='disabled'";
			else if ($destSlotname == $slot->getShortname())
				print " selected='selected'";
			print ">";
			print $destSlot->getShortname()."</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t</form>";
	}
	
	/**
	 * Print out a line for a Segue2 slot if it exists
	 * 
	 * @param string $slotName
	 * @return void
	 * @access protected
	 * @since 3/12/08
	 */
	protected function printSegue2Slot ($slotName) {
		$slotMgr = SlotManager::instance();
		try {
			$slot = $slotMgr->getSlotByShortname($slotName);
			
			print "\n\t\t\t<td class='";
			if ($slot->siteExists())
				print 'filled';
			else 
				print 'open';
			print "'>";
			
			$this->printSlotInfo($slot);
			
		} catch (UnknownIdException $e) {
			print "\n\t\t\t<td class='nonexistant'>";
		}
		
		print "\n\t\t\t</td>";
	}
	
	/**
	 * Print info about a Slot
	 * 
	 * @param object Slot $slot
	 * @return void
	 * @access private
	 * @since 3/13/08
	 */
	private function printSlotInfo (Slot $slot) {
		$harmoni = Harmoni::instance();
		
		print "\n\t<div class='slotname'>";
		print $slot->getShortname();
		print "\n\t</div>";
		
		if ($slot->siteExists()) {
			$asset = $slot->getSiteAsset();
			
			// This authorization check slows things down considerably.
// 			try {
// 				$authZ = Services::getService('AuthZ');
// 				$idMgr = Services::getService('Id');
// 				if (!$authZ->isUserAuthorizedBelow(
// 					$idMgr->getId('edu.middlebury.authorization.view'),
// 					$slot->getSiteId()))
// 				{
// 					print "\n<div class='site_info'>";
// 					print  "\n\t<div class='site_description'>";
// 					print _("A site has been created for this placeholder, but you do not have authorization to view it.");
// 					print "</div>";
// 					print "\n</div>";
// 					return;
// 				}	
// 			} catch (UnknownIdException $e) {
// 			}
			
			
			$viewUrl = $harmoni->request->quickURL('ui1', 'view', array('site' => $slot->getShortname()));
			
			print "\n<div class='site_info'>";
			print "\n\t<div class='site_title'>";
			print "\n\t\t<a href='".$viewUrl."' target='_blank'>";
			print "\n\t\t\t<strong>".HtmlString::getSafeHtml($asset->getDisplayName())."</strong>";
			print "\n\t\t</a>";
			print "\n\t</div>";
			
			$description = HtmlString::withValue($asset->getDescription());
			$description->trim(25);
			print  "\n\t<div class='site_description'>".$description->asString()."</div>";
			print "\n</div>";
		}
	}
	
	/**
	 * Answer an array of slots that are either segue1 sites, segue2 sites, or segue2 placeholders.
	 *
	 * @return array of slot objects
	 * @access protected
	 * @since 3/4/08
	 */
	protected function getSlotNames () {
		if (!isset($this->allSlotNames)) {
			$this->allSlotNames = array_unique(array_merge(
				$this->getSegue1SlotNames(),
				$this->getSegue2SlotNames()));
			sort($this->allSlotNames);
		}
		return $this->allSlotNames;
	}
	
	/**
	 * @var array $allSlotNames;  
	 * @access private
	 * @since 3/13/08
	 */
	private $allSlotNames;
	
	/**
	 * Answer an array of Segue1 slots for the current user
	 *
	 * @return array of slot names
	 * @access protected
	 * @since 3/4/08
	 */
	protected function getSegue1SlotNames () {
		$slots = $this->getSegue1Slots();
		$names = array();
		foreach ($slots as $slot)
			$names[] = $slot->getShortname();
		
		return $names;
	}
	
	/**
	 * Answer an array of Segue2 slot names for the current user
	 *
	 * @return array of slot names
	 * @access protected
	 * @since 3/4/08
	 */
	protected function getSegue2SlotNames () {
		$slotMgr = SlotManager::instance();
		$slots = $slotMgr->getSlots();
		$names = array();
		foreach ($slots as $slot)
			$names[] = $slot->getShortname();
		
		return $names;
	}
	
	/**
	 * Answer an array of Segue2 slots for the current user
	 *
	 * @return array of slots
	 * @access protected
	 * @since 3/4/08
	 */
	protected function getSegue2Slots () {
		$slotMgr = SlotManager::instance();
		return $slotMgr->getSlots();	
	}
	
	/**
	 * Answer a Segue 1 slot object or throw an UnknownIdException if it doesn't exist
	 * 
	 * @param string $slotName
	 * @return object Slot
	 * @access public
	 * @since 3/12/08
	 */
	public function getSegue1Slot ($slotName) {
		foreach ($this->getSegue1Slots() as $slot) {
			if ($slot->getShortname() == $slotName)
				return $slot;
		}
		
		throw new UnknownIdException("Nothing known about Segue1 slot, '$slotName'.");
	}
	
	/**
	 * @var array $segue1Slots;  
	 * @access private
	 * @since 3/13/08
	 */
	private $segue1Slots;
	
	/**
	 * Load and return a list of Segue1 slots for the current user.
	 *
	 * @return void
	 * @access private
	 * @since 3/13/08
	 */
	private function getSegue1Slots () {
		if (!isset($this->segue1Slots) || !is_array($this->segue1Slots)) {
			// Create a session cache for the site data
			if (!isset($_SESSION['DATAPORT_SEGUE1_DATA']))
				$_SESSION['DATAPORT_SEGUE1_DATA'] = array();
			
			// Load site data from Segue1 if we don't have it cached.
			if (!isset($_SESSION['DATAPORT_SEGUE1_DATA'][$this->getSegue1UserName()])) {
				if (!defined('DATAPORT_SEGUE1_URL'))
					throw new ConfigurationErrorException('DATAPORT_SEGUE1_URL is not defined.');
				
				if (!defined('DATAPORT_SEGUE1_SECRET_KEY'))
					throw new ConfigurationErrorException('DATAPORT_SEGUE1_SECRET_KEY is not defined.');
					
				if (!defined('DATAPORT_SEGUE1_SECRET_VALUE'))
					throw new ConfigurationErrorException('DATAPORT_SEGUE1_SECRET_VALUE is not defined.');
				
				$url = DATAPORT_SEGUE1_URL.'/export/getSiteList.php?user='.$this->getSegue1UserName()
						.'&'.DATAPORT_SEGUE1_SECRET_KEY.'='.DATAPORT_SEGUE1_SECRET_VALUE;
				
				$_SESSION['DATAPORT_SEGUE1_DATA'][$this->getSegue1UserName()] = file_get_contents($url);
			}
				
			$doc = new DOMDocument;
			$success = @ $doc->loadXML($_SESSION['DATAPORT_SEGUE1_DATA'][$this->getSegue1UserName()]);
			if ($success) {
				$this->segue1Slots = array();
				$this->loadSegue1SlotsFromXml($doc);	
			} else {
				throw new OperationFailedException('Could not load Segue 1 site list.');
			}
		}
		
		return $this->segue1Slots;
	}
	
	/**
	 * Answer the username to pass to Segue1
	 * 
	 * @return string
	 * @access private
	 * @since 3/13/08
	 */
	private function getSegue1UserName () {
		$authNMgr = Services::getService('AuthN');
		$agentMgr = Services::getService('Agent');
		$agent = $agentMgr->getAgent($authNMgr->getFirstUserId());
		
		$properties = $agent->getPropertiesByType(new Type('Authentication', 'edu.middlebury.harmoni', 'Middlebury LDAP'));
		if (is_object($properties))
			return $properties->getProperty('username');
		else
			throw new OperationFailedException("Could not map a Segue 1 username for ".$agent->getDisplayName().".");
	}
	
	/**
	 * Load a list of Segue1 slots from an XML document
	 * 
	 * @param object DOMDocument $doc
	 * @return void
	 * @access private
	 * @since 3/13/08
	 */
	private function loadSegue1SlotsFromXml (DOMDocument $doc) {
		$elements = $doc->getElementsByTagName('slot');
		foreach ($elements as $element)
			$this->segue1Slots[] = new Segue1Slot($element);
	}
}

?>