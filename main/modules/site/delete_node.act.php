<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete_node.act.php,v 1.4 2006/03/07 19:27:27 adamfranco Exp $
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
 * @version $Id: delete_node.act.php,v 1.4 2006/03/07 19:27:27 adamfranco Exp $
 */
class delete_nodeAction 
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
			$idManager->getId("edu.middlebury.authorization.delete"),
			$idManager->getId(RequestContext::value('node')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to delete this <em>Node</em>.");
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
		$assetId =& $idManager->getId(RequestContext::value('node'));
		
		// Log the success or failure
		if (Services::serviceAvailable("Logging")) {
			$loggingManager =& Services::getService("Logging");
			$log =& $loggingManager->getLogForWriting("Segue");
			$formatType =& new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType =& new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$asset =& $repository->getAsset($assetId);
			$item =& new AgentNodeEntryItem("Delete Node", "Node deleted: <br/>&nbsp;&nbsp;&nbsp;&nbsp;DisplayName: ".$asset->getDisplayName()."<br/>&nbsp;&nbsp;&nbsp;&nbsp; Id: ".$assetId->getIdString()."<br/>&nbsp;&nbsp;&nbsp;&nbsp;Type: ".Type::typeToString($asset->getAssetType()));
			$item->addNodeId($assetId);
			$renderer =& NodeRenderer::forAsset($asset);
			$siteRenderer =& $renderer->getSiteRenderer();
			$item->addNodeId($siteRenderer->getId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$repository->deleteAsset($assetId);
		
		RequestContext::locationHeader($harmoni->request->quickURL(
			"site", "editview",
			array("node" => RequestContext::value('return_node'))));
	}
}

?>