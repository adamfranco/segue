<?php

/**
 * Debugging and testing options.
 *
 * USAGE: Copy this file to debug.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: debug_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */

/*********************************************************
 * Set to true to enable functionality of resetting the Segue database to
 * a fresh install. Useful for data-corrupting testing/development.
 * Enabling this will allow all of your data to be deleted with one click.
 *********************************************************/
define ("ENABLE_RESET", false);

/*********************************************************
 * Enable the creation of a set of testing users (dwarves)
 * for the purpose of testing user/group functionality.
 *********************************************************/
define ("ENABLE_DWARVES", false);


/*********************************************************
 * Enable the display of timers and query-counters.
 * (Useful for debugging/testing).
 *********************************************************/
define ("ENABLE_TIMERS", false);
 
/*********************************************************
 * PHP error reporting setting. uncomment to enable override
 * of default environment.
 *********************************************************/
error_reporting(E_ALL);