<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.1 2007/09/05 14:01:58 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(MYDIR."/main/library/PluginManager/SeguePlugins/SeguePluginsAjaxPlugin.abstract.php");

/**
 * This action provides a wizard for editing a navigation node
 * 
 * @since 5/11/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.1 2007/09/05 14:01:58 adamfranco Exp $
 */
class editSiteAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/11/07
	 */
	function getHeadingText () {
		return _("Edit Site");
	}
}

?>