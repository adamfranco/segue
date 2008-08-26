<?php
/**
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.23 2008/04/09 21:12:03 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/modules/view/ViewModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/NoHeaderFooterEditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/DetailEditModeSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/html.act.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.23 2008/04/09 21:12:03 adamfranco Exp $
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
			
			$requestedNode = SiteDispatcher::getCurrentNode();
			
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
		$allwrapper = parent::execute();
		$mainScreen = $this->mainScreen;
		
		// Add permissions button
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$siteId = SiteDispatcher::getCurrentRootNode()->getQualifierId();

		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
			
		ob_start();
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteId))
		{
			$url = SiteDispatcher::quickURL("ui1", "editSite", 
				array("node" => $siteId->getIdString(),
				"returnNode" => SiteDispatcher::getCurrentNodeId(),
				"returnAction" => $harmoni->request->getRequestedAction()));
			print "\n\t<button onclick='window.location = \"$url\".urlDecodeAmpersands();'>";
			print _("Edit Site Options")."</button>";
		}
	
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteId))
		{
			$url = SiteDispatcher::quickURL("ui1", "theme_options", 
				array("node" => $siteId->getIdString(),
				"returnNode" => SiteDispatcher::getCurrentNodeId(),
				"returnAction" => $harmoni->request->getRequestedAction()));
			print "\n\t<button onclick='window.location = \"$url\".urlDecodeAmpersands();'>";
			print _("Theme Options")."</button>";
		}
		
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
			print "\n\t<button onclick='window.location = \"$url\".urlDecodeAmpersands();'>";
			print _("Roles")."</button>";
		}
			
		if (strlen(ob_get_contents())) {
			$mainScreen->add(new UnstyledBlock(
				"\n<div style='text-align: right;'>".ob_get_contents()."\n</div>"),
				$rootSiteComponent->getWidth(), null, CENTER, BOTTOM);
		}
		ob_end_clean();
		
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
		
		ob_start();
		print "<div class='commands'>";
		print "<a href='";
		print SiteDispatcher::quickURL('view', 'html');
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | "._("edit");
		
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
		
		print " | ".self::getUiSwitchForm('ui1');
		print "</div>";		
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>