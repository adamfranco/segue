<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: newView.act.php,v 1.8 2006/04/12 21:19:56 cws-midd Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
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
 * @version $Id: newView.act.php,v 1.8 2006/04/12 21:19:56 cws-midd Exp $
 */
class newViewAction
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
		$testDocument =& new DOMIT_Document();
		$testDocument->setNamespaceAwareness(true);
		$success = $testDocument->loadXML(MYDIR."/main/library/SiteDisplay/test/testSite.xml");

		if ($success !== true) {
			throwError(new Error("DOMIT error: ".$testDocument->getErrorCode().
				"<br/>\t meaning: ".$testDocument->getErrorString()."<br/>", "SiteDisplay"));
		}

		$xmlDirector =& new XmlSiteDirector($testDocument);
		
//		$flow =& $xmlDirector->getSiteComponentById(4);
//		printpre($flow->_element->toNormalizedString(true));
//		print "<hr/>";

//		$flow->deleteSubcomponentInCell(2);
//		printpre($flow->_element->toNormalizedString(true));
//		print "<hr/>";
// 		$blockA =& $xmlDirector->createSiteComponent('Block');
// 		printpre($blockA->_element->toNormalizedString(true));
//		print "<hr/>";
//		$blockA->updateDisplayName('New Block');
// 		printpre($blockA->_element->toNormalizedString(true));
// 		print "<hr/>";
// 		$blockA->updateDescription("I'm the new block on the kid.");
// 		printpre($blockA->_element->toNormalizedString(true));
// 		print "<hr/>";
//		$blockA->updateTitleMarkup("Where is this printed?");
// 		printpre($blockA->_element->toNormalizedString(true));
// 		print "<hr/>";
// 		$blockA->updateContentMarkup('Hey... Hey... World... Wake up... WAKE UP!\n ps I have id 7');
// 		printpre($blockA->_element->toNormalizedString(true));
// 		print "<hr/>";
//		$flow->addSubcomponent($blockA);
//		printpre($flow->_element->toNormalizedString(true));
//		print "<hr/>";
		
		
//		$blockA =& $xmlDirector->getSiteComponentById(6);
// 		printpre($blockA->_element->toNormalizedString(true));
//		$blockA->updateDisplayName('New TextBlock A displayName');
//		$blockA->updateDescription('My description is the bomb');
//		$blockA->updateContentMarkup('Hello world. I am a banana. Hear me Roar!');
// 		print "<hr/>";
// 		printpre($blockA->_element->toNormalizedString(true));
// 		print "<hr/>";
// 		printpre($testDocument->toNormalizedString(true));
		
//		$flow->moveBefore('0', '3');
//		printpre($flow->_element->toNormalizedString(true));
//		print "<hr/>";
//		$flow->moveToEnd('1');
//		printpre($flow->_element->toNormalizedString(true));
//		print "<hr/>";

// 		$fixed =& $xmlDirector->getSiteComponentById();
// 		$flow->moveBefore('0', '1');
// 		print "<hr/>";
// 		printpre($flow->_element->toNormalizedString(true));
// 		print "<hr/>";
// 		$flow->moveToEnd('0');
// 		printpre($flow->_element->toNormalizedString(true));
// 		print "<hr/>";
		
		if (!$nodeId = RequestContext::value("node"))
			$nodeId = "5";
		
		$rootSiteComponent =& $xmlDirector->getRootSiteComponent($nodeId);
		
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
}

?>