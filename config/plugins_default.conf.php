<?php

/**
 * Set up the PluginManager
 *
 * USAGE: Copy this file to plugins.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: plugins_default.conf.php,v 1.1 2006/01/24 20:04:35 cws-midd Exp $
 */
 
	require_once(MYDIR."/main/library/PluginManager/PluginManager.class.php");
	Services::registerService("PluginManager", "PluginManager");
	Services::createServiceAlias("PluginManager", "Plugs");


	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
	$configuration->addProperty('plugin_path', $path = MYPATH."/plugins");
	Services::startManagerAsService("PluginManager", $context, $configuration);