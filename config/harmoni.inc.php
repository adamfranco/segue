<?
// :: Version: $Id: harmoni.inc.php,v 1.3 2004/05/31 20:33:52 gabeschine Exp $


// :: set up the $harmoni object :: 
	$harmoni->config->set("useAuthentication",true);
	$harmoni->config->set("defaultModule","home");
	$harmoni->config->set("defaultAction","welcome");
	$harmoni->config->set("charset","utf-8");
	$harmoni->config->set("outputHTML",true);
	$harmoni->config->set("sessionName","AUTHN");
	$harmoni->config->set("sessionUseCookies",true);
	$harmoni->config->set("sessionCookiePath","/");
	$harmoni->config->set("sessionCookieDomain","middlebury.edu");

// :: setup the ActionHandler ::
	function callback_action(&$harmoni) {
		return $harmoni->pathInfoParts[0] . "." . $harmoni->pathInfoParts[1];
	}
	$harmoni->setActionCallbackFunction( "callback_action" );
	$harmoni->ActionHandler->addActionSource( new FlatFileActionSource( realpath(MYDIR."/main/modules"), ".act.php"));
	$harmoni->ActionHandler->addActionSource( new FlatFileActionSource( realpath(POLYPHONY."/main/modules"), ".act.php"));

// :: Set up the database connection ::
	$dbHandler=&Services::requireService("DBHandler");
	$dbID = $dbHandler->addDatabase( new MySQLDatabase("localhost","segue2","test","test") );
	$dbHandler->pConnect($dbID);
	unset($dbHandler); // done with that for now
	define("DBID",$dbID);

// :: Set up the SharedManager as this is required for the ID service ::
	Services::startService("Shared", $dbID, "segue2");


// :: Set up the Authentication and Login Handlers ::
	$harmoni->LoginHandler->setFailedLoginAction("auth.fail_redirect");
	$harmoni->LoginHandler->addNoAuthActions("auth.logout",
											"auth.fail",
											"auth.login",
											"language.change",
											"window.screen",
											"home.welcome",
											"collections.main",
											"collections.namebrowse",
											"collections.typebrowse"
											);
	
	//printpre($GLOBALS);
	
	Services::startService("AuthN", $dbID,"segue2");
	
	#########################
	# HANDLE AUTHENTICATION #
	# A) authenticated      #
	# B) not authenticated  #
	# C) attempting log in  #
	#########################
	
	Services::startService("Authentication");
	Services::startService("DBHandler");
	
	// :: get all the services we need ::
	$authHandler =& Services::getService("Authentication");
	
	// :: set up the DBAuthenticationMethod options ::
	$options =& new DBMethodOptions;
	$options->set("databaseIndex",$dbID);
	$options->set("tableName", "segue2.auth_n_user");
	$options->set("usernameField", "username");
	$options->set("passwordField", "password");
	$options->set("passwordFieldEncrypted", FALSE);
//	$options->set("passwordFieldEncryptionType", "databaseMD5");
	
	// :: create the DBAuthenticationMethod with the above options ::
	$dbAuthMethod =& new DBAuthenticationMethod($options);
	
	// :: add it to the handler ::
	$authHandler->addMethod("dbAuth",0,$dbAuthMethod);


// :: Layout and Theme Setup ::
	Services::registerService("Themes", "ThemeHandler");
	Services::startService("Themes");
	$harmoni->setTheme(new SimpleLinesTheme);


// :: Set up language directories ::
	Services::startService('Lang');
	$langLoc =& Services::getService ('Lang');
	$langLoc->addApplication('segue', MYDIR.'/main/languages');
	$langLoc->addApplication('polyphony', POLYPHONY.'/main/languages');
//	$langLoc->setLanguage("es_ES");
//	$langLoc->setLanguage("en_US");
	$languages =& $langLoc->getLanguages();

// :: Set up the DataManager ::
	HarmoniDataManager::setup($dbID);

// :: Set up the Hierarchy Manager ::
	$configuration = array(
		"type" => SQL_DATABASE,
		"database_index" => $dbID,
		"hierarchy_table_name" => "hierarchy",
		"hierarchy_id_column" => "id",
		"hierarchy_display_name_column" => "display_name",
		"hierarchy_description_column" => "description",
		"node_table_name" => "hierarchy_node",
		"node_hierarchy_key_column" => "fk_hierarchy",
		"node_id_column" => "id",
		"node_parent_key_column" => "fk_parent",
		"node_display_name_column" => "display_name",
		"node_description_column" => "description"
	);
	Services::startService("Hierarchy", $configuration);
	$hierarchyManager =& Services::getService("Hierarchy");
	$nodeTypes = array();
// 	$hierarchy =& $hierarchyManager->createHierarchy("Segue", "The Hierarchy for the Segue DR", $nodeTypes, FALSE, FALSE);
// 	printpre($hierarchy);

// :: Set up the DigitalRepositoryManager ::
	$configuration = array(
		"hierarchyId" => "1",
		"versionControlAll" => TRUE
	);
	
	Services::startService("DR", $configuration);