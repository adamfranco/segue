<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.4 2006/04/13 19:39:15 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.4 2006/04/13 19:39:15 adamfranco Exp $
 */
class moveComponentAction 
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
		
		return true;
// 		return $authZ->isUserAuthorized(
// 			$idManager->getId("edu.middlebury.authorization.modify"),
// 			$idManager->getId(RequestContext::value('parent_id')));
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
		$testDocument =& new DOMIT_Document();
		$testDocument->setNamespaceAwareness(true);
		$success = $testDocument->loadXML(MYDIR."/main/library/SiteDisplay/test/testSite.xml");

		if ($success !== true) {
			throwError(new Error("DOMIT error: ".$testDocument->getErrorCode().
				"<br/>\t meaning: ".$testDocument->getErrorString()."<br/>", "SiteDisplay"));
		}

		$director =& new XmlSiteDirector($testDocument);
		
		// Get the target organizer's Id & Cell
		$targetId = RequestContext::value('destination');
		preg_match("/^(.+)_cell:(.+)$/", $targetId, $matches);
		$targetOrgId = $matches[1];
		$targetCell = $matches[2];
		
		$component =& $director->getSiteComponentById(RequestContext::value('component'));
		$newOrganizer =& $director->getSiteComponentById($targetOrgId);
		$oldCellId = $newOrganizer->putSubcomponentInCell($component, $targetCell);
		
		// If the targetCell was a target for any menus, change their targets
		// to the cell just vacated by the component we swapped with
		if (in_array($targetId, $director->getFilledTargetIds($targetOrgId))) {
			$menuIds = array_keys($director->getFilledTargetIds($targetOrgId), $targetId);
			foreach ($menuIds as $menuId) {
				$menuOrganizer =& $director->getSiteComponentById($menuId);
				printpre(get_class($menuOrganizer));
				
				$menuOrganizer->updateTargetId($oldCellId);
			}
		}
		
// 		printpre($testDocument->toNormalizedString(true));
		$filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
		$somecontent = $testDocument->toNormalizedString();
		
		// Let's make sure the file exists and is writable first.
		if (is_writable($filename)) {
		
			// In our example we're opening $filename in append mode.
			// The file pointer is at the bottom of the file hence
			// that's where $somecontent will go when we fwrite() it.
			if (!$handle = fopen($filename, 'w')) {
				echo "Cannot open file ($filename)";
				exit;
			}
			
			// Write $somecontent to our opened file.
			if (fwrite($handle, $somecontent) === FALSE) {
				echo "Cannot write to file ($filename)";
				exit;
			}
			
			fclose($handle);
			
			$harmoni =& Harmoni::instance();
			RequestContext::locationHeader($harmoni->request->quickURL(
				"site", "newEdit",
				array("node" => RequestContext::value('returnNode'))));	
			
		} else {
			printpre($testDocument->toNormalizedString(true));
			echo "The file $filename is not writable";
		}
	}
}

?>