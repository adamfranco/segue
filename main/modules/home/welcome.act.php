<?

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("Welcome to Segue")));
$actionRows->addComponent($introHeader);

$introText =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
$text = "";
$text .= "<p>";
$text .= _("<strong>Segue</strong> is a digital Content Management System developed at Middlebury College.");
$text .= "</p>\n<p>";
$text .= _("The two main parts of <strong>Segue</strong> are the creation and modification of complex websites and the collaborative functionality of a very advanced granular permissions structure, allowing multiple users to work together to create and publish content online.");
$text .= "</p>\n<p>";
$text .= _("Some sites may be restricted to certains users or groups of users. Log in above to ensure your greatest access to all parts of this system.");
$text .= "</p>";
$introText->addComponent(new Content($text));
$actionRows->addComponent($introText);

// return the main layout.
return $mainScreen;