<?php

/**
 * The main configuration file.
 *
 * USAGE: Copy this file to harmoni.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: harmoni_default.conf.php,v 1.13 2007/05/09 20:04:31 adamfranco Exp $
 */

// :: set up the $harmoni object :: 
	$harmoni->config->set("defaultModule","home");
	$harmoni->config->set("defaultAction","welcome");
	$harmoni->config->set("programTitle","Segue");
	$harmoni->config->set("sessionName","SEGUE_SESSID");
	$harmoni->config->set("sessionUseCookies",true);
	// In order to prevent User's from including their SESSION IDs in urls that they
	// copy/paste for others, we will force the usage of cookies. This will prevent
	// inadvertant session fixation problems.
	$harmoni->config->set("sessionUseOnlyCookies",true);
	$harmoni->config->set("sessionCookiePath","/");
	$harmoni->config->set("sessionCookieDomain","middlebury.edu");
	
	// tell harmoni to post-process all actions with this specified action.
	// the action takes the result from previous actions and builds a display
	// screen from it.
	
	// until polyphony has been updated to use this functionality, we must ignore
	// it.
	$postProcessIgnoreList = array(
									"language.*",
									"repository.*",
									"plugin_manager.*",
									"ui1.view",
									"ui1.editview",
									"ui1.editContent",
									"ui2.view",
									"ui2.editview",
									"ui2.arrangeview",
									"help.*"
								);
	
	$harmoni->setPostProcessAction("window.display", $postProcessIgnoreList);
	
	$context =& new OsidContext;
	$context->assignContext('harmoni', $harmoni);