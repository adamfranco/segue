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
 * @version $Id: plugins_default.conf.php,v 1.3 2007/01/12 20:27:59 adamfranco Exp $
 */
 
	require_once(MYDIR."/main/library/PluginManager/PluginManager.class.php");
	Services::registerService("PluginManager", "PluginManager");
	Services::createServiceAlias("PluginManager", "Plugs");


	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
	$configuration->addProperty('plugin_path', $path = MYPATH."/plugins");
	$configuration->addProperty('authN_priority', $prior = 'Middlebury LDAP');
	Services::startManagerAsService("PluginManager", $context, $configuration);
	
	
	$pluginManager =& Services::getService("PluginManager");
// 	$pluginManager->_installPlugin(new Type ('SeguePlugins', 'edu.middlebury', 'TextBlock'));
	