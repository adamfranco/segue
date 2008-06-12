<?php
/**
 * This configuration file is for setting options for templates -- site starting
 * points. Templates are placed in two folders: 
 *		segue/templates-dist/ 	Templates distributed with Segue
 *		segue/templates-local/ 	Custom templates
 *
 * Custom templates with the same folder name as distributed templates will replace
 * the distributed template.
 *
 *
 * USAGE: Copy this file to templates.conf.php to set custom values.
 * 
 *
 * @since 6/12/08
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
 
 $templateMgr = Segue_Templates_TemplateManager::instance();
 
/*********************************************************
 * Define an ordered list of template ids (directory names).
 * 
 * If no order is specified, Alphabetical order will be used.
 * 
 * Any templates not listed here will be appended alphabetically to the end of 
 * the list.
 *********************************************************/
$templateMgr->setOrder(array(
// 	"Basic"
));

/*********************************************************
 * Define a list of disabled templates. Use this to disable
 * any distributed templates that you do not wish to be
 * available to your users.
 *
 * Each template can define (in its info.xml) a list of 
 * users and/or groups for whom the template is available.
 * That is a preferred method for fine-grained access control
 * of particular custom templates.
 *********************************************************/
$templateMgr->setDisabled(array(
// 	"Basic"
));