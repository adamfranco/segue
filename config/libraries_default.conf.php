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
 * @version $Id: libraries_default.conf.php,v 1.4 2008/02/29 20:04:06 adamfranco Exp $
 */

/*********************************************************
 * Harmoni Location
 * 		the location on the file system
 *********************************************************/
define("HARMONI_DIR", MYDIR."/main/harmoni/");

/*********************************************************
 * Polyphony location
 *		DIR: the location on the file system
 *		PATH: the location as seen by the browser. For image urls and javascript files.
 *********************************************************/
define("POLYPHONY_DIR", MYDIR."/main/polyphony/");
define("POLYPHONY_PATH", trim(MYPATH, '/')."/main/polyphony/");

/*********************************************************
 * JPGraph location
 * 
 * If you are running in safe mode, you may have safe-mode
 * restrictions preventing PHP from reading files in the 
 * system font directories. Copy the system's truetype font
 * directory and make it owned by your webserver user, then
 * define the TTF_DIR constant below
 *********************************************************/
define("JPGRAPH_DIR", MYDIR."/../jpgraph");
// define("TTF_DIR", MYDIR."/../ttf/");