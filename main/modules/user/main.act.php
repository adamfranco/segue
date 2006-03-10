<?php
/**
 * @package segue.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: main.act.php,v 1.2 2006/03/10 20:53:49 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/Container.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");

/**
 * 
 * 
 * @package segue.modules.user
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: main.act.php,v 1.2 2006/03/10 20:53:49 adamfranco Exp $
 */
class mainAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/24/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 10/24/05
	 */
	function getHeadingText () {
		return _("User Tools");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 10/24/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();

		$actionRows->add(new Heading(_("Authentication"), 2));
		
		// Current AuthN Table
		ob_start();
		$authNManager =& Services::getService("AuthN");
		$agentManager =& Services::getService("Agent");
		$authTypes =& $authNManager->getAuthenticationTypes();
		print "\n<table border='2' align='left'>";
		print "\n\t<tr><th colspan='3'><center>";
		print _("Current Authentications: ");
		print "</center>\n\t</th></tr>";
		
		while($authTypes->hasNextType()) {
			$authType =& $authTypes->nextType();
			$typeString = HarmoniType::typeToString($authType);
			print "\n\t<tr>";
			print "\n\t\t<td><small>";
			print "<a href='#' title='$typeString' onclick='alert(\"$typeString\")'>";
			print $authType->getKeyword();
			print "</a>";
			print "\n\t\t</small></td>";
			print "\n\t\t<td><small>";
			$userId =& $authNManager->getUserId($authType);
			$userAgent =& $agentManager->getAgent($userId);
			print '<a title=\''._("Agent Id").': '.$userId->getIdString().'\' onclick=\'Javascript:alert("'._("Agent Id").':\n\t'.$userId->getIdString().'");\'>';
			print $userAgent->getDisplayName();
			print "</a>";
			print "\n\t\t</small></td>";
			print "\n\t\t<td><small>";
			
			$harmoni->request->startNamespace("polyphony");
			// set where we are before login 
			$harmoni->history->markReturnURL("polyphony/login");
				
			if ($authNManager->isUserAuthenticated($authType)) {
				$url = $harmoni->request->quickURL(
					"auth", "logout_type",
					array("type"=>urlencode($typeString))
				);
				print "<a href='".$url."'>Log Out</a>";
			} else {
				$url = $harmoni->request->quickURL(
					"auth", "login_type",
					array("type"=>urlencode($typeString))
				);
				print "<a href='".$url."'>Log In</a>";
			}
			$harmoni->request->endNamespace();
			
			print "\n\t\t</small></td>";
			print "\n\t</tr>";
		}
		print "\n</table>";

		$statusBar =& new Block(ob_get_contents(),2);
		$actionRows->add($statusBar,null,null,RIGHT,TOP);
		ob_end_clean();


		
		ob_start();
		print "\n<ul>".
			"\n\t<li><a href='".
			$harmoni->request->quickURL("user", "change_password")."'>".
			_("Change 'Harmoni DB' Password").
			"</li>";
			
		$introText =& new Block(ob_get_contents(),2);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
		// end of authN links
		
	}
}
?>
