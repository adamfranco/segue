<?php

/**
 * Set up the AuthenticationManager and associated Authentication modules
 *
 * USAGE: Copy this file to authentication.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: authentication_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */
 
// :: Start the AuthenticationManager OSID Impl.
	$configuration =& new ConfigurationProperties;
	$tokenCollectors = array(
		serialize(new Type ("Authentication", "edu.middlebury.harmoni", "Segue DB")) 
			=> new FormActionNamePassTokenCollector($harmoni->request->quickURL("auth","username_password_form")),
// 		serialize(new Type ("Authentication", "edu.middlebury.harmoni", "Middlebury LDAP")) 
// 			=> new FormActionNamePassTokenCollector($harmoni->request->quickURL("auth","username_password_form")),
	);
	$configuration->addProperty('token_collectors', $tokenCollectors);
	Services::startManagerAsService("AuthenticationManager", $context, $configuration);


// :: Start and configure the AuthenticationMethodManager
	$configuration =& new ConfigurationProperties;
	
		// set up a Database Authentication Method
		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseAuthNMethod.class.php");
		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/SQLDatabaseMD5UsernamePasswordAuthNTokens.class.php");
		$dbAuthType =& new Type ("Authentication", "edu.middlebury.harmoni", "Segue DB");
		$dbMethodConfiguration =& new ConfigurationProperties;
		$dbMethodConfiguration->addProperty('tokens_class', $arg0 = 'SQLDatabaseMD5UsernamePasswordAuthNTokens');
		$dbMethodConfiguration->addProperty('database_id', $dbID);
		$dbMethodConfiguration->addProperty('authentication_table', $arg2 = 'auth_db_user');
		$dbMethodConfiguration->addProperty('username_field', $arg3 = 'username');
		$dbMethodConfiguration->addProperty('password_field', $arg4 = 'password');
		$propertiesFields = array(
			'username' => 'username',
//			'name'=> 'display_name',
		);
		$dbMethodConfiguration->addProperty('properties_fields', $propertiesFields);
		
		$dbAuthNMethod =& new SQLDatabaseAuthNMethod;
		$dbAuthNMethod->assignConfiguration($dbMethodConfiguration);
		unset($arg0, $arg1, $arg2, $arg3, $arg4, $propertiesFields, $dbMethodConfiguration);
		
	$configuration->addProperty($dbAuthType, $dbAuthNMethod);
	
	$GLOBALS["NewUserAuthNType"] =& $dbAuthType;
		
		// set up LDAPAuthentication Method
// 		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNMethod.class.php");
// 		require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/LDAPAuthNTokens.class.php");	
// 		$ldapAuthType =& new Type ("Authentication", "edu.middlebury.harmoni", "Middlebury LDAP");
// 		$ldapConfiguration =& new ConfigurationProperties;
// 		$ldapConfiguration->addProperty('tokens_class', $arg0 = 'LDAPAuthNTokens');
// 		$ldapConfiguration->addProperty("LDAPHost", $arg1 = "ad.middlebury.edu");
// 		$ldapConfiguration->addProperty("baseDN", $arg2 = "cn=users,dc=middlebury,dc=edu");
// 		$ldapConfiguration->addProperty("bindDN", $arg3 = "juser");
// 		$ldapConfiguration->addProperty("bindDNPassword", $arg4 = "");
// 		$propertiesFields = array (
// 			'username' => 'samaccountname',
// 			'name' =>  'displayname',
// 			'first name' =>  'givenname',
// 			'last name' =>  'sn',
// 			'department' =>  'department',
// 			'email' =>  'mail',
// 		);
// 		$ldapConfiguration->addProperty('properties_fields', $propertiesFields);
// 		$loginFields = array (
// 			'samaccountname', 
// 			'mail',
// 			'cn',
// 		);
// 		$ldapConfiguration->addProperty('login_fields', $loginFields);
// 		$ldapConfiguration->addProperty("display_name_property", $arg6 = "name");
// 
// 		$ldapAuthNMethod =& new LDAPAuthNMethod;
// 		$ldapAuthNMethod->assignConfiguration($ldapConfiguration);
// 		unset($arg0, $arg1, $arg2, $arg3, $arg4, $propertiesFields, $loginFields, $ldapConfiguration);
// 		
// 	$configuration->addProperty($ldapAuthType, $ldapAuthNMethod);
	
	Services::startManagerAsService("AuthNMethodManager", $context, $configuration);
	
	
// :: Agent-Token Mapping Manager ::	
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('database_id', $dbID);
	Services::startManagerAsService("AgentTokenMappingManager", $context, $configuration);