<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addplugin.act.php,v 1.1 2007/03/01 20:12:58 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addplugin.act.php,v 1.1 2007/03/01 20:12:58 adamfranco Exp $
 */
class addpluginAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId(RequestContext::value('parent_id')));
	}
	
	/**
	 * Return the "unauthorized" string to print
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to add a child to this <em>Node</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		$parentAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('parent_id')));
		
		$type =& Type::fromString(urldecode(RequestContext::value('type')));
		
		$asset =& $repository->createAsset("Default Title", 
										"", 
										$type);
		
		$parentAsset->addAsset($asset->getId());
		
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Segue");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item =& new AgentNodeEntryItem("Create Content", "Plugin added: ".Type::typeToString($type));
			$item->addNodeId($asset->getId());
			$item->addNodeId($parentAsset->getId());
			$renderer =& NodeRenderer::forAsset($asset);
			$siteRenderer =& $renderer->getSiteRenderer();
			$item->addNodeId($siteRenderer->getId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		RequestContext::locationHeader($harmoni->request->quickURL(
			"site", "editview",
			array("node" => RequestContext::value('return_node'))));
	}
}

?>