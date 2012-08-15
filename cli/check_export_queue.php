<?php
/**
 * This is a command line script that will check the export queue and export the
 * next site if needed.
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
"This is a command line script that will check the export queue and export the 
next site if needed.
");

if (!defined("OAI_UPDATE_OUTPUT_HTML"))
	define("OAI_UPDATE_OUTPUT_HTML", false);

$_SERVER['argv'][] = '--module=dataport';
$_SERVER['argv'][] = '--action=check_export_queue';

require(dirname(__FILE__)."/index_cli.php");
