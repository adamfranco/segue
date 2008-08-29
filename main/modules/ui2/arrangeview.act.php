<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: arrangeview.act.php,v 1.17 2008/04/09 21:12:03 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/modules/view/ViewModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/ArrangeModeSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/html.act.php");
require_once(dirname(__FILE__)."/Rendering/UI2.class.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: arrangeview.act.php,v 1.17 2008/04/09 21:12:03 adamfranco Exp $
 */
class arrangeviewAction
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
		$visitor = new ArrangeModeSiteVisitor();
		return $visitor;
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
			SiteDispatcher::getCurrentRootNode()->getQualifierId()))
		{
			$visitor = $this->getSiteVisitor();
			$controlsHTML = $visitor->getBarPreHTML('#090')
				.$visitor->getControlsHTML(
					"<em>"._("Site")."</em>", 
					SiteDispatcher::getCurrentRootNode()->acceptVisitor($visitor->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, false);
			$mainScreen->setPreHTML($controlsHTML.$mainScreen->getPreHTML($null = null));
			
			$mainScreen->setPostHTML($visitor->getBarPostHTML());
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
		
		ob_start();
		print "<div class='commands'>";
		print "<a href='";
		print SiteDispatcher::quickURL('view', 'html');
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | <a href='";
		print SiteDispatcher::quickURL('ui2', 'editview');
		print "' title='"._("Go to Edit-Mode")."'>";
		print _("edit")."</a>";
		
		
		print " | <a href='";
		print SiteDispatcher::quickURL('ui2', 'headerfooter');
		print "' title='"._("Go to Header/Footer Edit-Mode")."'>";
		print _("header/footer")."</a>";
		
		print " | "._("arrange");
		
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
				
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>