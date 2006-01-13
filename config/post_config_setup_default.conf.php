<?php

/**
 * Run some post-configuration setup.
 *
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: post_config_setup_default.conf.php,v 1.1 2006/01/13 18:30:22 adamfranco Exp $
 */

if (!isset($_SESSION['post_config_setup_complete'])) {

	// Exhibition Repository
	$repositoryManager =& Services::getService("Repository");
	$idManager =& Services::getService("Id");
	$exhibitionRepositoryId =& $idManager->getId("edu.middlebury.concerto.exhibition_repository");

	$repositories =& $repositoryManager->getRepositories();
	$exhibitionRepositoryExists = FALSE;
	while ($repositories->hasNext()) {
		$repository =& $repositories->next();
		if ($exhibitionRepositoryId->isEqual($repository->getId())) {
			$exhibitionRepositoryExists = TRUE;
			break;
		}
	}
	
	if (!$exhibitionRepositoryExists) {

		$exhibitionRepositoryType =& new Type (
						'System Repositories', 
						'edu.middlebury.concerto', 
						'Exhibitions',
						'A Repository for holding Exhibitions, their Slide-Shows and Slides');
		$repository =& $repositoryManager->createRepository(
								  "All Exhibitions",
								  "This is a Repository that holds all of the Exhibitions in Concerto.",
								  $exhibitionRepositoryType,
								  $exhibitionRepositoryId);


		$slideSchemaId =& $idManager->getId("edu.middlebury.concerto.slide_record_structure");
		$slideSchema =& $repository->createRecordStructure(
							"Slide Schema", 
							"This is the schema used for exhibition slides.", 
							"text/plain", 
							"", 
							$slideSchemaId);
		$slideSchema->createPartStructure(
							"target id", 
							"The Id of the asset that this slide is referencing.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.target_id"));
		$slideSchema->createPartStructure(
							"text position", 
							"The location of any text presented in the slide. (bottom, top, left, right)", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.text_position"));
		$slideSchema->createPartStructure(
							"display metadata", 
							"Whether or not to display the metadata of the associated asset referenced by target id.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "boolean"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.concerto.slide_record_structure.display_metadata"));


	}
	
	
	$_SESSION['post_config_setup_complete'] = TRUE;
}