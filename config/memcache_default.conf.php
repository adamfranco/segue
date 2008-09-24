<?php

/**
 * Set up the Memcache connection information.
 *
 * USAGE: Copy this file to memcache.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
 
	Services::startService("Memcache");
    $memcache = Services::getService("Memcache");
	
/*********************************************************
 * Set up the memcache connection
 *********************************************************/

    // Set this to "true" to enable memcache usage.

	define("HAVE_MEMCACHE", false);


    // Uncomment/modify/add lines to add servers.

    // $memcache->addServer("some.ip", 11211)
    // $memcache->addServer("other.ip", 11211)
