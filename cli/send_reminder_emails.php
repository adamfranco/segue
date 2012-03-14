<?php
/**
 * This is a command line script that will send migration reminder emails.
 * It takes no arguments or parameters.
 *
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

if (!defined('HELP_TEXT')) 
	define("HELP_TEXT", 
"This is a command line script that will clean up old OAI resumption tokens.
It takes no arguments or parameters.
");

if (!defined("OAI_UPDATE_OUTPUT_HTML"))
	define("OAI_UPDATE_OUTPUT_HTML", false);

$_SERVER['argv'][] = '--module=dataport';
$_SERVER['argv'][] = '--action=send_migration_reminders';

require(dirname(__FILE__)."/index_cli.php");
