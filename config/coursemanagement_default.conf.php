<?php

/**
* Set up the CourseManagementManager
*
* USAGE: Copy this file to coursemanagament.conf.php to set custom values.
*
* @package concerto.config
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: coursemanagement_default.conf.php,v 1.2 2007/09/04 18:00:42 adamfranco Exp $
*/


/************************************************
/ Set this to true to create defualt terms
**********************************************/
$CREATE_TERMS =true;


// :: Set up the CourseManagementManager ::
$configuration = new ConfigurationProperties;
$configuration->addProperty('database_index', $dbID);

$courseManagamentHierarchyId = "edu.middlebury.authorization.hierarchy";
$courseManagementId ="edu.middlebury.coursemanagement";


$configuration->addProperty('hierarchy_id', $courseManagamentHierarchyId);
$configuration->addProperty('course_management_id', $courseManagementId);



$configuration->addProperty('whether_to_add_terms', $CREATE_TERMS);



for($year = 2004; $year < 2010; $year++){
	

	
	$array = array();
	$array['name'] = "Winter ".$year;
	$array['start'] = Timestamp::fromString($year."-01-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-02-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Winter");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Spring ".$year;
	$array['start'] = Timestamp::fromString($year."-02-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-06-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Spring");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Summer ".$year;
	$array['start'] = Timestamp::fromString($year."-06-01T00:00:00"); 
	$array['end'] = Timestamp::fromString($year."-09-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Summer");
	$terms[] = $array;
	
	$array = array();
	$array['name'] = "Fall ".$year;
	$array['start'] = Timestamp::fromString($year."-09-01T00:00:00"); 
	$array['end'] = Timestamp::fromString(($year+1)."-01-01T00:00:00"); 
	$array['type'] = new Type("TermType","edu.middlebury","Fall");
	$terms[] = $array;
	
}

$configuration->addProperty('terms_to_add', $terms);
$configuration->addProperty('authority', $authority="edu.middlebury");


Services::startManagerAsService("CourseManagementManager", $context, $configuration);
