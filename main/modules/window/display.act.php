<?php
/**
 * @package segue.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: display.act.php,v 1.19 2007/10/12 19:18:38 adamfranco Exp $
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
 * @version $Id: display.act.php,v 1.19 2007/10/12 19:18:38 adamfranco Exp $
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
	function execute () {
		/**
		 * @package segue.display
		 * 
		 * @copyright Copyright &copy; 2005, Middlebury College
		 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
		 *
		 * @version $Id: display.act.php,v 1.19 2007/10/12 19:18:38 adamfranco Exp $
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
				
		$xLayout = new XLayout();
		$yLayout = new YLayout();
		
		
		$mainScreen = new Container($yLayout, BLOCK, 1);

		// :: login, links and commands
		$this->headRow = $mainScreen->add(
			new Container($xLayout, BLOCK, 1), 
			"100%", null, CENTER, TOP);
			
		
		$rightHeadColumn = $this->headRow->add(
			new Container($yLayout, BLANK, 1), 
			null, null, CENTER, TOP);

		$rightHeadColumn->add($this->getLoginComponent(), 
				null, null, RIGHT, TOP);
		

		
	// :: Top Row ::
		// The top row for the logo and status bar.
		$headRow = new Container($xLayout, HEADER, 1);
		
		// The logo
		$logo = new Component("\n<a href='".MYPATH."/'> <img src='".LOGO_URL."' 
							style='border: 0px;' alt='"._("Segue Logo'"). "/> </a>", BLANK, 1);
		$headRow->add($logo, null, null, LEFT, TOP);
		
		// Language Bar
		$harmoni->history->markReturnURL("polyphony/language/change");
		$languageText = "\n<form action='".$harmoni->request->quickURL("language", "change")."' method='post'>";
			
		$harmoni->request->startNamespace("polyphony");
		$languageText .= "\n\t<div style='text-align: right'>\n\t<select style='font-size: 10px' name='".$harmoni->request->getName("language")."'>";
		$harmoni->request->endNamespace();
		
		$langLoc = Services::getService('Lang');
		$currentCode = $langLoc->getLanguage();
		$languages = $langLoc->getLanguages();
		ksort($languages);
		foreach($languages as $code => $language) {
			$languageText .= "\n\t\t<option value='".$code."'".
							(($code == $currentCode)?" selected='selected'":"").">";
			$languageText .= $language."</option>";
		}
		$languageText .= "\n\t</select>";
		
		
		$languageText .= "\n\t<input class='button small' value='Set language'type='submit' />&nbsp;";
		$languageText .= "\n\t</div>\n</form>";
		
		$languageBar = new Component($languageText, BLANK, 1);
		$headRow->add($languageBar, null, null, LEFT,BOTTOM);
		
		// Pretty Login Box
// 		$loginRow = new Container($yLayout, OTHER, 1);
// 		$headRow->add($loginRow, null, null, RIGHT, TOP);
// 		$loginRow->add($this->getLoginComponent(), null, null, RIGHT, TOP);
				
		//Add the headerRow to the mainScreen
		$mainScreen->add($headRow, "100%", null, LEFT, TOP);
		
	// :: Center Pane ::
		$centerPane = new Container($xLayout, OTHER, 1);
		$mainScreen->add($centerPane,"100%",null, LEFT, TOP);		
		
		// Main menu
		$mainMenu = SegueMenuGenerator::generateMainMenu($harmoni->getCurrentAction());
		$centerPane->add($mainMenu,"140px",null, LEFT, TOP);
		
		// use the result from previous actions
		if ($harmoni->printedResult) {
			$contentDestination = new Container($yLayout, OTHER, 1);
			$centerPane->add($contentDestination, null, null, LEFT, TOP);
			$contentDestination->add(new Block($harmoni->printedResult, 1), null, null, TOP, CENTER);
			$harmoni->printedResult = '';
		} else {
			$contentDestination = $centerPane;
		}
		
		// use the result from previous actions
		if (is_object($harmoni->result))
			$contentDestination->add($harmoni->result, null, null, CENTER, TOP);
		else if (is_string($harmoni->result))
			$contentDestination->add(new Block($harmoni->result, STANDARD_BLOCK), null, null, CENTER, TOP);
		
		// Right Column
		$rightColumn = $centerPane->add(new Container($yLayout, OTHER, 1), "140px", null, LEFT, TOP);
		// Basket
		$basket = Basket::instance();
		$rightColumn->add($basket->getSmallBasketBlock(), "100%", null, LEFT, TOP);
		if (ereg("^(collection|asset)\.browse$", $harmoni->getCurrentAction()))
			$rightColumn->add(AssetPrinter::getMultiEditOptionsBlock(), "100%", null, LEFT, TOP);
		
	// :: Footer ::
		$footer = new Container (new XLayout, FOOTER, 1);
		
		$helpText = "<a target='_blank' href='";
		$helpText .= $harmoni->request->quickURL("help", "browse_help");
		$helpText .= "'>"._("Help")."</a>";
		$footer->add(new UnstyledBlock($helpText), "50%", null, LEFT, BOTTOM);
		
		
		$footer->add(new UnstyledBlock(self::getVersionText()), "50%", null, RIGHT, BOTTOM);
		
		$mainScreen->add($footer, "100%", null, RIGHT, BOTTOM);

		return $mainScreen;
	}
	
	/**
	 * Answer the version and copyright text
	 *
	 * @return string
	 * @access public
	 * @since 9/25/07
	 */
	public static function getVersionText () {
		// Version
		if (!isset($_SESSION['SegueVersion'])) {
			$document = new DOMDocument();
			// attempt to load (parse) the xml file
			if ($document->load(MYDIR."/doc/raw/changelog/changelog.xml")) {
				$versionElems = $document->getElementsByTagName("version");
				$latest = $versionElems->item(0);
				$_SESSION['SegueVersion'] = $latest->getAttribute('number');
				if (preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', $latest->getAttribute('date'), $matches))
					$_SESSION['SegueCopyrightYear'] = $matches[1];
				else
					$_SESSION['SegueCopyrightYear'] = $latest->getAttribute('date');
			} else {
				$_SESSION['SegueVersion'] = "2.x.x";
				$_SESSION['SegueCopyrightYear'] = "2007";
			}
		}
		
		$harmoni = Harmoni::instance();
		ob_start();
		print "<a href='".$harmoni->request->quickURL('window', 'changelog')."' target='_blank'>Segue v.".$_SESSION['SegueVersion']."</a> &nbsp; &nbsp; &nbsp; ";
		print "&copy;".$_SESSION['SegueCopyrightYear']." Middlebury College  &nbsp; &nbsp; &nbsp; <a href='http://segue.sourceforge.net'>";
		print _("about");
		print "</a>";
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the component containing the login/logout form.
	 * 
	 * @return object Component
	 * @access public
	 * @since 3/13/06
	 */
	function getLoginComponent () {
		ob_start();
		$harmoni = Harmoni::instance();
		$authN = Services::getService("AuthN");
		$agentM = Services::getService("Agent");
		$idM = Services::getService("Id");
		$authTypes = $authN->getAuthenticationTypes();
		$users = '';
		while ($authTypes->hasNext()) {
			$authType = $authTypes->next();
			$id = $authN->getUserId($authType);
			if (!$id->isEqual($idM->getId('edu.middlebury.agents.anonymous'))) {
				$agent = $agentM->getAgent($id);
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
			print "\n<div style='text-align: right; margin-right: 10px; margin-bottom: 3px;'><small>";
			if (count(explode("+", $users)) == 1)
				print $users."\t";
			else 
				print _("Users: ").$users."\t";
			
			print " | <a href='".$harmoni->request->quickURL("auth",
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
			print  "\n<div style='text-align: right; margin-right: 10px; margin-bottom: 3px;'>".
				"\n<form action='".
				$harmoni->request->quickURL("auth", "login").
				"' align='right' method='post'><small>".
				"\n\t"._("Username:")." <input class='small' type='text' size='8' 
					name='$usernameField'/>".
				"\n\t"._("Password:")." <input class='small' type='password' size ='8' 
					name='$passwordField'/>".
				"\n\t <input class='button small' type='submit' value='Log in' />".
				"\n</small></form></div>\n";
			$harmoni->request->endNamespace();
		}		
		

		$loginForm = new Component(ob_get_clean(), BLANK, 2);
		
		return $loginForm;
	}
	
	/**
	 * Get the form for switching to a different UI mode.
	 * 
	 * @return string
	 * @access public
	 * @since 9/6/07
	 */
	public function getUiSwitchForm ($targetAction = 'view') {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough('node');
		$harmoni->request->passthrough('site');
		ob_start();
		print "\n\t<form action='".$harmoni->request->quickURL('XXXMODULEXXX','XXXACTIONXXX')."' method='post' ";
		print "style='display: inline;'>";
		$harmoni->request->forget('node');
		$harmoni->request->forget('site');
		
		$options = array ('ui1' => _("Classic Mode"), 'ui2' => _("New Mode"));
		
		print "\n\t\t<select style='font-size: 10px' name='".RequestContext::name('user_interface')."' ";
		
		print "onchange=\"";
		print "var module = this.value; ";
		print "var action = '".$targetAction."'; ";
		print "var url = this.form.action; ";
// 		print "alert(url); ";
		print "url = url.replace(/XXXMODULEXXX/, module); ";
		print "url = url.replace(/XXXACTIONXXX/, action); ";
		print "url = url.urlDecodeAmpersands(); ";
// 		print "alert(url); ";
		print "window.location = url; ";
		print "return false;";
		
		print "\">";
		foreach ($options as $key => $val) {
			print "\n\t\t\t<option value='$key'";
			print (($harmoni->request->getRequestedModule() == $key)?" selected='selected'":"");
			print ">$val</option>";
		}
		print "\n\t\t</select>";
		print "\n\t</form>";
		return ob_get_clean();
	}
}

?>