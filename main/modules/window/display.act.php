<?php
/**
 * @package segue.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: display.act.php,v 1.10 2006/05/02 20:24:17 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

/**
 * build the frame of the window
 * 
 * @package segue.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: display.act.php,v 1.10 2006/05/02 20:24:17 adamfranco Exp $
 */
class displayAction 
	extends Action
{
		
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function &execute ( &$harmoni ) {
		/**
		 * @package segue.display
		 * 
		 * @copyright Copyright &copy; 2005, Middlebury College
		 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
		 *
		 * @version $Id: display.act.php,v 1.10 2006/05/02 20:24:17 adamfranco Exp $
		 */
		 
		require_once(HARMONI."GUIManager/Components/Header.class.php");
		require_once(HARMONI."GUIManager/Components/Menu.class.php");
		require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
		require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
		require_once(HARMONI."GUIManager/Components/Heading.class.php");
		require_once(HARMONI."GUIManager/Components/Footer.class.php");
		require_once(HARMONI."GUIManager/Container.class.php");
		
		require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
		require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
		
		require_once(HARMONI."GUIManager/StyleProperties/FloatSP.class.php");
				
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		
		
		$mainScreen =& new Container($yLayout, BLOCK, 1);
		
	// :: Top Row ::
		// The top row for the logo and status bar.
		$headRow =& new Container($xLayout, HEADER, 1);
		
		// The logo
		$logo =& new Component("\n<a href='".MYPATH."/'> <img src='".LOGO_URL."' 
							style='border: 0px;' alt='"._("Segue Logo'"). "/> </a>", BLANK, 1);
		$headRow->add($logo, null, null, LEFT, TOP);
		
		// Language Bar
		$harmoni->history->markReturnURL("polyphony/language/change");
		$languageText = "\n<form action='".$harmoni->request->quickURL("language", "change")."' method='post'>";
	$harmoni->request->startNamespace("polyphony");
	$languageText .= "\n\t<div style='text-align: center'>\n\t<select name='".$harmoni->request->getName("language")."'>";
	$harmoni->request->endNamespace();
		$langLoc =& Services::getService('Lang');
		$currentCode = $langLoc->getLanguage();
		$languages = $langLoc->getLanguages();
		ksort($languages);
		foreach($languages as $code => $language) {
			$languageText .= "\n\t\t<option value='".$code."'".
							(($code == $currentCode)?" selected='selected'":"").">";
			$languageText .= $language."</option>";
		}
		$languageText .= "\n\t</select>";
		$languageText .= "\n\t<input type='submit' />";
		$languageText .= "\n\t</div>\n</form>";
		
		$languageBar =& new Component($languageText, BLANK, 1);
		$headRow->add($languageBar, null, null, LEFT,TOP);
		
		// Pretty Login Box
		$loginRow =& new Container($yLayout, OTHER, 1);
		$headRow->add($loginRow, null, null, RIGHT, TOP);
		$loginRow->add($this->getLoginComponent(), null, null, RIGHT, TOP);
				
		//Add the headerRow to the mainScreen
		$mainScreen->add($headRow, "100%", null, LEFT, TOP);
		
	// :: Center Pane ::
		$centerPane =& new Container($xLayout, OTHER, 1);
		$mainScreen->add($centerPane,"100%",null, LEFT, TOP);		
		
		// Main menu
		$mainMenu =& SegueMenuGenerator::generateMainMenu($harmoni->getCurrentAction());
		$centerPane->add($mainMenu,"140px",null, LEFT, TOP);
		
		// use the result from previous actions
		if ($harmoni->printedResult) {
			$contentDestination =& new Container($yLayout, OTHER, 1);
			$centerPane->add($contentDestination, null, null, LEFT, TOP);
			$contentDestination->add(new Block($harmoni->printedResult, 1), null, null, TOP, CENTER);
			$harmoni->printedResult = '';
		} else {
			$contentDestination =& $centerPane;
		}
		
		// use the result from previous actions
		$contentDestination->add($harmoni->result, null, null, CENTER, TOP); 
		
		// Right Column
		$rightColumn =& $centerPane->add(new Container($yLayout, OTHER, 1), "140px", null, LEFT, TOP);
		// Basket
		$basket =& Basket::instance();
		$rightColumn->add($basket->getSmallBasketBlock(), "100%", null, LEFT, TOP);
		if (ereg("^(collection|asset)\.browse$", $harmoni->getCurrentAction()))
			$rightColumn->add(AssetPrinter::getMultiEditOptionsBlock(), "100%", null, LEFT, TOP);
		
	// :: Footer ::
		$footer =& new Container (new XLayout, FOOTER, 1);
		
		$helpText = "<a target='_blank' href='";
		$helpText .= $harmoni->request->quickURL("help", "browse_help");
		$helpText .= "'>"._("Help")."</a>";
		$footer->add(new UnstyledBlock($helpText), "50%", null, LEFT, BOTTOM);
		
		$footerText = "Segue v.2.0-Alpha &copy;2006 Middlebury College: <a href=''>";
		$footerText .= _("credits");
		$footerText .= "</a>";
		$footer->add(new UnstyledBlock($footerText), "50%", null, RIGHT, BOTTOM);
		
		$mainScreen->add($footer, "100%", null, RIGHT, BOTTOM);

		return $mainScreen;
	}
	
	/**
	 * Answer the component containing the login/logout form.
	 * 
	 * @return object Component
	 * @access public
	 * @since 3/13/06
	 */
	function &getLoginComponent () {
		ob_start();
		$harmoni =& Harmoni::instance();
		$authN =& Services::getService("AuthN");
		$agentM =& Services::getService("Agent");
		$idM =& Services::getService("Id");
		$authTypes =& $authN->getAuthenticationTypes();
		$users = '';
		while ($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			$id =& $authN->getUserId($authType);
			if (!$id->isEqual($idM->getId('edu.middlebury.agents.anonymous'))) {
				$agent =& $agentM->getAgent($id);
				$exists = false;
				foreach (explode("+", $users) as $user) {
					if ($agent->getDisplayName() == $user)
						$exists = true;
				}
				if (!$exists) {
					if ($users == '')
						$users .= $agent->getDisplayName();
					else
						$users .= " + ".$agent->getDisplayName();
				}
			}
		}
		if ($users != '') {
			print "\n<div style='text-align: right'><small>";
			if (count(explode("+", $users)) == 1)
				print _("User: ").$users."\t";
			else 
				print _("Users: ").$users."\t";
			
			print "<a href='".$harmoni->request->quickURL("auth",
				"logout")."'>"._("Log Out")."</a></small></div>";
		} else {
			// set bookmarks for success and failure
			$harmoni->history->markReturnURL("polyphony/display_login");
			$harmoni->history->markReturnURL("polyphony/login_fail",
				$harmoni->request->quickURL("user", "main"));

			$harmoni->request->startNamespace("harmoni-authentication");
			$usernameField = $harmoni->request->getName("username");
			$passwordField = $harmoni->request->getName("password");
			$harmoni->request->endNamespace();
			$harmoni->request->startNamespace("polyphony");
			print  "\n<div style='text-align: right'>".
				"\n<form action='".
				$harmoni->request->quickURL("auth", "login").
				"' align='right' method='post'><small>".
				"\n\t"._("Username:")." <input type='text' size='8' 
					name='$usernameField'/>".
				"\n\t"._("Password:")." <input type='password' size ='8' 
					name='$passwordField'/>".
				"\n\t <input type='submit' value='Log In' />".
				"\n</small></form></div>\n";
			$harmoni->request->endNamespace();
		}		
		

		$loginForm =& new Component(ob_get_clean(), BLANK, 2);
		
		return $loginForm;
	}
}

?>