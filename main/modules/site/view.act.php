<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.13 2007/01/12 16:54:26 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/EditModeSiteVisitor.class.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.13 2007/01/12 16:54:26 adamfranco Exp $
 */
class viewAction
	extends displayAction {
		
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/3/06
	 */
	function &execute ( &$harmoni ) {
		/*********************************************************
		 * XML Version
		 *********************************************************/
// 		$testDocument =& new DOMIT_Document();
// 		$testDocument->setNamespaceAwareness(true);
// 		$success = $testDocument->loadXML(MYDIR."/main/library/SiteDisplay/test/testSite.xml");
// 
// 		if ($success !== true) {
// 			throwError(new Error("DOMIT error: ".$testDocument->getErrorCode().
// 				"<br/>\t meaning: ".$testDocument->getErrorString()."<br/>", "SiteDisplay"));
// 		}
// 
// 		$director =& new XmlSiteDirector($testDocument);
// 		
// 		if (!$nodeId = RequestContext::value("node"))
// 			$nodeId = "1";

		/*********************************************************
		 * Asset version
		 *********************************************************/
		$repositoryManager =& Services::getService('Repository');
		$idManager =& Services::getService('Id');
		
		$director =& new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));			
		
		if (!$nodeId = RequestContext::value("node"))
			throwError(new Error('No site node specified.', 'SiteDisplay'));
		
		/*********************************************************
		 * Aditional setup
		 *********************************************************/
		$rootSiteComponent =& $director->getRootSiteComponent($nodeId);
		$this->rootSiteComponent =& $rootSiteComponent;
		
		$visitor =& $this->getSiteVisitor();
		
		$siteGuiComponent =& $rootSiteComponent->acceptVisitor($visitor);
		
		
		/*********************************************************
		 * Other headers and footers
		 *********************************************************/
		$outputHandler =& $harmoni->getOutputHandler();
		$head = $outputHandler->getHead();
		if (preg_match('/<title>.*<\/title>/', $head))
			$head = preg_replace('/<title>.*<\/title>/', 
				'<title>'.$rootSiteComponent->getDisplayName().'</title>', $head);
		else
			$head .= "<title>".$rootSiteComponent->getDisplayName()."</title>";
		$outputHandler->setHead($head);
		
				
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		
		
		$mainScreen =& new Container($yLayout, BLOCK, BACKGROUND_BLOCK);
		
		// :: Top Row ::
		$headRow =& $mainScreen->add(
			new Container($xLayout, HEADER, 1), 
			"100%", null, CENTER, TOP);
		
		$headRow->add(new UnstyledBlock("<h1>".$rootSiteComponent->getTitleMarkup()."</h1>"), 
			null, null, LEFT, TOP);
		
		$rightHeadColumn =& $headRow->add(
			new Container($yLayout, BLANK, 1), 
			null, null, CENTER, TOP);
		
		$rightHeadColumn->add($this->getLoginComponent(), 
				null, null, RIGHT, TOP);
		
		$rightHeadColumn->add($this->getSegueLinksComponent(), 
				null, null, RIGHT, TOP);
				
		$rightHeadColumn->add($this->getCommandsComponent(), 
				null, null, RIGHT, TOP);
		
		
		// :: Site ::
		$mainScreen->add($siteGuiComponent);
		
		
		// :: Footer ::
		$footer =& $mainScreen->add(
			new Container (new XLayout, FOOTER, 1),
			"100%", null, RIGHT, BOTTOM);
		
		$helpText = "<a target='_blank' href='";
		$helpText .= $harmoni->request->quickURL("help", "browse_help");
		$helpText .= "'>"._("Help")."</a>";
		$footer->add(new UnstyledBlock($helpText), "50%", null, LEFT, BOTTOM);
		
		$footerText = "Segue v.2.0-Alpha &copy;2006 Middlebury College: <a href=''>";
		$footerText .= _("credits");
		$footerText .= "</a>";
		$footer->add(new UnstyledBlock($footerText), "50%", null, RIGHT, BOTTOM);
		
		
		return $mainScreen;
	}

	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function &getSiteVisitor () {
		$visitor =& new ViewModeSiteVisitor();
		return $visitor;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function &getSegueLinksComponent () {
		$harmoni =& Harmoni::instance();
		ob_start();
		
		print "<a href='".$harmoni->request->quickURL('home', 'welcome')."' alt='"._("The Segue homepage")."'>";
		print _("home")."</a> | ";
		
		print "<a href='".$harmoni->request->quickURL('site', 'list')."' alt='"._("List of Segue sites")."'>";
		print _("site list")."</a>";
		
		$ret =& new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function &getCommandsComponent () {
		$harmoni =& Harmoni::instance();
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		ob_start();
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$idManager->getId($this->rootSiteComponent->getId())))
		{
			print "<a href='";
			print $harmoni->request->quickURL('site', 'editview', array(
					'node' => RequestContext::value("node")));
			print "' alt='"._("Go to Edit-Mode")."'>";
			print _("edit")."</a>";
		}
	
		
		$ret =& new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>