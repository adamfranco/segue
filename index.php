<?php
/**
 * This is the main control script for the application.
 *
 * @package segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: index.php,v 1.12 2008/02/21 20:29:13 adamfranco Exp $
 */

/*********************************************************
 * Define a Constant reference to this application directory.
 *********************************************************/
error_reporting(E_ALL);
ini_set('display_errors', true);

define("MYDIR",dirname(__FILE__));

if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
	$protocol = 'https';
else
	$protocol = 'http';

if ($_SERVER['SCRIPT_NAME'])
	$scriptPath = $_SERVER['SCRIPT_NAME'];
else
	$scriptPath = $_SERVER['PHP_SELF'];
	
define("MYPATH", $protocol."://".$_SERVER['HTTP_HOST'].str_replace(
												"\\", "/", 
												dirname($scriptPath)));

// The following lines set the MYURL constant.
if (file_exists(MYDIR.'/config/url.conf.php'))
	include_once (MYDIR.'/config/url.conf.php');
else
	include_once (MYDIR.'/config/url_default.conf.php');

if (!defined("MYURL"))
	define("MYURL", trim(MYPATH, '/')."/index.php");


define("LOAD_GUI", true);

/*********************************************************
 * Include our libraries
 *********************************************************/
require_once(dirname(__FILE__)."/main/include/libraries.inc.php");

/*********************************************************
 * Include our configuration and setup scripts
 *********************************************************/
require_once(dirname(__FILE__)."/main/include/setup.inc.php");

/*********************************************************
 * Execute our actions
 *********************************************************/
if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
	require_once(HARMONI."/utilities/Timer.class.php");
	$execTimer = new Timer;
	$execTimer->start();
	ob_start();
}

try {
	$harmoni->execute();

// Handle certain types of uncaught exceptions specially. In particular,
// Send back HTTP Headers indicating that an error has ocurred to help prevent
// crawlers from continuing to pound invalid urls.
} catch (UnknownActionException $e) {
	header('HTTP/1.1 400 Bad Request');
	SegueErrorPrinter::printException($e, 400);
	HarmoniErrorHandler::logException($e);
} catch (NullArgumentException $e) {
	header('HTTP/1.1 400 Bad Request');
	SegueErrorPrinter::printException($e, 400);
	HarmoniErrorHandler::logException($e);
} catch (PermissionDeniedException $e) {
	header('HTTP/1.1 403 Forbidden');
	SegueErrorPrinter::printException($e, 403);
	HarmoniErrorHandler::logException($e);
} catch (UnknownIdException $e) {
	header('HTTP/1.1 404 Not Found');
	SegueErrorPrinter::printException($e, 404);
	HarmoniErrorHandler::logException($e);
}
// Default 
catch (Exception $e) {
	header('HTTP/1.1 500 Internal Server Error');
	SegueErrorPrinter::printException($e, 500);
	HarmoniErrorHandler::logException($e);
}

if (defined('ENABLE_TIMERS') && ENABLE_TIMERS) {
	$execTimer->end();
	$output = ob_get_clean();
	
	ob_start();
	print "\n<table>\n<tr><th align='right'>Execution Time:</th>\n<td align='right'><pre>";
	printf("%1.6f", $execTimer->printTime());
	print "</pre></td></tr>\n</table>";
	
	
	$dbhandler = Services::getService("DBHandler");
	printpre("NumQueries: ".$dbhandler->getTotalNumberOfQueries());
	
// 	printpreArrayExcept($_SESSION, array('__temporarySets'));
	// debug::output(session_id());
	// Debug::printAll();
	
	print "\n\t</body>\n</html>";
	print preg_replace('/<\/body>\s*<\/html>/i', ob_get_clean(), $output);
}

?>
