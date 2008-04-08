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
 * @version $Id: database_default.conf.php,v 1.4 2008/04/08 19:43:24 adamfranco Exp $
 */
 
 	$configuration = new ConfigurationProperties;
	Services::startManagerAsService("DatabaseManager", $context, $configuration);
	
/*********************************************************
 * Set up the database connection
 *********************************************************/

	$databaseManager = Services::getService("DatabaseManager");
	$dbName = "my_segue_database";
	$dbID = $databaseManager->addDatabase( new MySQLDatabase("localhost", $dbName,"test","test") );
	$databaseManager->pConnect($dbID);
	
	define("IMPORTER_CONNECTION", $dbID);
	
/*********************************************************
 * Set up a Harmoni_Db connection if desired.
 *		http://harmoni.sourceforge.net/wiki/index.php/Harmoni_Db
 *
 * Using the Harmoni_Db requires installation of the PDO
 * extension
 *		http://www.php.net/pdo
 * as well as a database-specific PDO driver for your database
 *		http://www.php.net/manual/en/ref.pdo.php#pdo.drivers
 * 
 * Using Harmoni_Db allows the use of prepared statements
 * and can improve the performance of some page loads by
 * 30% or better.
 *********************************************************/

// 	$db = Harmoni_Db::factory('Pdo_Mysql', array(
// 		'host'     => 'localhost',
// 		'username' => 'test',
// 		'password' => 'test',
// 		'dbname'   => $dbName,
// 		'adapterNamespace' => 'Harmoni_Db_Adapter'
// 	));
// 	
// 	Harmoni_Db::registerDatabase('segue_db', $db);	
	
	
/*********************************************************
 * Enable tracing of methods that execute database queries
 *********************************************************/
// $databaseManager->recordQueryCallers = true;
// $db->recordQueryCallers = true;