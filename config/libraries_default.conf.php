<?php

/**
 * Library locations configuration file.
 *
 * USAGE: Copy this file to libraries.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: libraries_default.conf.php,v 1.2 2007/09/20 17:14:14 adamfranco Exp $
 */

/*********************************************************
 * Harmoni Location
 * 		the location on the file system
 *********************************************************/
define("HARMONI_DIR", MYDIR."/main/harmoni/");

/*********************************************************
 * Polyphony location
 *		DIR: the location on the file system
 *		PATH: the location as seen by the browser. For image urls.
 *********************************************************/
define("POLYPHONY_DIR", MYDIR."/main/polyphony/");
define("POLYPHONY_PATH", dirname(MYURL)."/main/polyphony/");