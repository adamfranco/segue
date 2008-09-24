<?php
/**
 * This is the main control script for the application.
 *
 * @package segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: setup.inc.php,v 1.11 2008/04/04 20:23:14 achapin Exp $
 */

/*********************************************************
 * Set a low debug level to not store up all queries.
 *********************************************************/
debug::level(-100);

/******************************************************************************
 * Start the session so that we can use the session for storage.
 ******************************************************************************/
if (file_exists(MYDIR.'/config/harmoni.conf.php'))
	require_once (MYDIR.'/config/harmoni.conf.php');
else
	require_once (MYDIR.'/config/harmoni_default.conf.php');
	
if (file_exists(MYDIR.'/config/action.conf.php'))
	require_once (MYDIR.'/config/action.conf.php');
else
	require_once (MYDIR.'/config/action_default.conf.php');
 
$harmoni->startSession();


/*********************************************************
 * If we pressed a button to reset segue, clear the session
 * and delete our tables.
 *********************************************************/
if (file_exists(MYDIR.'/config/debug.conf.php'))
	require_once (MYDIR.'/config/debug.conf.php');
else
	require_once (MYDIR.'/config/debug_default.conf.php');
		
if (isset($_REQUEST["reset_segue"]) 
	&& defined('ENABLE_RESET') 
	&& ENABLE_RESET) 
{
	$_SESSION = array();
	if (file_exists(MYDIR.'/config/database.conf.php'))
		require_once (MYDIR.'/config/database.conf.php');
	else
		require_once (MYDIR.'/config/database_default.conf.php');
	
	$dbc = Services::getService("DatabaseManager");
	$tableList = $dbc->getTableList($dbID);
	if (count($tableList)) {
		$queryString = "DROP TABLE `".implode("`, `", $tableList)."`;";
		print $queryString;
		$query = new GenericSQLQuery($queryString);
		$dbc->query($query, $dbID);
	}
}

/******************************************************************************
 * Include our configs
 ******************************************************************************/
require_once(HARMONI."/oki2/shared/ConfigurationProperties.class.php");
require_once(OKI2."/osid/OsidContext.php");

$configs = array(	
					'validation',
					'debug',
					'starting_site',
					'harmoni',
					'action',
					'database',
					'id',
                    'memcache',
					'logging',
					'recaptcha',
					'authentication_setup',
					'gui',
					'language',
					'help',
					'sets',
					'mime',
					'imageprocessor',
					'hierarchy',
					'authorization',
					'installer',
					'agent',
					'datamanager',
					'repository',
					'plugins',
					'post_config_setup',
					'viewer',
					'slots',
					'scheduling',
					'grading',
					'coursemanagement',
					'dataport',
					'tagging',
					'templates',
					'themes',
					'uploads',
					'welcome'
				);

foreach ($configs as $config) {
	if (file_exists(MYDIR.'/config/'.$config.'.conf.php'))
		require_once (MYDIR.'/config/'.$config.'.conf.php');
	else
		require_once (MYDIR.'/config/'.$config.'_default.conf.php');
}

/*********************************************************
 * Set our starting site if needed.
 *********************************************************/
if (defined('SEGUE_STARTING_SITE') && SEGUE_STARTING_SITE) {
	$harmoni->config->set("defaultModule","view");
	$harmoni->config->set("defaultAction","html");
	$harmoni->config->set("defaultParams",array("site" => SEGUE_STARTING_SITE));
}

/*********************************************************
 * Set a list of actions that require request tokens to prevent 
 * Cross-Site Request Forgery attacks. All actions that 
 * could potentially change data should require this.
 *
 * Actions in this list will not be able to be loaded directly.
 *********************************************************/
$harmoni->ActionHandler->addRequestTokenRequiredActions(array(
		"comments.*",
		"dataport.convert",
		"dataport.import",
		"media.delete",
		"media.update",
		"media.upload",
		"plugin_manager.update_ajax",
		"media.delete",
		"portal.copy_site",
		"roles.choose_agent",
		"roles.modify",
		"roles.rebuildImplicit",
		"slots.delete",
		"slots.edit",
		"ui1.add",
		"ui1.add_wiki_component",
		"ui1.addContent",
		"ui1.addMenuContent",
		"ui1.deleteComponent",
		"ui1.editContent",
		"ui1.editFlowOrg",
		"ui1.editHeader",
		"ui1.editMenu",
		"ui1.editNav",
		"ui1.editSite",
		"ui1.reorder",
		"ui1.theme_options",
		"ui2.add",
		"ui2.add_wiki_component",
		"ui2.addComponent",
		"ui2.createSubMenu",
		"ui2.deleteComponent",
		"ui2.modifyComponent",
		"ui2.moveComponent",
		"ui2.reorder",
		"updates.*",
		"versioning.revert"
	));
