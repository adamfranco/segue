<?php
/**
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.18 2007/12/14 21:37:05 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * 
 * 
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.18 2007/12/14 21:37:05 adamfranco Exp $
 */
class listAction 
	extends MainWindowAction
{

	/**
	 * @var array $sitesPrinted;  
	 * @access public
	 * @since 8/23/07
	 */
	public static $sitesPrinted = array();
	
	/**
	 * @var array $slotsPrinted;  
	 * @access public
	 * @since 8/23/07
	 */
	public static $slotsPrinted = array();
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return $this->getUIModeForm()._("Your Portal")."\n\t<div style='clear: both; height: 0px;'>&nbsp;</div>";
	}
	
	/**
	 * Answer the UI mode form
	 * 
	 * @return string
	 * @access private
	 * @since 12/14/07
	 */
	private function getUIModeForm () {
		$harmoni = Harmoni::instance();

		if (RequestContext::value('user_interface')) {
			$this->setUiModule(RequestContext::value('user_interface'));
		}
		
		ob_start();
		
		// UI selection
		print "\n\t<form action='".$harmoni->request->quickURL()."' method='post' style='float: right;'>";
		$options = array ('ui1' => _("Classic Mode"), 'ui2' => _("New Mode"));
		print "\n\t\t<select style='font-size: 10px' name='".RequestContext::name('user_interface')."'";
		print " onchange='this.form.submit();'";
		print ">";
		foreach ($options as $key => $val) {
			print "\n\t\t\t<option value='$key'";
			print (($this->getUiModule() == $key)?" selected='selected'":"");
			print ">$val</option>";
		}
		print "\n\t\t</select>";
		print "\n\t</form>";
		return ob_get_clean();
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$repositoryManager = Services::getService("Repository");
		$idManager = Services::getService("Id");
		$authZ = Services::getService("AuthZ");
		$authN = Services::getService("AuthN");
		$harmoni = Harmoni::instance();
		
		// Creation of new personal slots
		$harmoni->request->startNamespace('personal_slot');
		if (RequestContext::value('slot_postfix') && PersonalSlot::hasPersonal()) {
			$newSlotname = PersonalSlot::getPersonalShortname($authN->getFirstUserId())
				."-".RequestContext::value('slot_postfix');
			// Replace delimiting marks with an underscore
			$newSlotname = preg_replace('/[\s\/=+.,()]+/i', '_', $newSlotname);
			// Remove anything left over (other than letters/numbers/-/_)
			$newSlotname = preg_replace('/[^a-z0-9_-]/i', '', $newSlotname);
			
			$slot = new PersonalSlot(strtolower($newSlotname));
			$slot->addOwner($authN->getFirstUserId());
		}
		$harmoni->request->endNamespace();
		
		$actionRows = $this->getActionRows();
				
		
		
		$repository = $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		
		$siteType = new HarmoniType('segue', 
							'edu.middlebury', 
							'SiteNavBlock', 
							'An Asset of this type is the root node of a Segue site.');
		
		$courseMgr = SegueCourseManager::instance();
		$slotMgr = SlotManager::instance();
		
		/*********************************************************
		 * Future Classes
		 *********************************************************/
		$courses = $courseMgr->getUsersFutureCourses(SORT_DESC);
		if (count($courses)) {
			$actionRows->add(new Heading(_("Future Classes"), 2));
			foreach ($courses as $course) {
				$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
				self::$slotsPrinted[] = $slot->getShortname();
				$actionRows->add($this->printSlot($slot), null, null, LEFT, CENTER);
			}
		}
		
		/*********************************************************
		 * Current Classes
		 *********************************************************/
		$courses = $courseMgr->getUsersCurrentCourses(SORT_DESC);
		if (count($courses)) {
			$actionRows->add(new Heading(_("Current Classes"), 2));
			foreach ($courses as $course) {
				$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
				self::$slotsPrinted[] = $slot->getShortname();
				$actionRows->add($this->printSlot($slot), null, null, LEFT, CENTER);
			}
		}
		
		/*********************************************************
		 * Past Classes
		 *********************************************************/
		$courses = $courseMgr->getUsersPastCourses(SORT_DESC);
		if (count($courses)) {
			$actionRows->add(new Heading(_("Past Classes"), 2));
			foreach ($courses as $course) {
				$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
				self::$slotsPrinted[] = $slot->getShortname();
				$actionRows->add($this->printSlot($slot), null, null, LEFT, CENTER);
			}
		}
		
		/*********************************************************
		 * Personal Slots owned by the user
		 *********************************************************/
		ob_start();
		
		if (PersonalSlot::hasPersonal()) {
			$harmoni->request->startNamespace('personal_slot');
			$url = $harmoni->request->quickURL();
			print "\n<form class='add_slot_form' method='post' action='$url'>";
			print "<strong>"._("Create a new placeholder:")."</strong><br/>";
			print PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			print "-";
			print "\n\t<input type='text' name='".RequestContext::name('slot_postfix')."' value='' size='10'/>";
			print "\n\t<input type='submit' value='"._('Create')."'/>";
			print "\n</form>\n";
			$harmoni->request->endNamespace();
		}
		
		$slotComponents = array();
		foreach ($slotMgr->getSlotsByType(Slot::personal) as $slot) {
			if (!in_array($slot->getShortName(), self::$slotsPrinted)) {
				self::$slotsPrinted[] = $slot->getShortname();
				$slotComponents[] = $this->printSlot($slot);
			}
		}
		
		if (PersonalSlot::hasPersonal() || count($slotComponents)) {
			print _("Personal Sites");
			$actionRows->add(new Heading(ob_get_clean(), 2));
			foreach ($slotComponents as $component) {
				$actionRows->add($component, null, null, LEFT, CENTER);
			}
		} else {
			ob_end_clean();
		}
		
		/*********************************************************
		 * Other Slots owned by the user
		 *********************************************************/
		$slotComponents = array();
		
		foreach ($slotMgr->getSlots() as $slot) {
			if (!in_array($slot->getShortName(), self::$slotsPrinted)) {
				self::$slotsPrinted[] = $slot->getShortname();
				$slotComponents[] = $this->printSlot($slot);
			}
		}
		
		if (count($slotComponents)) {
			$actionRows->add(new Heading(_("Other Sites"), 2));
			foreach ($slotComponents as $component) {
				$actionRows->add($component, null, null, LEFT, CENTER);
			}
		}
		
		/*********************************************************
		 * All other Sites
		 *********************************************************/
		$actionRows->add(new Heading(_("All Other Sites You Can View"), 2));
		$assets = $repository->getAssetsByType($siteType);
		
		
		// Print out the results
		$resultPrinter = new IteratorResultPrinter($assets, 1, 10, "printSiteShort", $this);
		$resultLayout = $resultPrinter->getLayout("canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
	
	/**
	 * Print out a slot
	 * 
	 * @param object Slot $slot
	 * @return void
	 * @access protected
	 * @since 8/22/07
	 */
	protected function printSlot ( Slot $slot ) {
		if ($slot->getSiteId()) {
			return printSiteShort($slot->getSiteAsset(), $this, 0); 			
		} 
		// If no site is created
		else {
			ob_start();
			print $slot->getShortname();
			print " - ";
			if ($slot->isUserOwner()) {
				$harmoni = Harmoni::instance();
				print " <a href='".$harmoni->request->quickURL($this->getUiModule(), 'add', array('slot' => $slot->getShortname()))."' class='create_site_link'>"._("Create Site")."</a>";
				
				$authN = Services::getService("AuthN");
				if ($slot->getType() == Slot::personal &&
					$slot->getShortName() != PersonalSlot::getPersonalShortname($authN->getFirstUserId())) 
				{
					$harmoni = Harmoni::instance();
					$harmoni->request->startNamespace("slots");
					print " | <a href='";
					print $harmoni->request->quickURL('slots', 'delete', 
						array('name' => $slot->getShortName(), 
							'returnModule' => $harmoni->request->getRequestedModule(),
							'returnAction' => $harmoni->request->getRequestedAction()));
					print "'";
					print " onclick=\"return confirm('"._("Are you sure that you want to delete this placeholder?")."');\" ";
					print ">"._("delete placeholder")."</a>";
					$harmoni->request->endNamespace();
				}
			} else {
				print " <span class='site_not_created_message'>"._("No Site Created")."</span>";
			}
			return new Block(ob_get_clean(), EMPHASIZED_BLOCK);
		}
	}
	
	/**
	 * Answer the current UI module
	 * 
	 * @return string
	 * @access public
	 * @since 7/27/07
	 */
	function getUiModule () {
		if (!isset($_SESSION['UI_MODULE']))
			$this->setUiModule('ui1');
			
		return $_SESSION['UI_MODULE'];
	}
	
	/**
	 * Set the UI module
	 * 
	 * @param string $module
	 * @return void
	 * @access public
	 * @since 7/27/07
	 */
	function setUiModule ($module) {
		$_SESSION['UI_MODULE'] = $module;
	}
}

/**
 * return true if the current user can view this asset
 * 
 * @param object Asset $asset
 * @return boolean
 * @access public
 * @since 1/18/06
 */
function canView( $asset ) {
	// Filter out any sites we have already printed.
	if (in_array($asset->getId()->getIdString(), listAction::$sitesPrinted))
		return false;
	
	$authZ = Services::getService("AuthZ");
	$idManager = Services::getService("Id");
	
	if ($authZ->isUserAuthorizedBelow($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * 
 * 
 * @param <##>
 * @return <##>
 * @access public
 * @since 1/18/06
 */
function printSiteShort($asset, $action, $num) {
	$harmoni = Harmoni::instance();
	$assetId = $asset->getId();
	
	listAction::$sitesPrinted[] = $assetId->getIdString();
			
	$container = new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC = new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
// 	$fillContainerSC->addSP(new WidthSP("100%"));
// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
	$container->addStyle($fillContainerSC);
	
	$centered = new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	// Use the alias instead of the Id if it is available.
	$slotManager = SlotManager::instance();
	try {
		$slot = $slotManager->getSlotBySiteId($assetId);
		$params = array('site' => $slot->getShortname());
	} catch (Exception $e) {
		$params = array('node' => $assetId->getIdString());
	}
	$viewUrl = $harmoni->request->quickURL($action->getUiModule(), 'view', $params);
	
	// Print out the content
	ob_start();
	print "\n\t<div class='portal_list_slotname'>";
	if (isset($slot)) {
		print $slot->getShortname();
	} else {
		print _("ID#").": ".$assetId->getIdString();
	}
	print "\n\t</div>";
	print "\n\t<div class='portal_list_site_title'>";
	print "\n\t\t<a href='".$viewUrl."'>";
	print "\n\t\t\t<strong>".htmlspecialchars(HtmlString::getSafeHtml($asset->getDisplayName()))."</strong>";
	print "\n\t\t</a>";
	print "\n\t</div>";
	
	print "\n\t<div class='portal_list_controls'>";
	print "\n\t<a href='".$viewUrl."'>"._("view")."</a>";
	print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
	if ($action->getUiModule() == 'ui2') {
		print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'arrangeview', array('node' => $assetId->getIdString()))."'>"._("arrange")."</a>";
	}
	print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'deleteComponent', array('node' => $assetId->getIdString()))."' onclick=\"if (!confirm('"._("Are you sure that you want to permenantly delete this site?")."')) { return false; }\">"._("delete")."</a>";
	print "\n\t</div>";
	
	$description = HtmlString::withValue($asset->getDescription());
	$description->trim(25);
	print  "\n\t<div class='portal_list_site_description'>".$description->asString()."</div>";	
	
	$component = new UnstyledBlock(ob_get_contents());
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	return $container;
}

?>