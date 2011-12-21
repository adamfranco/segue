<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
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
 * @package segue.modules.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */

/**
 * Test view using new components
 * 
 * @package segue.modules.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
class htmlAction
	extends displayAction 
{
	
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
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
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		
		/*********************************************************
		 * Split sites based on their location-category
		 *********************************************************/
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		
		try {
			$slot = $rootSiteComponent->getSlot();
			
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

		$this->mainScreen = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);
		$this->addHead($this->mainScreen);
		
		$this->addSiteContent($this->mainScreen);
		
		return $this->mainScreen;
	}
	
	/**
	 * Add the header controls to the main screen gui component
	 * 
	 * @param object Component $mainScreen
	 * @return object Component The allWrapper
	 * @access public
	 */
	public function addHead (Component $mainScreen) {
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
				
		$outputHandler->setHead(ob_get_clean());
		
		
		// Add the RSS head links
		RssLinkPrinter::addHeadLinks(SiteDispatcher::getCurrentNode());
	}
	
	/**
	 * Add the site content gui components
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
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
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
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
}

/**
 * This is an Exception to help log where sites are being redirected.
 * 
 * @package segue.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
class WrongSiteException
	extends Exception
{
	
}


