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
 * @version $Id: authorization_default.conf.php,v 1.3 2007/09/04 18:00:42 adamfranco Exp $
 */
 
// :: Set up the Authorization System ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('harmoni_db_name', 'segue_db');
	Services::startManagerAsService("AuthorizationManager", $context, $configuration);

// 	require_once(HARMONI."/oki2/AuthZ2/authz/AuthorizationManager.class.php");
// 	$azMgr = new AuthZ2_AuthorizationManager;
// 	$azMgr->assignConfiguration($configuration);
// 	Services::registerObjectAsService("AuthorizationManager", $azMgr);