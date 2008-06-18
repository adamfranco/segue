<?php
/**
 * @since 1/28/08
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: dataport_default.conf.php,v 1.2 2008/03/14 15:38:30 adamfranco Exp $
 */ 
 
if (!defined('DATAPORT_TMP_DIR'))
	define('DATAPORT_TMP_DIR', '/tmp');

// if (!defined('DATAPORT_BACKUP_DIR'))
// 	define('DATAPORT_TMP_DIR', '/var/segue_backups');

/*********************************************************
 * Configuration for importing from Segue1
 *********************************************************/
// if (!defined('DATAPORT_SEGUE1_URL'))
// 	define('DATAPORT_SEGUE1_URL', 'http://segue.example.edu/');

// if (!defined('DATAPORT_SEGUE1_SECRET_KEY'))
// 	define('DATAPORT_SEGUE1_SECRET_KEY', 'sadfj234j1');

// if (!defined('DATAPORT_SEGUE1_SECRET_VALUE'))
// 	define('DATAPORT_SEGUE1_SECRET_VALUE', '28usafnjm023jfa0235rhj2052');

/*********************************************************
 * Define which authentication types map users to the Segue 1
 * system.
 *********************************************************/
// $GLOBALS['dataport_migration_auth_types'] = array(
// 	new Type('Authentication', 'edu.middlebury.harmoni', 'LDAP')
// );