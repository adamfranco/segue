<?php
/**
 * @since 1/28/08
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: dataport_default.conf.php,v 1.2 2008/03/14 15:38:30 adamfranco Exp $
 */ 
 
if (!defined('DATAPORT_TMP_DIR'))
	define('DATAPORT_TMP_DIR', '/tmp');

// if (!defined('DATAPORT_BACKUP_DIR'))
// 	define('DATAPORT_TMP_DIR', '/var/segue_backups');

// if (!defined('WGET_PATH'))
// 	define('WGET_PATH', '/usr/bin/wget');

/*********************************************************
 * Configuration for importing from Segue1
 *********************************************************/
// if (!defined('DATAPORT_SEGUE1_URL'))
// 	define('DATAPORT_SEGUE1_URL', 'http://segue.example.edu/');

// if (!defined('DATAPORT_SEGUE1_SECRET_KEY'))
// 	define('DATAPORT_SEGUE1_SECRET_KEY', 'sadfj234j1');

// if (!defined('DATAPORT_SEGUE1_SECRET_VALUE'))
// 	define('DATAPORT_SEGUE1_SECRET_VALUE', '28usafnjm023jfa0235rhj2052');

/*********************************************************
 * Define which authentication types map users to the Segue 1
 * system.
 *********************************************************/
// $GLOBALS['dataport_migration_auth_types'] = array(
// 	new Type('Authentication', 'edu.middlebury.harmoni', 'LDAP')
// );

/*********************************************************
 * Enable recording of where Segue sites have been migrated
 * to. This will allow automatic redirection of migrated sites.
 *********************************************************/
// define('DATAPORT_ENABLE_EXPORT_REDIRECT', true);

/*********************************************************
 * Define which export links are available and who they
 * are available to.
 *********************************************************/
$GLOBALS['dataport_export_types'] = array(
// 	'html' => array(
// 		'help' => 'http://mediawiki.middlebury.edu/wiki/LIS/Migrate_From_Segue',
// 		'groups' => array(
// 			'1', // Administrators
// 			"CN=LIS Web Applications,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=LIS Curricular Technology Team,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=Digital Media Tutors,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 		),
// 	),
// 	'wordpress' => array(
// 		'help' => 'http://mediawiki.middlebury.edu/wiki/LIS/Migrate_From_Segue',
// 		'groups' => array(
// 			'1', // Administrators
// 			"CN=LIS Web Applications,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=LIS Curricular Technology Team,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=Digital Media Tutors,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 		),
// 	),
// 	'files' => array(
// 		'help' => 'http://mediawiki.middlebury.edu/wiki/LIS/Migrate_From_Segue',
// 		'groups' => array(
// 			'1', // Administrators
// 			"CN=LIS Web Applications,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=LIS Curricular Technology Team,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 			"CN=Digital Media Tutors,OU=General,OU=Groups,DC=middlebury,DC=edu",
// 		),
// 	),
);

/*********************************************************
 * If we are on the viewfile action and coming from a trusted 
 * host, log in as the view-only admin
 *********************************************************/
// $viewAllHosts = array(
// 	'140.233.92.65',
// );
// $viewAllActions = array(
// 	'repository.viewfile',
// 	'repository.viewfile_flash',
// 	'repository.viewthumbnail',
// 	'repository.viewthumbnail_flash',
// );
// 
// $harmoni = Harmoni::instance();
// if (in_array($_SERVER['REMOTE_ADDR'], $viewAllHosts) && in_array($harmoni->request->getRequestedModuleAction(), $viewAllActions)) {
// 	$authN = Services::getService("AuthN");
// 	if (!$authN->isUserAuthenticatedWithAnyType()) {
// 		$idMgr = Services::getService("Id");
// 		// Verify that the asset id, the record Id, and the file name match.
// 		try {
// 			$harmoni->request->update();
// 			$harmoni->request->startNamespace("polyphony-repository");
// 			$repositoryId =$idMgr->getId(RequestContext::value("repository_id"));
// 			$assetId =$idMgr->getId(RequestContext::value("asset_id"));
// 			$recordId =$idMgr->getId(RequestContext::value("record_id"));
// 			$file = MediaFile::withIds($repositoryId, $assetId, $recordId);
// 		} catch (UnknownIdException $e) {
// 			throw new PermissionDeniedException("File ids do not match. ".$e->getMessage());
// 		}
// 		
// 		// Verify that the record Id and the file name match.
// 		if (!in_array(RequestContext::value('file_name'), array($file->getFilename(), $file->getThumbnailFilename()))) {
// 				throw new PermissionDeniedException("File name doesn't match file id.");
// 		}
// 		
// 		$harmoni->request->endNamespace();
// 		
// 		// Set the user.
// 		$authType = new Type ("Authentication", "edu.middlebury.harmoni", "Harmoni DB");
// 		$_SESSION['__AuthenticatedAgents']['Authentication::edu.middlebury.harmoni::Harmoni DB'] = $idMgr->getId('17008');
// 		$authZ = Services::getService("AuthZ");
// 		$isAuthorizedCache = $authZ->getIsAuthorizedCache();
// 		$isAuthorizedCache->dirtyUser();
// 	}
// }

/*********************************************************
 * Migration Reminder Emails
 *********************************************************/
// define('MIGRATION_REMINDER_EMAIL_FROM_NAME', 'Middlebury College Webmaster');
// define('MIGRATION_REMINDER_EMAIL_FROM_MAIL', 'webmaster@middlebury.edu');
// define('MIGRATION_REMINDER_EMAIL_SUBJECT', 'Segue Migration Reminder for [[USER]]');
// define('MIGRATION_REMINDER_EMAIL_MESSAGE', '<html>
// <head>
// 	<title>Segue Migration Reminder for [[USER]]</title>
// </head>
// <body>
// 	<p>Greetings [[USER]],</p>
// 	
// 	<p>Segue will be shut down on August 31st, 2012. You are listed as an administrator
// 	of the following Segue sites whose migration status is "Incomplete".</p>
// 	
// 	[[SITE_LIST]]
// 	
// 	<p>If you would like to preserve these sites please migrate or archive them.
// 	<a href="http://mediawiki.middlebury.edu/wiki/LIS/Migrate_From_Segue">Instructions on 
// 	migrating and archiving sites</a> can be found in the LIS Wiki. If you have 
// 	questions about the migration process, please contact your LIS Liason
// 	(<a href="http://go.middlebury.edu/liasons">go/liaisons</a>).</p>
// 	
// 	<p>After you have migrated or archived each site, please log into Segue and 
// 	update its status to stop receiving these reminders for that site. If you 
// 	simply no longer need a site you can log into Segue and mark it as 
// 	"No Longer Needed" to stop receiving these reminders for that site.</p>
// 		
// 	
// </body>
// </html>');
// 
// define('MIGRATION_REMINDER_EMAIL_TEST_ONLY', true);
// define('MIGRATION_REMINDER_EMAIL_TEST_MAX', 2);
// define('MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT', 'afranco@middlebury.edu');
