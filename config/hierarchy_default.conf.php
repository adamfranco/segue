<?php

/**
 * Set up the HierarchyManager
 *
 * USAGE: Copy this file to hierarchy.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: hierarchy_default.conf.php,v 1.4 2008/04/08 19:43:24 adamfranco Exp $
 */
 
// :: Set up the Hierarchy Manager ::
	$configuration = new ConfigurationProperties;
	$configuration->addProperty('database_index', $dbID);
	$configuration->addProperty('database_name', $dbName);
	$configuration->addProperty('harmoni_db_name', 'segue_db');
	Services::startManagerAsService("HierarchyManager", $context, $configuration);

// 	require_once(HARMONI."/oki2/AuthZ2/hierarchy/HierarchyManager.class.php");
// 	$mgr = new AuthZ2_HierarchyManager;
// 	$mgr->assignConfiguration($configuration);
// 	Services::registerObjectAsService("HierarchyManager", $mgr);