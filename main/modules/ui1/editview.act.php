<?php
/**
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.13 2007/11/27 22:06:46 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/NoHeaderFooterEditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/DetailEditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/view.act.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.13 2007/11/27 22:06:46 adamfranco Exp $
 */
class editviewAction
	extends viewAction {
	
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
				$this->visitor = new DetailEditModeSiteVisitor($requestedNode);
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
		$mainScreen = parent::execute();
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$siteId = $this->rootSiteComponent->getQualifierId();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteId)
			|| $authZ->isUserAuthorizedBelow(
			$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
			$siteId))
		{
		
			$rootSiteComponent = $this->_director->getRootSiteComponent($siteId);
			$this->rootSiteComponent = $rootSiteComponent;
			
			ob_start();
			$harmoni = Harmoni::instance();
			print "\n<div style='text-align: right;'>";
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"), 
				$siteId))
			{
				print "\n<a href='".$harmoni->request->quickURL("ui1", "editSite", 
					array("node" => $siteId->getIdString(),
					"returnNode" => RequestContext::value("node"),
					"returnAction" => $harmoni->request->getRequestedAction()))."'>";
				print "\n\t<input type='button' value='"._("Edit Site Options")."'/>";
				print "\n</a>";
			}
			if ($authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				$siteId))
			{
				print "\n<a href='".$harmoni->request->quickURL("roles", "choose_agent", 
					array("node" => RequestContext::value("node"),
					"returnModule" => $harmoni->request->getRequestedModule(),
					"returnAction" => $harmoni->request->getRequestedAction()))."'>";
				print "\n\t<input type='button' value='"._("Permissions")."'/>";
				print "\n</a>";
			}
			print "\n</div>";
			
			$mainScreen->add(new UnstyledBlock(ob_get_clean()), $rootSiteComponent->getWidth(), null, CENTER, BOTTOM);
		}
		
		return $mainScreen;
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
		
		ob_start();
		print "<div class='commands'>";
		print "<a href='";
		print $harmoni->request->quickURL('ui1', 'view', array(
				'node' => RequestContext::value("node")));
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | "._("edit");
		
		print " | ".self::getUiSwitchForm('editview');
		print "</div>";		
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>