<?php

/**
 * Argument Validation configuration. 
 * - Disable argument validation to speed execution. 
 * - Enable it for development and debugging.
 *
 * It may be possible to further speed execution by commenting out the check
 * for "DISABLE_VALIDATION" in 
 *		harmoni/core/utilities/ArgumentValidator.class.php
 * so that the validate() method simply returns true. Do this at your own risk.
 *
 * USAGE: Copy this file to validation.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: validation_default.conf.php,v 1.2 2006/01/13 18:51:17 adamfranco Exp $
 */

/*********************************************************
 * Argument Validation configuration. 
 * - Disable argument validation to speed execution. 
 * - Enable it for development and debugging.
 *
 * It may be possible to further speed execution by commenting out the check
 * for "DISABLE_VALIDATION" in 
 *		harmoni/core/utilities/ArgumentValidator.class.php
 * so that the validate() method simply returns true. Do this at your own risk.
 *********************************************************/
define('DISABLE_VALIDATION', false);