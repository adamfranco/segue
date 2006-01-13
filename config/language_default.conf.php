<?php

/**
 * Set up the LanguageLocalization system
 *
 * USAGE: Copy this file to language.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: language_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */
 
// :: Set up language directories ::
	$configuration =& new ConfigurationProperties;
	$configuration->addProperty('default_language', $arg0 = 'en_US');
	$configuration->addProperty('applications', $arg1 = array (
		'segue' => MYDIR.'/main/languages',
		'polyphony'=> POLYPHONY.'/main/languages'
	));
	unset ($arg0, $arg1);
	Services::startManagerAsService("LanguageManager", $context, $configuration);