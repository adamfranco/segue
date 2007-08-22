<?php
/**
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.5 2007/08/22 20:08:51 adamfranco Exp $
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
 * @version $Id: list.act.php,v 1.5 2007/08/22 20:08:51 adamfranco Exp $
 */
class listAction 
	extends MainWindowAction
{
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
		return _("Your Portal");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		
		if (RequestContext::value('user_interface')) {
			$this->setUiModule(RequestContext::value('user_interface'));
		}
		
		ob_start();
		
		// UI selection
		print "\n\t<form action='".$harmoni->request->quickURL()."' method='post' style='float: right;'>";
		$options = array ('ui1' => _("Classic Mode"), 'ui2' => _("New Mode"));
		print "\n\t\t<select name='".RequestContext::name('user_interface')."'>";
		foreach ($options as $key => $val) {
			print "\n\t\t\t<option value='$key'";
			print (($this->getUiModule() == $key)?" selected='selected'":"");
			print ">$val</option>";
		}
		print "\n\t\t</select>";
		print "\n\t\t<input type='submit' value='"._('Set interface')."'/>";
		print "\n\t</form>";
		
		// Create Site Button
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.segue.sites_repository")))
		{
			
			print "\n\t<div><a href='";
			print $harmoni->request->quickURL($this->getUiModule(), "add");
			print "' style='border: 1px solid; padding: 2px; text-align: center; text-decoration: none; margin: 2px;'>";
			print _("Create New Site");
			print "</a></div>";
			
		}
		
		print "\n\t<div style='clear: both;'></div>";
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, RIGHT, CENTER);
		
		
		
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		
		$siteType = new HarmoniType('segue', 
							'edu.middlebury', 
							'SiteNavBlock', 
							'An Asset of this type is the root node of a Segue site.');
		
		$slotsPrinted = array();
		$courseMgr = SegueCourseManager::instance();
		$slotMgr = SlotManager::instance();
		
		/*********************************************************
		 * Future Classes
		 *********************************************************/
		$actionRows->add(new Heading(_("Future Classes"), 2));
		ob_start();
		foreach ($courseMgr->getUsersFutureCourses(SORT_DESC) as $course) {
			$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
			$slotsPrinted[] = $slot->getShortname();
			$this->printSlot($slot);
		}
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, CENTER);
		
		/*********************************************************
		 * Current Classes
		 *********************************************************/
		$actionRows->add(new Heading(_("Current Classes"), 2));
		ob_start();
		foreach ($courseMgr->getUsersCurrentCourses(SORT_DESC) as $course) {
			$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
			$slotsPrinted[] = $slot->getShortname();
			$this->printSlot($slot);
		}
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, CENTER);
		
		/*********************************************************
		 * Past Classes
		 *********************************************************/
		$actionRows->add(new Heading(_("Past Classes"), 2));
		ob_start();
		foreach ($courseMgr->getUsersPastCourses(SORT_DESC) as $course) {
			$slot = $slotMgr->getSlotByShortname($course->getId()->getIdString());
			$slotsPrinted[] = $slot->getShortname();
			$this->printSlot($slot);
		}
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, CENTER);
		
		/*********************************************************
		 * Personal Slots owned by the user
		 *********************************************************/
		$actionRows->add(new Heading(_("Personal Sites"), 2));
		ob_start();
		foreach ($slotMgr->getSlotsByType(Slot::personal) as $slot) {
			if (!in_array($slot->getShortName(), $slotsPrinted)) {
				$slotsPrinted[] = $slot->getShortname();
				$this->printSlot($slot);
			}
		}
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, CENTER);
		
		/*********************************************************
		 * Other Slots owned by the user
		 *********************************************************/
		$actionRows->add(new Heading(_("Other Sites"), 2));
		ob_start();
		foreach ($slotMgr->getAllSlots() as $slot) {
			if (!in_array($slot->getShortName(), $slotsPrinted)) {
				$slotsPrinted[] = $slot->getShortname();
				$this->printSlot($slot);
			}
		}
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, LEFT, CENTER);
		
		/*********************************************************
		 * All Sites
		 *********************************************************/
		$actionRows->add(new Heading(_("All Sites"), 1));
		$assets =& $repository->getAssetsByType($siteType);
		
		
		// Print out the results
		$resultPrinter =& new IteratorResultPrinter($assets, 1, 10, "printSiteShort", $this);
		$resultLayout =& $resultPrinter->getLayout("canView");
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
		print "\n<div class='slot_row'>";
		print $slot->getShortname();
		if ($slot->getSiteId()) {
			printpre("SiteId: ".$slot->getSiteId()->getIdString());
// 			$site = $slot->getSite();
		} 
		// If no site is created
		else {
			if ($slot->isUserOwner()) {
				print " <a href='' class='create_site_link'>"._("Create Site")."</a>";
			} else {
				print " <span class='site_not_created_message'>"._("No Site Created")."</span>";
			}
		
		}
		
		print "\n</div>";
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
function canView( & $asset ) {
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	
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
function printSiteShort(& $asset, &$action, $num) {
	$harmoni =& Harmoni::instance();
	$assetId =& $asset->getId();
			
	$container =& new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
// 	$fillContainerSC->addSP(new WidthSP("100%"));
// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	// Use the alias instead of the Id if it is available.
	$slotManager =& SlotManager::instance();
	try {
		$slot = $slotManager->getSlotBySiteId($assetId);
		$params = array('site' => $slot->getShortname());
	} catch (Exception $e) {
		$params = array('node' => $assetId->getIdString());
	}
	$viewUrl = $harmoni->request->quickURL($action->getUiModule(), 'view', $params);
	
	// Print out the content
	ob_start();
	print "\n\t<a href='".$viewUrl."'>";
	print "\n\t<strong>".htmlspecialchars($asset->getDisplayName())."</strong>";
	print "\n\t</a>";
	print "\n\t<br/>"._("ID#").": ".$assetId->getIdString();
	print "\n\t<a href='".$viewUrl."'>"._("view")."</a>";
	print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
	if ($action->getUiModule() == 'ui2') {
		print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'arrangeview', array('node' => $assetId->getIdString()))."'>"._("arrange")."</a>";
	}
	print "\n\t | <a href='".$harmoni->request->quickURL($action->getUiModule(), 'deleteComponent', array('node' => $assetId->getIdString()))."'>"._("delete")."</a>";
	
	$description =& HtmlString::withValue($asset->getDescription());
	$description->trim(25);
	print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";	
	
	$component =& new UnstyledBlock(ob_get_contents());
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	return $container;
}

?>