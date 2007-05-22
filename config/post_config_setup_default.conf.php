<?php

/**
 * Run some post-configuration setup.
 *
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: post_config_setup_default.conf.php,v 1.11 2007/05/22 20:17:59 adamfranco Exp $
 */
if (!isset($_SESSION['post_config_setup_complete'])) {
	// Exhibition Repository
	$repositoryManager =& Services::getService("Repository");
	$idManager =& Services::getService("Id");
	$siteRepositoryId =& $idManager->getId("edu.middlebury.segue.sites_repository");

	$repositories =& $repositoryManager->getRepositories();
	$siteRepositoryExists = FALSE;
	while ($repositories->hasNext()) {
		$repository =& $repositories->next();
		if ($siteRepositoryId->isEqual($repository->getId())) {
			$siteRepositoryExists = TRUE;
			break;
		}
	}
	
	if (!$siteRepositoryExists) {

		$siteRepositoryType =& new Type (
						'System Repositories', 
						'edu.middlebury.segue', 
						'Site',
						'A Repository for holding the sites of Segue');
		$repository =& $repositoryManager->createRepository(
								  "All Sites",
								  "This is a Repository that holds all of the Sites in Segue.",
								  $siteRepositoryType,
								  $siteRepositoryId);
	}

	$schemas =& $repository->getRecordStructures();
	$pluginSchemaExists = false;
	$pluginSchemaId = $idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs");
	
	while ($schemas->hasNext()) {
		$schema =& $schemas->next();
		if ($pluginSchemaId->isEqual($schema->getId())) {
			$pluginSchemaExists = TRUE;
		}
	}
	
	/*********************************************************
	 * SeguePlugin Schema
	 *********************************************************/	
	if (!$pluginSchemaExists) {
			
		$schema =& $repository->createRecordStructure(
							"Segue Plugin RecordStructure", 
							"This is the RecordStruction used for common Segue Plugin data.", 
							"text/plain", 
							"", 
							$pluginSchemaId,
							true);
							
		$schema->createPartStructure(
							"raw_description", 
							"A raw description field. Data stored here is used interally to to plugin.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"));
	}
	
	// check if Install default plugins
	$db =& Services::getService("DBHandler");
	$pm =& Services::getService("Plugs");
	$query = new SelectQuery();
	$query->addTable("plugin_type");
	$query->addColumn("*");
	
	$results =& $db->query($query, IMPORTER_CONNECTION);

	if ($results->getNumberOfRows() == 0) {
		// install default (registered) plugins
		$plugins = $pm->getRegisteredPlugins();
		// iterate through registered plugins and install them
		foreach ($plugins as $type) {
			$pm->installPlugin($type);
		}
	} else {
		$pm->_loadPlugins();
	}
	
	$results->free();
	
	
	// Check for the dublin core record structure
	$dcId =& $idManager->getId('dc');
	$dcExists = FALSE;
	$recStructs =& $repository->getRecordStructures();
	while ($recStructs->hasNext()) {
		$recStruct =& $recStructs->next();
		if ($dcId->isEqual($recStruct->getId())) {
			$dcExists = true;
			break;
		}
	}
	
	if (!$dcExists) {
		$array = array();
		$importer =& XMLRepositoryImporter::withObject(
			$array,
			$repository,
			MYDIR."/sampledata/SchemaInstallCollection.xml", 
			"insert");
		$importer->parseAndImportBelow("recordstructure");
		if ($importer->hasErrors()) {
			$importer->printErrorMessages();
			exit;
		}
	}

	$_SESSION['post_config_setup_complete'] = TRUE;
}