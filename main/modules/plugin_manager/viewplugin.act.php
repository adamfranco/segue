<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewplugin.act.php,v 1.1 2007/05/09 20:04:32 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewplugin.act.php,v 1.1 2007/05/09 20:04:32 adamfranco Exp $
 */
class viewpluginAction 
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
	function &execute () {
		ob_start();
		$harmoni =& Harmoni::instance();
		
		/*********************************************************
		 * Other headers and footers
		 *********************************************************/
		$outputHandler =& $harmoni->getOutputHandler();
		
		
		// Add our common Harmoni javascript libraries
		require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");
		
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/TabbedContent.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/prototype.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/js_quicktags.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/MediaLibrary.js'></script>";
		print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/MediaLibrary.css'/>";
		
		$outputHandler->setHead(ob_get_clean());
		
		
		// Get the plugin asset id
		$harmoni->request->startNamespace('plugin_manager');
		$id = RequestContext::value('plugin_id');
		$harmoni->request->endNamespace();
			
		// Get the plugin asset object
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		$asset =& $repository->getAsset($idManager->getId($id));

		$pluginManager =& Services::getService("Plugs");
		$plugin =& $pluginManager->getPlugin($asset);

		
		if (!is_object($plugin)) {
			print $plugin;
		} else {
			print $plugin->executeAndGetMarkup(TRUE);			
		}
		
// 		exit();

		$block =& new UnstyledBlock(ob_get_clean());
		return $block;
	}
}

?>