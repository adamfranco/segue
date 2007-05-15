<?php
/**
 * @since 5/15/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editMenu.act.php,v 1.1 2007/05/15 16:48:24 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/editFlowOrg.act.php");

/**
 * This action allows editing of menu options
 * 
 * @since 5/15/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editMenu.act.php,v 1.1 2007/05/15 16:48:24 adamfranco Exp $
 */
class editMenuAction
	extends editFlowOrgAction
{
		
	/**
	 * Print rows/columns controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/13/07
	 */
	function printRowsColumns ( &$siteComponent, &$step ) {		
	
	}
	
	/**
	 * Save the rows/columns results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveRowsColumns ( &$component, $values ) {
		return true;
	}
	
}

?>