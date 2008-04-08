<?php
/**
 * This is the main control script for the application.
 *
 * @package segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: index.php,v 1.15.2.2 2008/04/08 14:47:28 adamfranco Exp $
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
	try {
		/*********************************************************
		 * Redirect for short form /sites/mysitename urls
		 *********************************************************/
		if (isset($_SERVER['PATH_INFO']) 
                        && preg_match('/^\/sites\/([\w_-]+)\/?/', $_SERVER['PATH_INFO'], $matches))
		{
			$harmoni->request->set('site', $matches[1]);
			$harmoni->request->setModuleAction('view', 'html');
		}

		$harmoni->execute();
	} catch (UnknownActionException $e) {
		// If we are passed a Segue1-style URL, forward to an appropriate place.
		Segue1UrlResolver::forwardCurrentIfNeeded();
		
		// If we were not forwarded, re-throw
		throw $e;
	} catch (UnknownIdException $e) {
		// If we are passed a Segue1-style URL, forward to an appropriate place.
		Segue1UrlResolver::forwardCurrentIfNeeded();
		
		// If we were not forwarded, re-throw
		throw $e;
	}

// Handle certain types of uncaught exceptions specially. In particular,
// Send back HTTP Headers indicating that an error has ocurred to help prevent
// crawlers from continuing to pound invalid urls.
} catch (UnknownActionException $e) {
	SegueErrorPrinter::handleException($e, 400);
} catch (NullArgumentException $e) {
	SegueErrorPrinter::handleException($e, 400);
} catch (PermissionDeniedException $e) {
	SegueErrorPrinter::handleException($e, 403);
} catch (UnknownIdException $e) {
	SegueErrorPrinter::handleException($e, 404);
}
// Default 
catch (Exception $e) {
	SegueErrorPrinter::handleException($e, 500);
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
	if (isset($dbhandler->recordQueryCallers) && $dbhandler->recordQueryCallers)
		print $dbhandler->getQueryCallerStats();
	
	try {
		$db = Harmoni_Db::getDatabase('segue_db');
		print "<p>".$db->getStats()."</p>";
	} catch (UnknownIdException $e) {
	}
	
// 	printpreArrayExcept($_SESSION, array('__temporarySets'));
	// debug::output(session_id());
	// Debug::printAll();
	
	print "\n\t</body>\n</html>";
	print preg_replace('/<\/body>\s*<\/html>/i', ob_get_clean(), $output);
}

?>
