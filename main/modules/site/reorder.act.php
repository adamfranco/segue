<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: reorder.act.php,v 1.4 2006/02/22 20:29:56 adamfranco Exp $
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
 * @version $Id: reorder.act.php,v 1.4 2006/02/22 20:29:56 adamfranco Exp $
 */
class reorderAction 
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
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId(RequestContext::value('parent_id')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Node</em>.");
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
		$childId =& $idManager->getId(RequestContext::value('node'));
		$parentRenderer =& NodeRenderer::forAsset($parentAsset, $null = null);
		$orderedSet =& $parentRenderer->getChildOrder();
		
		printpre($orderedSet);
		
		$before = RequestContext::value('before');
		if ($before == 'end')
			$orderedSet->moveToEnd($childId);
		else if ($before == 'beginning')
			$orderedSet->moveToBeginning($childId);
		else {
			$beforeId =& $idManager->getId($before);
			if ($orderedSet->getPosition($beforeId) > 0)
				$orderedSet->moveToPosition($childId, $orderedSet->getPosition($beforeId) - 1);
			else
				$orderedSet->moveToPosition($childId, 0);
		}
		
		printpre($orderedSet);
		
		$parentRenderer->saveChildOrder();
		
		RequestContext::locationHeader($harmoni->request->quickURL(
			"site", "editview",
			array("node" => RequestContext::value('return_node'))));
	}
}

?>