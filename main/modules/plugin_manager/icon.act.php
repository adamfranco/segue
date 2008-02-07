<?php
/**
 * @since 6/4/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: icon.act.php,v 1.5 2008/02/07 20:05:52 adamfranco Exp $
 */ 

/**
 * Answer the data for a plugin icon PNG image.
 * 
 * @since 6/4/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: icon.act.php,v 1.5 2008/02/07 20:05:52 adamfranco Exp $
 */
class iconAction
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('plugin_manager');
		$pluginManager = Services::getService("Plugs");
		$icon = $pluginManager->getPluginDir(
					HarmoniType::fromString(RequestContext::value('type')))
				."/icon.png";
		
		header("Content-Type: image/png");
		header('Content-Disposition: filename="icon.png"');
		
		if (!file_exists($icon)) {
			$icon = POLYPHONY_DIR."/icons/filetypes/tar.png";
		}
		
		print file_get_contents($icon);
		
		$harmoni->request->endNamespace();
		
		exit;
	}
	
}

?>