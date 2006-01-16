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
 * @version $Id: post_config_setup_default.conf.php,v 1.3 2006/01/16 20:12:03 adamfranco Exp $
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


		$navNodeSchemaId =& $idManager->getId("edu.middlebury.segue.nav_nod_rs");
		$navNodeSchema =& $repository->createRecordStructure(
							"Navigational-Node RecordStructure", 
							"This is the RecordStruction used for navigational-nodes.", 
							"text/plain", 
							"", 
							$navNodeSchemaId);
		$navNodeSchema->createPartStructure(
							"num_cells", 
							"The number of cells that this node's children will be arranged in.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "integer"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.segue.nav_nod_rs.num_cells"));
		$navNodeSchema->createPartStructure(
							"layout_arrangement", 
							"The arrangement of the layout of this navigational-node: rows, columns, nested", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.segue.nav_nod_rs.layout_arrangement"));
		$navNodeSchema->createPartStructure(
							"target_override", 
							"The cell-number to use to use for the target for this node and descendent nodes: 1 to 'num_cells'.", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "integer"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.segue.nav_nod_rs.target_override"));


	}
	
	
	$_SESSION['post_config_setup_complete'] = TRUE;
}