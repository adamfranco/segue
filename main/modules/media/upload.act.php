<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: upload.act.php,v 1.14 2008/02/15 16:46:19 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaAction.abstract.php");

/**
 * Handle the uploading of a new file to the media library
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: upload.act.php,v 1.14 2008/02/15 16:46:19 adamfranco Exp $
 */
class uploadAction
	extends MediaAction
{
	
	/**
	 * Check authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/27/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access the media library
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$contentAsset = $this->getContentAsset();
		
		return ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$contentAsset->getId()) || 
			$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$contentAsset->getId()));
	}
	
	/**
	 * Process the changes and build the output
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function buildContent () {		
		if (!isset($_FILES['media_file']))
			$this->error('No Form Submitted');
		
		if ($_FILES['media_file']['error'])
			$this->error('No file uploaded.');
			
		if (!$_FILES['media_file']['size'])
			$this->error('Uploaded file is empty');
		
		try {
			$newFileAsset = $this->createFileAsset();
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}		
		
		$this->start();
		print $this->getAssetXml($newFileAsset);
		$this->end();
	}
	
	/**
	 * Create a new file asset
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/26/07
	 */
	function createFileAsset () {
		$contentAsset = $this->getContentAsset();
		$asset = MediaAsset::createForContentAsset($contentAsset);
		
		if (!($displayName = RequestContext::value('displayName')))
			$displayName = $_FILES['media_file']['name'];
		
		if (!($description = RequestContext::value('description')))
			$description = '';
		
		// Create the asset
		$asset->updateDisplayName($displayName);
		$asset->updateDescription($description);
		
		try {
			$this->addFileRecord($asset);
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
			$this->nonFatalError($e->getMessage(), get_class($e));
		}
		
		try {
			$this->addDublinCoreRecord($asset);
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
			$this->nonFatalError($e->getMessage(), get_class($e));
		}
		
		// Log the success or failure
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Media Library", "File uploaded with id '".$asset->getId()->getIdString()."' and filename '".$_FILES['media_file']['name']."'");
			$item->addNodeId($asset->getId());
			$item->addNodeId($contentAsset->getId());
			
			$idManager = Services::getService("Id");
			$director = AssetSiteDirector::forAsset($contentAsset);
			$site = $director->getRootSiteComponent($contentAsset->getId()->getIdString());
			$item->addNodeId($idManager->getId($site->getId()));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		return $asset;
	}
}

?>