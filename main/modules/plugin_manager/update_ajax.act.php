<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_ajax.act.php,v 1.2 2006/01/17 21:30:58 adamfranco Exp $
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
 * @version $Id: update_ajax.act.php,v 1.2 2006/01/17 21:30:58 adamfranco Exp $
 */
class update_ajaxAction 
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
		$harmoni =& Harmoni::instance();
		
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
		
		$configuration =& new ConfigurationProperties;
		$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
		
		
		$plugin =& Plugin::newInstance($asset, $configuration);
		
		$harmoni->request->startNamespace(get_class($plugin).':'.$id);
		if (!is_object($plugin)) {
			print $plugin;
		} else {
			$baseUrl =& $harmoni->request->mkURL();
			print $plugin->executeAndGetMarkup($baseUrl);
		}
		$harmoni->request->endNamespace();
		
		exit();
	}
}

?>