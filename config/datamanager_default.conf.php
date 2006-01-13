<?php

/**
 * Set up the DataManager
 *
 * USAGE: Copy this file to datamanager.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: datamanager_default.conf.php,v 1.1 2006/01/13 18:30:22 adamfranco Exp $
 */
 
// :: Set up the DataManager ::
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	Services::startManagerAsService("DataManager", $context, $configuration);