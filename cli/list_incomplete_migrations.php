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

$_SERVER['argv'][] = '--module=dataport';
$_SERVER['argv'][] = '--action=list_incomplete_migrations';

require(dirname(__FILE__)."/index_cli.php");
