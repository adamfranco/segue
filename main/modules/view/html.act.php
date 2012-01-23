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
		try {
			$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		} catch (UnknownIdException $e) {
			// For non-existant node exceptions, redirect to the site root.
			if ($e->getCode() == 289743 
				&& RequestContext::value('node') && RequestContext::value('site')) 
			{
				$url = SiteDispatcher::quickURL(
							$harmoni->request->getRequestedModule(),
							$harmoni->request->getRequestedAction(),
							array('site' => RequestContext::value('site')));
				$errorPrinter = SegueErrorPrinter::instance();
				$message = "<strong>"._("The node you requested does not exist or has been deleted. Click %1 to go to the %2.")."</strong>";
				$message = str_replace('%1', "<a href='".$url."'>"._("here")."</a>", $message);
				$message = str_replace('%2', "<a href='".$url."'>"._("main page of the site")."</a>", $message);
				$errorPrinter->handExceptionWithRedirect($e, 404, $message);
				exit;
			}
			else if (RequestContext::value('site')) {
				$slotMgr = SlotManager::instance();
				$slot = $slotMgr->getSlotByShortname(RequestContext::value('site'));
				// Redirect to the new URL if this site has been migrated
				if ($redirectUrl = $slot->getMigratedRedirectUrl()) {
					header("HTTP/1.1 301 Moved Permanently");
					header('Location: '.$redirectUrl);
					exit;
				}
				throw $e;
			}
			else {
				throw $e;
			}
		}
		try {
			$slot = $rootSiteComponent->getSlot();
			
			// Redirect to the new URL if this site has been migrated
			if ($redirectUrl = $slot->getMigratedRedirectUrl()) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.$redirectUrl);
				exit;
			}
			
			if (SiteDispatcher::getBaseUrlForSlot($slot) != MYURL) {
				RequestContext::sendTo(SiteDispatcher::quickUrl());
			} else {
				/*********************************************************
				 * Ensure that the requested node is a member of the site
				 * listed in the URL.
				 *********************************************************/
				if (!RequestContext::value('site') 
					|| RequestContext::value('site') != $slot->getShortname())
				{
					/*********************************************************
					 * This is added in Segue 2.1.0 for testing that all 
					 * Segue-generated links are producing the correct URLs.
					 * This should be removed here and in
					 *		segue/config/debug_default.conf.php
					 * after testing is complete.
					 *********************************************************/
					if (defined('DEBUG_LOG_WRONG_SITE') && DEBUG_LOG_WRONG_SITE == true
						&& isset($_SERVER['HTTP_REFERER']) 
						&& preg_match('#^'.str_replace('.', '\.', MYURL).'#', $_SERVER['HTTP_REFERER']))
					{
						HarmoniErrorHandler::logException(
							new WrongSiteException(
								"Expecting site '".$slot->getShortname()."', saw '".RequestContext::value('site')."' in the url. Links to wrong sites should not be generated by Segue. If the link on the referrer page was written by Segue (and not a user), submit a bug report. Sending to ".SiteDispatcher::quickUrl())
							);
					}
					
					RequestContext::sendTo(SiteDispatcher::quickUrl());
				}
			}
			
			// Mark the site as viewed
			Segue_AccessLog::instance()->touch($slot->getShortname());
			
		} catch (UnknownIdException $e) {		// No slot for the site....
		}

        $authZ = Services::getService("AuthZ");
        $recordManager = Services::getService("RecordManager");

        //
        // Begin Optimizations
        //
        // The code below queues up authorizations for all visible nodes, 
        // as well as pre-fetches all of the RecordSets that have data
        // specific to the visible nodes.
        $visibleComponents = SiteDispatcher::getSiteDirector()->getVisibleComponents(SiteDispatcher::getCurrentNodeId());

        $preCacheIds = array();
        foreach ($visibleComponents as $component) {
            $id = $component->getQualifierId();
            $authZ->getIsAuthorizedCache()->queueId($id);
            $preCacheIds[] = $id->getIdString();
        }

        $recordManager->preCacheRecordsFromRecordSetIDs($preCacheIds);
        //
        // End Optimizations
        //

		$mainScreen = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);
		
		$allWrapper = $this->addHeaderControls($mainScreen);
				
		$this->addSiteContent($mainScreen);
		$this->addFooterControls($allWrapper);
		
		if (defined('SEGUE_SITE_FOOTER')) {
			$allWrapper->add(new UnstyledBlock(SEGUE_SITE_FOOTER), "100%", null, CENTER, BOTTOM);
		}
		
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
		
		print Segue_MediaLibrary::getHeadHtml();
		
		$outputHandler->setHead(ob_get_clean());
		
		UserDataHelper::writeHeadJs();

		
		
		// Add the RSS head links
		RssLinkPrinter::addHeadLinks(SiteDispatcher::getCurrentNode());
		
		// Add the selection Panel
		Segue_Selection::instance()->addHeadJavascript();
		
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
		
		// Home
		print "<a href='".$harmoni->request->quickURL('portal', 'list')."' title='"._("List of Segue sites")."'>";
		print _("home")."</a> | ";
	
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
		print ">"._("map")."</a>";

		
		// Tracking
		$trackingUrl = $harmoni->request->quickURL("participation", "actions", array('node' => SiteDispatcher::getCurrentNodeId()));
		print " | <a target='_blank' href='".$trackingUrl."'";
		
		print ' onclick="';
		print "var url = '".$trackingUrl."'; ";
		print "window.open(url, 'site_map', 'width=500,height=600,resizable=yes,scrollbars=yes'); ";
		print "return false;";
		print '"';
		print ">"._("track")."</a>";
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
			
		// Home
		print "<a href='".$harmoni->request->quickURL('portal', 'list')."' title='"._("List of Segue sites")."'>";
		print _("home")."</a> | ";

		// Help
		print Help::link();
		
		// Site Map
		$siteMapUrl = $harmoni->request->quickURL("view", "map", array('node' => SiteDispatcher::getCurrentNodeId()));
		print " | <a target='_blank' href='".$siteMapUrl."'";
		
		print ' onclick="';
		print "var url = '".$siteMapUrl."'; ";
		print "window.open(url, 'site_map', 'width=700,height=600,resizable=yes,scrollbars=yes'); ";
		print "return false;";
		print '"';
		print ">"._("map")."</a>";
				
		// Tracking
		$trackingUrl = $harmoni->request->quickURL("participation", "actions", array('node' => SiteDispatcher::getCurrentNodeId()));
		print " | <a target='_blank' href='".$trackingUrl."'";
		
		print ' onclick="';
		print "var url = '".$trackingUrl."'; ";
		print "window.open(url, 'site_map', 'width=700,height=600,resizable=yes,scrollbars=yes'); ";
		print "return false;";
		print '"';
		print ">"._("track")."</a>";

		print "</div>";
		
		print $this->getExportControls();
		
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
			
			// Add permissions button
			$authZ = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			// Rather than checking the entire site, we will just check the current node.
			// This forces users who are not site-wide admins to browse to the place where
			// they are administrators in order to see the permissions button, but
			// cuts load-times for non-admins on a given large site from 35s to 1.4s.
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				SiteDispatcher::getCurrentNode()->getQualifierId()))
			{
				$url = SiteDispatcher::quickURL("roles", "choose_agent", 
						array("node" => SiteDispatcher::getCurrentNodeId(),
						"returnModule" => $harmoni->request->getRequestedModule(),
						"returnAction" => $harmoni->request->getRequestedAction()));
				print " | \n\t<a href='#' onclick='window.location = \"$url\".urlDecodeAmpersands(); return false;'>";
				print _("roles")."</a>";
			}
			
			print " | ".self::getUiSwitchForm();
			print "</div>";
		}
	
		
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
	
	/**
	 * Answer the export controls for the current site.
	 * 
	 * @param <##>
	 * @return string
	 */
	protected function getExportControls () {
		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$authN = Services::getService("AuthN");
		$userId = $authN->getFirstUserId();
		
		if (
			!$userId->isEqual($idManager->getId('edu.middlebury.agents.anonymous')) 
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				SiteDispatcher::getCurrentRootNode()->getQualifierId())
			)
		{
			// enter links in our head to load needed javascript libraries
			$outputHandler = $harmoni->getOutputHandler();
			$outputHandler->setHead(
				$outputHandler->getHead()
				."
			<script type='text/javascript' src='".MYPATH."/javascript/MigrationPanel.js'></script>
			<script type='text/javascript' src='".MYPATH."/javascript/ArchiveStatus.js'></script>
			");
			require_once(MYDIR."/main/modules/portal/list.act.php");
			$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
			$listAction = new listAction();
			return '<div class="site_export_controls">'.$listAction->getExportControls(SiteDispatcher::getCurrentRootNode()->getQualifierId(), $rootSiteComponent->getSlot())."</div>";
		} else {
			return '';
		}
	}
}

/**
 * This is an Exception to help log where sites are being redirected.
 * 
 * @since 8/22/08
 * @package segue.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WrongSiteException
	extends Exception
{
	
}


