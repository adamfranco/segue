<?php

/**
 * Configuration of the welcome message.
 *
 * USAGE: Copy this file to welcome.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */

ob_start();

print "\n<p>";
print _("<strong>Segue</strong> is a collaborative learning tool developed at Middlebury College.");
print "</p>";

define('SEGUE_WELCOME_MESSAGE', ob_get_clean());