<?

require_once(MYDIR."/main/library/SegueMenuGenerator.class.php");

// Set a default title
$theme =& $harmoni->getTheme();
$theme->setPageTitle("Segue");


$mainScreen =& new RowLayout(TEXT_BLOCK_WIDGET, 1);

// :: Top Row ::
	// The top row for the Segue logo and status bar.
	$headRow =& new ColumnLayout();
	$mainScreen->addComponent($headRow, TOP, CENTER);
	
	// The Segue logo
//	$headRow->addComponent($logo, TOP, LEFT);
	$text = "\n<a href='".MYPATH."/'>";
	$text .= "<img src='".MYPATH."/main/modules/window/logo.gif' border='0' />";
	$text .= "</a>";
	$headRow->addComponent(new Content($text), TOP, LEFT);
	
	// Language Bar
	$languageText = "\n<form action='".MYURL."/language/change/".
	implode("/", $harmoni->pathInfoParts)."' method='post'>";
	$languageText .= "\n\t<select name='language'>";
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
	$languageText .= "\n\t<input type='submit'>";
	$languageText .= "\n</form>";
	$headRow->addComponent(new Content($languageText), TOP, LEFT);
		
	// Header space
	$header =& new Content(" &nbsp; &nbsp; &nbsp; ");
	$headRow->addComponent($header, TOP, CENTER);
	
	// Status Bar
	$statusText = _("Current User: ");
	if ($harmoni->LoginState->isValid()) {
		$statusText .= $harmoni->LoginState->getAgentName();
		$statusText .= " - <a href='".MYURL."/auth/logout/".
		implode("/", $harmoni->pathInfoParts)."'>";
		$statusText .= _("Log Out");
	} else {
		$statusText .= _("anonymous");
		$statusText .= " - <a href='".MYURL."/auth/login/".
		implode("/", $harmoni->pathInfoParts)."'>";
		$statusText .= _("Log In");
	}
	$statusText .= "</a>";
	$statusBar =& new Content($statusText);
	$headRow->addComponent($statusBar, TOP, RIGHT);
	
// :: Center Pane ::
	$centerPane =& new ColumnLayout();
	$mainScreen->addComponent($centerPane, TOP, LEFT);
	
	// Main Menu
	$mainMenu =& SegueMenuGenerator::generateMainMenu($harmoni->getCurrentAction());
	$centerPane->addComponent($mainMenu, TOP, LEFT);

// :: Footer ::
	$footerText = "Segue v2.0 beta &copy;2004 Middlebury College: <a href=''>";
	$footerText .= _("credits");
	$footerText .= "</a>";
	$mainScreen->addComponent(new Content($footerText), BOTTOM, RIGHT);

$harmoni->attachData('mainScreen', $mainScreen);
$harmoni->attachData('statusBar', $statusBar);
$harmoni->attachData('centerPane', $centerPane);

?>