<?php

/**
 * Set up the basic Visitors DB authentication method.
 *
 * USAGE: Copy this file to authentication_manager.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/VisitorSQLDatabaseAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseMD5UsernamePasswordAuthNTokens.class.php");

/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new VisitorSQLDatabaseAuthNMethod;
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('tokens_class', 'SQLDatabaseMD5UsernamePasswordAuthNTokens');
	$configuration->addProperty('database_id', $dbID);
	$configuration->addProperty('authentication_table', 'auth_visitor');
	$configuration->addProperty('username_field', 'email');
	$configuration->addProperty('password_field', 'password');
	$propertiesFields = array(
			'name' => 'display_name',
			'email' => 'email'
	);
	$configuration->addProperty('properties_fields', $propertiesFields);
	$configuration->addProperty("display_name_property", "name");
	
	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
	
	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new FormActionNamePassTokenCollector(
		$harmoni->request->quickURL("auth","username_password_form"));
	
	
	define('RECAPTCHA_PUBLIC_KEY', '6Le3GAIAAAAAAItWg6d6N4ghVIZY2g3Cf8Y2RIpd');
	define('RECAPTCHA_PRIVATE_KEY', '6Le3GAIAAAAAAJ2ZnFPl-0EAz-3fkpTdLNXdQzBS');
