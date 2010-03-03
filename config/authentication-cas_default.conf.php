<?php

/**
 * Set up the the LDAP authentication method.
 *
 * To add a second LDAP Authentication Method:
 * 		1. copy this file to a new name such as 'authentication-ldap2.conf.php' 
 *		2. Use a unique type for the new authentication method such as:
 *			$type = new Type ("Authentication", "edu.example", "Secondary LDAP");
 *		3. Update the authentications_sources.conf.php to add this new configuration:
 *			$authenticationSources = array(
 *				"db",
 *			 	"ldap",
 *				"ldap2",
 *				"visitors"
 *			);
 *
 * USAGE: Copy this file to authentication_manager.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */

require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNMethod.class.php");
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASAuthNTokens.class.php");	
require_once(HARMONI."/oki2/agentmanagement/AuthNMethods/CASGroup.class.php");	
require_once(HARMONI."/oki2/authentication/CasTokenCollector.class.php");	
 		
/*********************************************************
 * Create and configure the authentication method
 *********************************************************/
	$authNMethod = new CASAuthNMethod;
	
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('CAS_DEBUG_PATH', '/tmp/harmoni_cas.out');
	$configuration->addProperty("CAS_HOST", "login.middlebury.edu");
	$configuration->addProperty("CAS_PORT", "443");
	$configuration->addProperty("CAS_PATH", "/cas/");
	$configuration->addProperty("CAS_CERT", "/etc/pki/tls/certs/ca-bundle.crt");
	$configuration->addProperty("CALLBACK_URL", "https://chisel.middlebury.edu/~afranco/directory_client_test/storePGT.php");
	$configuration->addProperty("CASDIRECTORY_BASE_URL", "http://login.middlebury.edu/directory/");
	$configuration->addProperty("CASDIRECTORY_ADMIN_ACCESS", "qwertyuiop");
	$configuration->addProperty("DISPLAY_NAME_FORMAT", "[[FirstName]] [[LastName]]");
	
	$rootGroups = array(
// 		'OU=Groups,DC=middlebury,DC=edu',
		'OU=web data,DC=middlebury,DC=edu',
	);
	$configuration->addProperty("ROOT_GROUPS", $rootGroups);
	
	$configuration->addProperty("CASDIRECTORY_CLASS_ROOT", "OU=Classes,OU=Groups,DC=middlebury,DC=edu");

	$authNMethod->assignConfiguration($configuration);



/*********************************************************
 * Enable the authentication method
 *********************************************************/
	// Define a unique Type for this method
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");
	
	// Add the method to our AuthenticationMethodManagerConfiguration
	$authenticationMethodManagerConfiguration->addProperty($type, $authNMethod);
	// Assign a token-collector for this method
	$tokenCollectors[serialize($type)] = new CasTokenCollector();
	
/*********************************************************
 * Replace the username/password form in the head with a CAS link
 *********************************************************/
define('LOGIN_FORM_CALLBACK', 'getCasLoginLink');
function getCasLoginLink () {
	$type = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");
	$harmoni = Harmoni::instance();
	$harmoni->request->startNamespace("polyphony");
	$html = "<a href='".$harmoni->request->quickURL('auth', 'login_type', array('type' => $type->asString()))."'>Log In</a>";
	$harmoni->request->endNamespace();
	
    $harmoni->history->markReturnURL("polyphony/login", $harmoni->request->mkURL());
	
	if ($visitorLogin = getVisitorLoginLink()) {
		$html .= " &nbsp; | &nbsp; ".$visitorLogin;
	}
	
	return $html;
}
function getVisitorLoginLink() {
	$harmoni = Harmoni::instance();
	$authN = Services::getService("AuthN");
	
	// Visitor Registration Link
	$authTypes = $authN->getAuthenticationTypes();
	$hasVisitorType = false;
	$visitorType = new Type ("Authentication", "edu.middlebury.harmoni", "Visitors");
	while($authTypes->hasNext()) {
		$authType = $authTypes->next();
		if ($visitorType->isEqual($authType)) {
			$hasVisitorType = true;
			break;
		}
	}
	if ($hasVisitorType && !$authN->isUserAuthenticatedWithAnyType()) {
		$harmoni->request->startNamespace('polyphony');
		
		$url = $harmoni->request->mkURL("auth", "login_type");
		$url->setValue("type", urlencode($visitorType->asString()));
		
		// Add return info to the visitor registration url
		$visitorReturnModules = array('view', 'ui1', 'ui2', 'versioning');
		if (in_array($harmoni->request->getRequestedModule(), $visitorReturnModules)) {
			$url->setValue('returnModule', $harmoni->request->getRequestedModule());
			$url->setValue('returnAction', $harmoni->request->getRequestedAction());
			$url->setValue('returnKey', 'node');
			$url->setValue('returnValue', SiteDispatcher::getCurrentNodeId());
		}
		
		$harmoni->request->endNamespace();
		
		return "\n\t<a href='".$url->write()."'>"._("Visitor Login")."</a>";
	}
	
	return null;
}


/*********************************************************
 * Uncomment and customize to enable a mapping update script
 * to associate LDAP logins with CAS logins
 *********************************************************/
// global $update001Types, $update001CasType;
// 
// $update001Types[] = array(
// 	'type' => new Type ("Authentication", "edu.middlebury.harmoni", "LDAP"),
// 	'cas_id_property' => 'middleburycollegeuid'
// );
// 
// $update001CasType = new Type ("Authentication", "edu.middlebury.harmoni", "CAS");


