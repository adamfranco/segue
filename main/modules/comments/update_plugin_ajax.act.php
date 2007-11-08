<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_plugin_ajax.act.php,v 1.1 2007/11/08 22:07:24 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_plugin_ajax.act.php,v 1.1 2007/11/08 22:07:24 adamfranco Exp $
 */
class update_plugin_ajaxAction 
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
// 		$repository = $repositoryManager->getRepository(
// 			$idManager->getId("edu.middlebury.segue.sites_repository"));
// 		
// 		$asset = $repository->getAsset($idManager->getId($id));
// 
// // 		$pluginManager = Services::getService("Plugs");
// // 		$plugin = $pluginManager->getPlugin($asset);
// // 		
// // 		$plugin->setUpdateAction('comments', 'update_ajax');
		
		$commentManager = CommentManager::instance();
		$comment = $commentManager->getComment($idManager->getId($id));
		
		header("Content-type: text/xml");
		print "<plugin>\n";
		if (!is_object($comment)) {
			print "\t<markup>\n\t\t<![CDATA[";
			print $comment;
			print "]]>\n\t</markup>\n";
		} else {
			$markup = $comment->getBody();
				
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