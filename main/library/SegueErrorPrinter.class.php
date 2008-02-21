<?php
/**
 * @since 2/21/08
 * @package segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueErrorPrinter.class.php,v 1.1 2008/02/21 20:29:48 adamfranco Exp $
 */ 

/**
 * An ErrorPrinter for custom error pages
 * 
 * @since 2/21/08
 * @package segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueErrorPrinter.class.php,v 1.1 2008/02/21 20:29:48 adamfranco Exp $
 */
class SegueErrorPrinter {
		
	/**
	 * Print out a custom error page for an exception with the HTTP status code
	 * specified
	 * 
	 * @param object Exception $e
	 * @param int $code
	 * @return void
	 * @access public
	 * @static
	 * @since 2/21/08
	 */
	public static function printException (Exception $e, $code) {
		// Debugging mode for development, rethrow the exception
		if (defined('DISPLAY_ERROR_BACKTRACE') && DISPLAY_ERROR_BACKTRACE) {
			throw $e;
		} 
		
		// Normal production case
		else {
			$message = $e->getMessage();
			$codeString = self::getCodeString($code);
			$errorString = _('Error');
			$logMessage = _('This error has been logged.');
			print <<< END
<html>	
	<head>
		<title>$code $codeString</title>
		<style>
			body {
				background-color: #FFF8C6;
				font-family: Verdana, sans-serif;
			}
			
			.header {
				height: 65px;
				border-bottom: 1px dotted #333;
			}
			.segue_name {
				font-family: Tahoma, sans-serif; 
				font-variant: small-caps; 
				font-weight: bold;
				font-size: 60px;
				color: #333333;
				
				float: left;
			}
			
			.error {
				font-size: 20px;
				font-weight: bold;
				float: left;
				margin-top: 40px;
				margin-left: 20px;
			}
			
			blockquote {
 				margin-bottom: 50px;
				clear: both;
			}
		</style>
	</head>
	<body>
		<div class='header'>
			<div class='segue_name'>Segue</div> 
			<div class='error'>$errorString</div>
		</div>
		<blockquote>
			<h1>$codeString</h1>
			<p>$message</p>
		</blockquote>
		<p>$logMessage</p>
	</body>
</html>

END;
		}
	}
	
	/**
	 * Answer a string that matches the HTTP error code given.
	 * 
	 * @param int $code
	 * @return string
	 * @access public
	 * @since 2/21/08
	 * @static
	 */
	public static function getCodeString ($code) {
		switch ($code) {
			case 400:
				return _('Bad Request');
			case 401:
				return _('Unauthorized');
			case 402:
				return _('Payment Required');
			case 403:
				return _('Forbidden');
			case 404:
				return _('Not Found');
			case 405:
				return _('Method Not Allowed');
			case 406:
				return _('Not Acceptable');
			case 407:
				return _('Proxy Authentication Required');
			case 408:
				return _('Request Timeout');
			case 409:
				return _('Conflict');
			case 410:
				return _('Gone');
			case 411:
				return _('Length Required');
			case 412:
				return _('Precondition Failed');
			case 413:
				return _('Request Entity Too Large');
			case 414:
				return _('Request-URI Too Long');
			case 415:
				return _('Unsupported Media Type');
			case 416:
				return _('Requested Range Not Satisfiable');
			case 417:
				return _('Expectation Failed');
			case ($code > 400 && $code < 500):
				return _('Client Error');
			
			case 500:
				return _('Internal Server Error');
			case 501:
				return _('Not Implemented');
			case 502:
				return _('Bad Gateway');
			case 503:
				return _('Service Unavailable');
			case 505:
				return _('Gateway Timeout');
			case 505:
				return _('HTTP Version Not Supported');		
			
			case ($code > 500 && $code < 600):
				return _('Server Error');
			
			default:
				return _('Error');
		}
	}
	
}

?>