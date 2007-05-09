<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_ajax.act.php,v 1.8 2007/05/09 15:28:13 adamfranco Exp $
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
 * @version $Id: update_ajax.act.php,v 1.8 2007/05/09 15:28:13 adamfranco Exp $
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

		$pluginManager =& Services::getService("Plugs");
		$plugin =& $pluginManager->getPlugin($asset);
		$plugin->setShowControls(TRUE);

		
		$harmoni->request->startNamespace(get_class($plugin).':'.$id);
		header("Content-type: text/xml");
		print "<plugin>\n";
		if (!is_object($plugin)) {
			print "\t<markup>\n\t\t<![CDATA[";
			print $plugin;
			print "]]>\n\t</markup>\n";
		} else {
			$baseUrl =& $harmoni->request->mkURL();
			$markup = $plugin->executeAndGetMarkup($baseUrl);
			print "\t<markup>\n\t\t<![CDATA[";
			// CDATA sections cannot contain ']]>' and therefor cannot be nested
			// get around this by replacing the ']]>' tags in the markup.
			print preg_replace('/\]\]>/', '}}>', $markup);
			print "]]>\n\t</markup>\n";
			
		}
		print "</plugin>";
		$harmoni->request->endNamespace();
		
		exit();
	}
}

?>