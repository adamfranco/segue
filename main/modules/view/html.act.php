<?php
/**
 * @since 3/17/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: html.act.php,v 1.13 2008/04/10 19:18:04 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/rss/RssLinkPrinter.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/view/ViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/DetailViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsBlockVisitor.class.php");
// require_once(MYDIR."/main/modules/ui1/Rendering/EditModeSiteVisitor.class.php");
//require_once(MYDIR."/main/modules/ui2/Rendering/EditModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");

/**
 * action for viewing Segue sites in a standard web browser
 * 
 * @since 3/17/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: html.act.php,v 1.13 2008/04/10 19:18:04 achapin Exp $
 */

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: html.act.php,v 1.13 2008/04/10 19:18:04 achapin Exp $
 */
class htmlAction
	extends displayAction 
{
	
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/24/07
	 */
	public function isAuthorizedToExecute () {
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.view'),
			SiteDispatcher::getCurrentNode()->getQualifierId());
	}
	
	/**
	 * Answer a message in the case of no authorization
	 * 
	 * @return string
	 * @access public
	 * @since 2/28/08
	 */
	public function getUnauthorizedMessage () {
		$message = _("You are not authorized to view the requested node.");
		$message .= "\n<br/>";
		$authNMgr = Services::getService("AuthN");
		if (!$authNMgr->isUserAuthenticatedWithAnyType())
			$message .= _("Please log in or use your browser's 'Back' Button.");
		else
			$message .= _("Please use your browser's 'Back' Button.");
		
		return $message;
	}
	
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/3/06
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		
		/*********************************************************
		 * Split sites based on their location-category
		 *********************************************************/
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		try {
			$slot = $rootSiteComponent->getSlot();
			if (SiteDispatcher::getBaseUrlForSlot($slot) != MYURL) {
				RequestContext::sendTo(SiteDispatcher::quickUrl());
			}
		} catch (UnknownIdException $e) {		// No slot for the site....
		}
		
		$mainScreen = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);
		
		$allWrapper = $this->addHeaderControls($mainScreen);
				
		$this->addSiteContent($mainScreen);
		$this->addFooterControls($allWrapper);

		
		$this->mainScreen = $mainScreen;
		return $allWrapper;
	}
	
	/**
	 * Add the header controls to the main screen gui component
	 * 
	 * @param object Component $mainScreen
	 * @return object Component The allWrapper
	 * @access public
	 * @since 4/7/08
	 */
	public function addHeaderControls (Component $mainScreen) {
		$harmoni = Harmoni::instance();
				
		/*********************************************************
		 * Additional setup
		 *********************************************************/
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		
		$outputHandler = $harmoni->getOutputHandler();
		
		/*********************************************************
		 * Theme
		 *********************************************************/
		$outputHandler->setCurrentTheme($rootSiteComponent->getTheme());
		
		
		/*********************************************************
		 * Other headers and footers
		 *********************************************************/
		// Remove any existing title tags from the head text
		print preg_replace("/<title>[^<]*<\/title>/", "", $outputHandler->getHead());
		
		//Add our new title
		print "\n\t\t<title>";
		print strip_tags(SiteDispatcher::getCurrentNode()->acceptVisitor(new BreadCrumbsVisitor(SiteDispatcher::getCurrentNode())));
		print "</title>";
		
		// Add our common Harmoni javascript libraries
		require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");
		
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/TabbedContent.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/prototype.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/js_quicktags.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/brwsniff.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/MediaLibrary.js'></script>";
		print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/MediaLibrary.css'/>";
		
		$outputHandler->setHead(ob_get_clean());
		
		
		// Add the RSS head links
		RssLinkPrinter::addHeadLinks(SiteDispatcher::getCurrentNode());
		
		$allWrapper = new Container(new YLayout, BLANK, 1);
		
		
		// :: login, links and commands
		$this->headRow = $allWrapper->add(
			new Container(new XLayout, BLANK, 1), 
			$rootSiteComponent->getWidth(), null, CENTER, TOP);
			
		$this->leftHeadColumn = $this->headRow->add(
			$this->getSegueLinksComponent(), 
				null, null, LEFT, TOP);
		
		$rightHeadColumn = $this->headRow->add(
			new Container(new YLayout, BLANK, 1), 
			null, null, CENTER, TOP);

		$rightHeadColumn->add($this->getLoginComponent(), 
				null, null, RIGHT, TOP);
		
				
		if ($this->isAuthorizedToExecute()) {
			$rightHeadColumn->add($this->getCommandsComponent(), 
				null, null, RIGHT, TOP);
		}
		
				
		$allWrapper->add($mainScreen,
			$rootSiteComponent->getWidth(), null, CENTER, TOP);
		
				
		// :: Top Row ::
// 		$this->headRow = $mainScreen->add(
// 			new Container(new XLayout, HEADER, 1), 
// 			"100%", null, CENTER, TOP);
		
// 		$this->leftHeadColumn = $this->headRow->add(
// 			new UnstyledBlock("<h1>".$rootSiteComponent->getTitleMarkup()."</h1>"),
// 			null, null, LEFT, TOP);

		return $allWrapper;
	}
	
	/**
	 * Add the site content gui components
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function addSiteContent (Component $mainScreen) {
		$harmoni = Harmoni::instance();
		if ($this->isAuthorizedToExecute()) {
							
			// :: Site ::
			$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
			$this->siteGuiComponent = $rootSiteComponent->acceptVisitor($this->getSiteVisitor());
			$mainScreen->add($this->siteGuiComponent);
		} else {
			// Replace the title
			$outputHandler = $harmoni->getOutputHandler();
			$title = "\n\t\t<title>"._("Unauthorized")."</title>";
			$outputHandler->setHead(
				preg_replace("/<title>[^<]*<\/title>/", $title, $outputHandler->getHead()));			
		
			$mainScreen->add(new Block($this->getUnauthorizedMessage(), ALERT_BLOCK),
				"100%", null, CENTER, TOP);
		}
	}
	
	/**
	 * Add the footer controls to the main screen gui component
	 * 
	 * @param object Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function addFooterControls (Component $mainScreen) {
		// :: Footer ::
		$harmoni = Harmoni::instance();
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		
		$footer = $mainScreen->add(
			new Container (new XLayout, BLANK, 1),
			$rootSiteComponent->getWidth(), null, CENTER, BOTTOM);
		
		
		ob_start();
		print "<div class='seguefooter_left'>";
		// Help LInk
		print Help::link();
		
		// Site Map
		$siteMapUrl = $harmoni->request->quickURL("view", "map", array('node' => SiteDispatcher::getCurrentNodeId()));
		print " | <a target='_blank' href='".$siteMapUrl."'";
		
		print ' onclick="';
		print "var url = '".$siteMapUrl."'; ";
		print "window.open(url, 'site_map', 'width=500,height=600,resizable=yes,scrollbars=yes'); ";
		print "return false;";
		print '"';
		print ">"._("Site Map")."</a>";
		print "</div>";
		
				
		$footer->add(new UnstyledBlock(ob_get_clean()), "50%", null, LEFT, BOTTOM);
		
		$footer->add(new UnstyledBlock(displayAction::getVersionText()), "50%", null, RIGHT, BOTTOM);
	}

	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function getSiteVisitor () {
		if (!isset($this->visitor)) {
			
			$requestedNode = SiteDispatcher::getCurrentNode();
			
			if ($requestedNode->acceptVisitor(new IsBlockVisitor))
				$this->visitor = new DetailViewModeSiteVisitor($requestedNode);
			else
				$this->visitor = new ViewModeSiteVisitor();
		}
		return $this->visitor;
	}
		
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function getSegueLinksComponent () {
		$harmoni = Harmoni::instance();
		ob_start();
		print "<div class='seguelinks'>";
		
		print "<a href='".$harmoni->request->quickURL('portal', 'list')."' title='"._("List of Segue sites")."'>";
		print _("home")."</a> | ";
		
		//print "<a href='".$harmoni->request->quickURL('directory', 'users')."' title='"._("Segue User Directory")."'>";
		print _("directory");
		
// 		print "<a href='".$harmoni->request->quickURL('about', 'welcome')."' title='"._("The Segue homepage")."'>";
// 		print _("about")."</a>";

		print "</div>";
		
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function getCommandsComponent () {
		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$authN = Services::getService("AuthN");
		$userId = $authN->getFirstUserId();
		
		ob_start();
		if (
			// Since users should be logged in to edit, do not bother checking AZs
			// if the user isn't logged in. This cuts the number of authorization 
			// checks to a minimum if the user isn't logged in.
			// While it may be true that the anonymous user has authorization to edit,
			// we don't want to generally support this case to force at least a visitor
			// registration to prevent spamming.
			!$userId->isEqual($idManager->getId('edu.middlebury.agents.anonymous')) 
			
			// While it is more correct to check modify permission permission, doing
			// so forces us to check AZs on the entire site until finding a node with
			// authorization or running out of nodes to check. Since edit-mode actions
			// devolve into view-mode if no authorization is had by the user, just
			// show the links all the time to cut page loads from 4-6 seconds to
			// 1 second.
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				SiteDispatcher::getCurrentRootNode()->getQualifierId())
			)
		{
			print "<div class='commands'>";

			print _("view");
						
			
			if ($this->getUiModule() == "ui2") {
			
				print " | <a href='";
				$url = $harmoni->request->mkURLWithPassthrough('ui2', 'editview');
				print $url->write();
				print "' title='"._("Go to Edit-Mode")."'>";
				print _("edit")."</a>";
				
				
				print " | <a href='";
				$url = $harmoni->request->mkURLWithPassthrough('ui2', 'headerfooter');
				print $url->write();
				print "' title='"._("Go to Header/Footer Edit-Mode")."'>";
				print _("header/footer")."</a>";
		
				print " | <a href='";
				$url = $harmoni->request->mkURLWithPassthrough('ui2', 'arrangeview');
				print $url->write();
				print "' title='"._("Go to Arrange-Mode")."'>";
				print _("arrange")."</a>";
			
			} else {
			
				print " | <a href='";
				$url = $harmoni->request->mkURLWithPassthrough('ui1', 'editview');
				print $url->write();
				print "' title='"._("Go to Edit-Mode")."'>";
				print _("edit")."</a>";			
			}
			
			print " | ".self::getUiSwitchForm();
			print "</div>";
		}
	
		
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>