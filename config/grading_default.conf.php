<?php

/**
* Set up the GradingManager
*
* USAGE: Copy this file to grading.conf.php to set custom values.
*
* @package segue.config
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: grading_default.conf.php,v 1.1 2007/08/22 20:08:50 adamfranco Exp $
*/

// :: Set up the CourseManagementManager ::
$configuration =& new ConfigurationProperties;
$configuration->addProperty('database_index', $dbID);
Services::startManagerAsService("GradingManager", $context, $configuration);
