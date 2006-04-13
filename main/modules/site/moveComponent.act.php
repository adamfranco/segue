<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.2 2006/04/13 17:16:30 adamfranco Exp $
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
 * @version $Id: moveComponent.act.php,v 1.2 2006/04/13 17:16:30 adamfranco Exp $
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

		$xmlDirector =& new XmlSiteDirector($testDocument);
		
		// Get the target organizer's Id & Cell
		preg_match("/^(.+)_cell:(.+)$/", RequestContext::value('destination'), $matches);
		$targetOrgId = $matches[1];
		$targetCell = $matches[2];
		
		$component =& $xmlDirector->getSiteComponentById(RequestContext::value('component'));
		$newOrganizer =& $xmlDirector->getSiteComponentById($targetOrgId);
		$newOrganizer->putSubcomponentInCell($component, $targetCell);
		
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
			echo "The file $filename is not writable";
		}
	}
}

?>