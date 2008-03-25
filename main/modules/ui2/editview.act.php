<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.13 2008/03/25 15:29:12 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/EditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/NoHeaderFooterDetailEditModeSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/html.act.php");
require_once(dirname(__FILE__)."/Rendering/UI2.class.php");
require_once(dirname(__FILE__)."/Rendering/NoHeaderFooterEditModeSiteVisitor.class.php");


/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.13 2008/03/25 15:29:12 adamfranco Exp $
 */
class editviewAction
	extends htmlAction 
{
	
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function getSiteVisitor () {
		if (!isset($this->visitor)) {
			
			$requestedNode = $this->_director->getSiteComponentById(
				RequestContext::value("node"));
			
			if ($requestedNode->acceptVisitor(new IsBlockVisitor))
				$this->visitor = new NoHeaderFooterDetailEditModeSiteVisitor($requestedNode);
			else
				$this->visitor = new NoHeaderFooterEditModeSiteVisitor();
		}
		return $this->visitor;
	}
	
	/**
	 * Execute the action
	 * 
	 * @return object
	 * @access public
	 * @since 1/18/07
	 */
	function execute () {
		UI2::addBrowserWarning();
		$allwrapper = parent::execute();
		$mainScreen = $this->mainScreen;
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$this->rootSiteComponent->getQualifierId()))
		{
			$visitor = $this->getSiteVisitor();
			$controlsHTML = $visitor->getBarPreHTML('#090')
				.$visitor->getControlsHTML(
					"<em>"._("Site")."</em>", 
					$this->rootSiteComponent->acceptVisitor($visitor->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, false);
			$mainScreen->setPreHTML($controlsHTML.$mainScreen->getPreHTML($null = null));
			
			$mainScreen->setPostHTML($visitor->getBarPostHTML());
		}
		
		// Add permissions button
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorizedBelow(
			$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
			$this->rootSiteComponent->getQualifierId()))
		{
			ob_start();
			$harmoni = Harmoni::instance();
			print "\n<div style='text-align: right;'>";
			$url = $harmoni->request->quickURL("roles", "choose_agent", 
					array("node" => RequestContext::value("node"),
					"returnModule" => $harmoni->request->getRequestedModule(),
					"returnAction" => $harmoni->request->getRequestedAction()));
			print "\n\t<button onclick='window.location = \"$url\".urlDecodeAmpersands();'>";
			print _("Permissions")."</button>";
			print "\n</div>";
			$allwrapper->add(new UnstyledBlock(ob_get_clean()), $this->rootSiteComponent->getWidth(), null, CENTER, BOTTOM);
		}
		
		return $allwrapper;
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
		//printpre("module: ".$_SESSION['UI_MODULE']);
		ob_start();
		print "<div class='commands'>";
		
		print "<a href='";
		print $harmoni->request->quickURL('view', 'html', array(
				'node' => RequestContext::value("node")));
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | "._("edit");
		
		print " | <a href='";
		print $harmoni->request->quickURL('ui2', 'headerfooter', array(
				'node' => RequestContext::value("node")));
		print "' title='"._("Go to Header/Footer Edit-Mode")."'>";
		print _("header/footer")."</a>";
		
		print " | <a href='";
		print $harmoni->request->quickURL('ui2', 'arrangeview', array(
				'node' => RequestContext::value("node")));
		print "' title='"._("Go to Arrange-Mode")."'>";
		print _("arrange")."</a>";
		print " | ".self::getUiSwitchForm();
		print "</div>";
				
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>