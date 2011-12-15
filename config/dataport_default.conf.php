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
 * Define which export links are available and who they
 * are available to.
 *********************************************************/
$GLOBALS['dataport_export_types'] = array(
// 	'wordpress' => array(
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
// 			$harmoni->request->startNamespace("polyphony-repository");
// 			$repositoryId =$idMgr->getId(RequestContext::value("repository_id"));
// 			$assetId =$idMgr->getId(RequestContext::value("asset_id"));
// 			$recordId =$idMgr->getId(RequestContext::value("record_id"));
// 			$repositoryManager = Services::getService("Repository");
// 			$repository = $repositoryManager->getRepository($repositoryId);
// 			$asset =$repository->getAsset($assetId);
// 			$record = $asset->getRecord($recordId);
// 		} catch (UnknownIdException $e) {
// 			throw new PermissionDeniedException("File ids do not match. ".$e->getMessage());
// 		}
// 		
// 		// Verify that the record Id and the file name match.
// 		// Copied from viewfile.act.php
// 		require_once(POLYPHONY.'/main/modules/repository/viewfile.act.php');
// 		// Get the parts for the record.
// 		$partIterator =$record->getParts();
// 		$parts = array();
// 		while($partIterator->hasNext()) {
// 			$part =$partIterator->next();
// 			$partStructure =$part->getPartStructure();
// 			$partStructureId =$partStructure->getId();
// 			$parts[$partStructureId->getIdString()] =$part;
// 		}
// 		$size = RequestContext::value("size");
// 		$websafe = RequestContext::value("websafe");
// 		// See if we are passed a size
// 		if (is_numeric($size))
// 			$size = intval($size);
// 		else
// 			$size = FALSE;
// 		if ($websafe)
// 			$websafe = TRUE;
// 		else
// 			$websafe = FALSE;
// 		$imgProcessor = Services::getService("ImageProcessor");
// 		// If we want to (and can) resize the file, do so
// 		if (($size || $websafe)
// 			&& $imgProcessor->isFormatSupported($parts['MIME_TYPE']->getValue())) 
// 		{
// 			$imageCache = new RepositoryImageCache($record->getId(), $size, $websafe, $parts);
// 			if ($imageCache->getCachedFilename() != RequestContext::value('file_name'))
// 				throw new PermissionDeniedException("File name doesn't match file id.");
// 		} else {
// 			if ($parts['FILE_NAME']->getValue() != RequestContext::value('file_name'))
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