<?php
/**
 * Include the libraries and define constants for our application
 *
 * @package segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: libraries.inc.php,v 1.3 2006/01/13 20:59:42 adamfranco Exp $
 */

/******************************************************************************
 * Include Harmoni - required
 ******************************************************************************/
$harmoniPath = MYDIR."/../harmoni/harmoni.inc.php";
if (!file_exists($harmoniPath)) {
	print "<h2>Harmoni was not found in the specified location, '";
	print $harmoniPath;
	print "'. Please install Harmoni there or change the location specifed.</h2>";
	print "<h3>Harmoni is part of the Harmoni project and can be downloaded from <a href='http://sf.net/projects/harmoni/'>http://sf.net/projects/harmoni/</a></h3>";
}
require_once ($harmoniPath);

/******************************************************************************
 * Include Polyphony
 ******************************************************************************/
define("POLYPHONY_DIR", MYDIR."/../polyphony/");
define("POLYPHONY_PATH", MYPATH."/../polyphony/");

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
require_once(MYDIR."/main/library/SegueMenuGenerator.class.php");
require_once(MYDIR."/main/library/PluginManager/Plugin.abstract.php");


/******************************************************************************
 * Include any theme classes we want to use. They need to be included prior
 * to starting the session so that they can be restored properly.
 ******************************************************************************/
require_once(HARMONI."GUIManager/Themes/SimpleLinesTheme.class.php");


