<?php
/**
 * Include the libraries and define constants for our application
 *
 * @package segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: libraries.inc.php,v 1.15 2008/03/20 19:07:04 adamfranco Exp $
 */

if (file_exists(MYDIR.'/config/libraries.conf.php'))
	require_once (MYDIR.'/config/libraries.conf.php');
else
	require_once (MYDIR.'/config/libraries_default.conf.php');

/******************************************************************************
 * Include Harmoni - required
 ******************************************************************************/
if (!file_exists(HARMONI_DIR."/harmoni.inc.php")) {
	print "<h2>Harmoni was not found in the specified location, '";
	print HARMONI_DIR."/harmoni.inc.php";
	print "'. Please install Harmoni there or change the location specifed.</h2>";
	print "<h3>Harmoni is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once (HARMONI_DIR."/harmoni.inc.php");

/******************************************************************************
 * Include Polyphony
 ******************************************************************************/
if (!file_exists(POLYPHONY_DIR."/polyphony.inc.php")) {
	print "<h2>Polyphony was not found in the specified location, '";
	print POLYPHONY_DIR;
	print "'. Please install Polyphony there or change the location specifed.</h2>";
	print "<h3>Polyphony is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once (POLYPHONY_DIR."/polyphony.inc.php");

/******************************************************************************
 * Include our libraries
 ******************************************************************************/
require_once(MYDIR."/main/library/SegueErrorPrinter.class.php");
require_once(MYDIR."/main/library/SegueMenuGenerator.class.php");
require_once(MYDIR."/main/library/Slots/SlotManager.class.php");
require_once(MYDIR."/main/library/Roles/SegueRoleManager.class.php");
require_once(MYDIR."/main/library/CourseManagement/SegueCourseManager.class.php");
require_once(HARMONI."GUIManager/Components/MenuItem.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLinkWithAdditionalHtml.class.php");
require_once(POLYPHONY."/main/library/Importer/XMLImporters/XMLImporter.class.php");
require_once(MYDIR."/main/modules/roles/AgentSearchSource.class.php");
require_once(MYDIR."/main/modules/ui2/AddSiteAgentSearchSource.class.php");
require_once(MYDIR."/main/modules/ui2/AddSiteAgentSearchField.class.php");
require_once(MYDIR."/main/library/Segue1UrlResolver.class.php");
require_once(MYDIR."/main/library/Templates/TemplateManager.class.php");
require_once(MYDIR."/main/modules/selection/Selection.class.php");
require_once(MYDIR."/main/modules/portal/SearchPortalFolder.class.php");
require_once(HARMONI."UserData/UserData.class.php");

/******************************************************************************
 * Include any theme classes we want to use. They need to be included prior
 * to starting the session so that they can be restored properly.
 ******************************************************************************/
require_once(HARMONI."GUIManager/Themes/SimpleLinesTheme.class.php");


