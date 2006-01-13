<?php

/**
 * Set up the DatabaseHandler
 *
 * USAGE: Copy this file to database.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: database_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */
 
 	$configuration =& new ConfigurationProperties;
	Services::startManagerAsService("DatabaseManager", $context, $configuration);
	
	//Set up the database connection
	$databaseManager =& Services::getService("DatabaseManager");
	$dbName = "my_segue_database";
	$dbID = $databaseManager->addDatabase( new MySQLDatabase("localhost", $dbName,"test","test") );
	$databaseManager->pConnect($dbID);
	
	define("IMPORTER_CONNECTION", $dbID);
	unset($databaseManager); // done with that for now