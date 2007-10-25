<?php
/**
 * @since 2/14/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update.act.php,v 1.4 2007/10/25 14:06:50 adamfranco Exp $
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
 * @version $Id: update.act.php,v 1.4 2007/10/25 14:06:50 adamfranco Exp $
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
		ob_start();
		$idManager = Services::getService("Id");
		$fileAsset = $this->getFileAsset();
		
		if (!($displayName = RequestContext::value('displayName')))
			$displayName = $_FILES['media_file']['name'];
		
		if (!($description = RequestContext::value('description')))
			$description = '';
		
		$fileAsset->updateDisplayName($displayName);
		$fileAsset->updateDescription($description);
		
		$dublinCoreRecords = $fileAsset->getRecordsByRecordStructure(
			$idManager->getId('dc'));
		
		if ($dublinCoreRecords->hasNext())
			$this->updateDublinCoreRecord($fileAsset, $dublinCoreRecords->next());
		else
			$this->addDublinCoreRecord($fileAsset);
		
		foreach (array_keys($_FILES) as $fieldName) {
			if (preg_match('/^file___(.+)$/', $fieldName, $matches)) {
				$fileRecord = $fileAsset->getRecord($idManager->getId($matches[1]));
				$this->updateFileRecord($fileAsset, $fileRecord, $fieldName);
			} else if ($fieldName == 'media_file') {
				$this->addFileRecord($fileAsset);
			}
		}
			
		if ($error = ob_get_clean())
			$this->error($error);
		
		$this->start();
		
// 		print "\n<![CDATA[";
// 		print_r($_REQUEST);
// 		print_r($_FILES);
// 		print "\n]]>";
		
		print $this->getAssetXml($fileAsset);
		$this->end();
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