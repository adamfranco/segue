<?php
/**
 * @since 2/14/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update.act.php,v 1.8 2008/03/26 18:23:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaAction.abstract.php");

/**
 * Update an existing File Asset with new values
 * 
 * @since 2/14/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update.act.php,v 1.8 2008/03/26 18:23:18 adamfranco Exp $
 */
class updateAction
	extends MediaAction
{
	
	/**
	 * Answer the authorization function used for this action
	 * 
	 * @return object Id
	 * @access protected
	 * @since 10/25/07
	 */
	protected function getAuthorizationFunction () {
		$idManager = Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.modify");
	}
	
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
		
		$fileAsset = $this->getFileAsset();
		
		return $authZ->isUserAuthorized(
			$this->getAuthorizationFunction(),
			$fileAsset->getId());
	}
		
	/**
	 * Process the changes and build the output
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	public function buildContent () {
		try {
			ob_start();
			$idManager = Services::getService("Id");
			$fileAsset = $this->getFileAsset();
			
			// Update the files
			$oldFilename = null;
			$newFilename = null;
			foreach (array_keys($_FILES) as $fieldName) {
				if (preg_match('/^file___(.+)$/', $fieldName, $matches)) {
					$fileRecord = $fileAsset->getRecord($idManager->getId($matches[1]));
					$filenameParts = $fileRecord->getPartsByPartStructure($idManager->getId("FILE_NAME"));
					$oldFilename = $filenameParts->next()->getValue();
					$newFilename = $_FILES[$fieldName]['name'];
					$this->updateFileRecord($fileAsset, $fileRecord, $fieldName);
				} else if ($fieldName == 'media_file') {
					$oldFilename = null;
					$newFilename = $_FILES[$fieldName]['name'];
					$this->addFileRecord($fileAsset);
				}
			}
			
			// Update the displayname
			// 
			// If the displayname in the form is the old filename, update it to the
			// new filename
			if (RequestContext::value('displayName') 
				&& RequestContext::value('displayName') == $oldFilename
				&& $newFilename)
			{
				$fileAsset->updateDisplayName($newFilename);
			}
			// Otherwise use the new value in the form if one exists
			else if (RequestContext::value('displayName') 
				&& RequestContext::value('displayName') != $fileAsset->getDisplayName())
			{
				$fileAsset->updateDisplayName(HtmlString::getSafeHtml(RequestContext::value('displayName')));
			}
			
			// Update the description if needed.
			if (!RequestContext::value('description')) {
				$fileAsset->updateDescription('');
			} else if (RequestContext::value('description') != $fileAsset->getDescription()) {
				$fileAsset->updateDescription(HtmlString::getSafeHtml(RequestContext::value('description')));
			}
			
			// Update the other metadata.
			$dublinCoreRecords = $fileAsset->getRecordsByRecordStructure(
				$idManager->getId('dc'));
			
			if ($dublinCoreRecords->hasNext())
				$this->updateDublinCoreRecord($fileAsset, $dublinCoreRecords->next());
			else
				$this->addDublinCoreRecord($fileAsset);
			
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log = $loggingManager->getLogForWriting("Segue");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$message = "File updated with id '".$fileAsset->getId()->getIdString()."'";
				if (isset($newFilename))
					$message .= " and new filename '".$newFilename."'";
				$item = new AgentNodeEntryItem("Media Library", $message);
				$item->addNodeId($fileAsset->getId());
				$item->addNodeId($this->getContentAsset()->getId());
				
				$contentAsset = $this->getContentAsset();
				$idManager = Services::getService("Id");
				$director = AssetSiteDirector::forAsset($contentAsset);
				$site = $director->getRootSiteComponent($contentAsset->getId()->getIdString());
				$item->addNodeId($idManager->getId($site->getId()));
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
	// 		printpre($_FILES);
			if ($error = ob_get_clean())
				$this->error($error);
			
			$this->start();
	// 		print "\n<![CDATA[";
	// 		print_r($_REQUEST);
	// 		print_r($_FILES);
	// 		print "\n]]>";
			
			print $this->getAssetXml($fileAsset);
			print $this->getQuota();
			$this->end();
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
	}
	
	/**
	 * Answer the file asset
	 * 
	 * @return object Asset
	 * @access protected
	 * @since 2/14/07
	 */
	protected function getFileAsset () {
		if (!isset($this->_fileAsset)) {
			$contentAsset = $this->getContentAsset();
			$repository = $contentAsset->getRepository();
			$idManager = Services::getService("Id");
			
			$this->_fileAsset = $repository->getAsset(
				$idManager->getId(RequestContext::value('mediaAssetId')));
		}
		
		return $this->_fileAsset;
	}
	
}

?>