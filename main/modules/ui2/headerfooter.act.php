<?php
/**
 * @since 3/25/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: headerfooter.act.php,v 1.5 2008/04/02 21:15:22 achapin Exp $
 */ 
require_once(dirname(__FILE__)."/../view/html.act.php");
require_once(dirname(__FILE__)."/Rendering/DetailEditHeaderFooterSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/EditHeaderFooterSiteVisitor.class.php");

/**
 * An action for editing the header and footer in UI2
 * 
 * @since 3/25/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: headerfooter.act.php,v 1.5 2008/04/02 21:15:22 achapin Exp $
 */
class headerFooterAction 
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
				$this->visitor = new DetailEditHeaderFooterSiteVisitor($requestedNode);
			else
				$this->visitor = new EditHeaderFooterSiteVisitor();
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
	function getCommandsComponent () {
		$harmoni = Harmoni::instance();
		//printpre("module: ".$_SESSION['UI_MODULE']);
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
		
		print " | "._("header/footer");
		
		print " | <a href='";
		print SiteDispatcher::quickURL('ui2', 'arrangeview');
		print "' title='"._("Go to Arrange-Mode")."'>";
		print _("arrange")."</a>";
		
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