<?php

/**
 * Set up the AuthorizationManager
 *
 * USAGE: Copy this file to authorization.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: authorization_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */
 
// :: Set up the Authorization System ::
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	Services::startManagerAsService("AuthorizationManager", $context, $configuration);