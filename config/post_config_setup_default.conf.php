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
 * @version $Id: post_config_setup_default.conf.php,v 1.7 2006/03/14 22:13:55 cws-midd Exp $
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
							
		$navNodeSchema->createPartStructure(
							"child_order", 
							"The order of the children of this node", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.segue.nav_nod_rs.child_order"));

		$navNodeSchema->createPartStructure(
							"child_cells", 
							"The destination cells of the children of this node", 
							new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
							false, 
							false, 
							false,
							$idManager->getId("edu.middlebury.segue.nav_nod_rs.child_cells"));
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
			$pm->_installPlugin($type);
		}
	} else {
		$pm->_loadPlugins();
	}
	
	$results->free();

	$_SESSION['post_config_setup_complete'] = TRUE;
}