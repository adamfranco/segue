<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_ajax.act.php,v 1.11 2007/09/04 15:07:43 adamfranco Exp $
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
 * @version $Id: update_ajax.act.php,v 1.11 2007/09/04 15:07:43 adamfranco Exp $
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
		$harmoni = Harmoni::instance();
		
		// Get the plugin asset id
		$harmoni->request->startNamespace('plugin_manager');
		$id = RequestContext::value('plugin_id');
		if (RequestContext::value('extended') == 'true')
			$showExtended = true;
		else
			$showExtended = false;
		$harmoni->request->endNamespace();
			
		// Get the plugin asset object
		$repositoryManager = Services::getService("Repository");
		$idManager = Services::getService("Id");
		$repository = $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		$asset = $repository->getAsset($idManager->getId($id));

		$pluginManager = Services::getService("Plugs");
		$plugin = $pluginManager->getPlugin($asset);

		
		header("Content-type: text/xml");
		print "<plugin>\n";
		if (!is_object($plugin)) {
			print "\t<markup>\n\t\t<![CDATA[";
			print $plugin;
			print "]]>\n\t</markup>\n";
		} else {
			if ($showExtended)
				$markup = $plugin->executeAndGetExtendedMarkup(TRUE);
			else
				$markup = $plugin->executeAndGetMarkup(TRUE);
				
			print "\t<markup>\n\t\t<![CDATA[";
			// CDATA sections cannot contain ']]>' and therefor cannot be nested
			// get around this by replacing the ']]>' tags in the markup.
			print preg_replace('/\]\]>/', '}}>', $markup);
			print "]]>\n\t</markup>\n";
			
		}
		print "</plugin>";
		
		exit();
	}
}

?>