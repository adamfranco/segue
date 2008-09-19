<?php
/**
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_comment_plugins.act.php,v 1.1 2008/04/11 20:07:41 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/list_plugins.act.php");

/**
 * 
 * 
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_comment_plugins.act.php,v 1.1 2008/04/11 20:07:41 adamfranco Exp $
 */
class list_comment_pluginsAction 
	extends list_pluginsAction
{
	
	/**
	 * Answer the types.
	 * 
	 * @return array
	 * @access protected
	 * @since 4/11/08
	 */
	protected function getTypes () {
		$pluginManager = Services::getService("Plugs");
		$possibleCommentTypes = array(
			new Type ('SeguePlugins', 'edu.middlebury', 'TextBlock', EduMiddleburyTextBlockPlugin::getPluginDescription()),
			new Type ('SeguePlugins', 'edu.middlebury', 'Download', EduMiddleburyDownloadPlugin::getPluginDescription()),
			new Type ('SeguePlugins', 'edu.middlebury', 'AudioPlayer', EduMiddleburyAudioPlayerPlugin::getPluginDescription()),
			);
		
		$types = array();
		foreach ($possibleCommentTypes as $type) {
			if ($pluginManager->isEnabled($type))
				$types[] = $type;
		}
		
		return $types;
	}
}

?>