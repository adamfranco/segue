<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addnav.act.php,v 1.3 2006/02/22 20:29:56 adamfranco Exp $
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
 * @version $Id: addnav.act.php,v 1.3 2006/02/22 20:29:56 adamfranco Exp $
 */
class addnavAction 
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
	 * Return the "unauthorized" string to pring
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
		
		$type =& new Type('site_components', 'edu.middlebury.segue', 'navigation', 'Navigational Node');
		
		$asset =& $repository->createAsset("Default Title", 
										"", 
										$type);
		
		$parentAsset->addAsset($asset->getId());
		
		// Defaults for navigational layouts
		switch (RequestContext::value('nav_type')) {
			case 'single':
				$arrangement ='columns';
				$numCells = 1;
				$targetOverride = 2;
				break;
			default:
				$arrangement ='nested';
				$numCells = 2;
				$targetOverride = 2;
		}		
		
		// Add a default navigation record structure
		$navStructId =& $idManager->getId('Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs');
		$record =& $asset->createRecord($navStructId);
		
		// layout_arrangement
		$partStructId =& $idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.layout_arrangement');
		$value =& String::withValue($arrangement);
		$record->createPart($partStructId, $value);
		
		// num_cells
		$partStructId =& $idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.num_cells');
		$value =& Integer::withValue($numCells);
		$record->createPart($partStructId, $value);
		
		// target_override
		$partStructId =& $idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.target_override');
		$value =& Integer::withValue($targetOverride);
		$record->createPart($partStructId, $value);
		
		
		RequestContext::locationHeader($harmoni->request->quickURL(
			"site", "editview",
			array("node" => RequestContext::value('return_node'))));
	}
}

?>