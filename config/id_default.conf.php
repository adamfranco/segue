<?php

/**
 * Set up the IdManager as this is required for the ID service
 *
 * USAGE: Copy this file to id.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: id_default.conf.php,v 1.5 2008/04/21 18:07:35 adamfranco Exp $
 */
 
 	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
// 	$configuration->addProperty('id_prefix', 'dev_id-');
	$configuration->addProperty('harmoni_db_name', 'segue_db');
	Services::startManagerAsService("IdManager", $context, $configuration);