<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.6 2007/04/27 15:13:31 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");


/**
 * This Abstract class provides access to media assets within a parent.
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.6 2007/04/27 15:13:31 adamfranco Exp $
 */
class MediaAction
	extends XmlAction
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/29/07
	 */
	function MediaAction () {
		$this->mediaFileType =& new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		if (method_exists($this, 'XmlAction'))
			$this->XmlAction();	
	}

	/**
	 * Check authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access the media library
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$contentAsset =& $this->getContentAsset();
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$contentAsset->getId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Node's</em> media.");
	}
		
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->error($this->getUnauthorizedMessage());
		
		$this->buildContent();
		
		$this->start();
		$this->end();
	}
	
	/**
	 * Answer the asset that the media library belongs to.
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/26/07
	 */
	function &getContentAsset () {
		if (!isset($this->_contentAsset)) {
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository'));
			$this->_contentAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('assetId')));
		}
		
		return $this->_contentAsset;
	}
	
	/**
	 * Answer out an XML block representing the given asset, its Dublin Core, and
	 * its file records
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function getAssetXml (&$asset) {
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$asset->getId()))
		{
			return '';
		}
		
		ob_start();
		
		$assetId =& $asset->getId();
		$repository =& $asset->getRepository();
		$repositoryId =& $repository->getId();
		print "\n\t<asset id=\"".$assetId->getIdString()."\" repositoryId=\"".$repositoryId->getIdString()."\">";
		
		print "\n\t\t<displayName><![CDATA[";
		print $asset->getDisplayName();		
		print "]]></displayName>";
		
		print "\n\t\t<description><![CDATA[";
		print $asset->getDescription();
		print "]]></description>";
		
		print "\n\t\t<modificationDate><![CDATA[";
		$date =& $asset->getModificationDate();
		print $date->asString();
		print "]]></modificationDate>";
		
		print "\n\t\t<authorization function='edu.middlebury.authorization.view' />";
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$asset->getId()))
		{
			print "\n\t\t<authorization function='edu.middlebury.authorization.modify' />";
		}
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"),
			$asset->getId()))
		{
			print "\n\t\t<authorization function='edu.middlebury.authorization.delete' />";
		}
		
		/*********************************************************
		 * Files
		 *********************************************************/
 		$fileRecords =& $asset->getRecordsByRecordStructure(
 			$idManager->getId('FILE'));
 		while ($fileRecords->hasNext()) {
 			$fileRecord =& $fileRecords->next();
 			$fileRecordId =& $fileRecord->getId();
			print "\n\t\t<file id=\"".$fileRecordId->getIdString()."\">";
			
			$parts =& $fileRecord->getPartsByPartStructure($idManager->getId("FILE_NAME"));
			$part =& $parts->next();
			print "\n\t\t\t<name><![CDATA[".$part->getValue()."]]></name>";
			
			$parts =& $fileRecord->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
			$part =& $parts->next();
			print "\n\t\t\t<size>".$part->getValue()."</size>";
			
			print "\n\t\t\t<url><![CDATA[";
			print RepositoryInputOutputModuleManager::getFileUrlForRecord(
					$asset, $fileRecord);
			print "]]></url>";
			
			print "\n\t\t\t<thumbnailUrl><![CDATA[";
			print RepositoryInputOutputModuleManager::getThumbnailUrlForRecord(
					$asset, $fileRecord);
			print "]]></thumbnailUrl>";
			
			print "\n\t\t</file>";
		}
		
		/*********************************************************
		 * Dublin Core
		 *********************************************************/
		$records =& $asset->getRecordsByRecordStructure(
 			$idManager->getId('dc'));
 		if ($records->hasNext()) {
	 		$record =& $records->next();
	 		$recordId =& $record->getId();
			print "\n\t\t<dublinCore id=\"".$recordId->getIdString()."\">";
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.title"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				print "\n\t\t\t<title><![CDATA[".$valueObj->asString()."]]></title>";
			}
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.description"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				print "\n\t\t\t<description><![CDATA[".$valueObj->asString()."]]></description>";
			}
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.creator"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				print "\n\t\t\t<creator><![CDATA[".$valueObj->asString()."]]></creator>";
			}
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.source"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				print "\n\t\t\t<source><![CDATA[".$valueObj->asString()."]]></source>";
			}
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.publisher"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				print "\n\t\t\t<publisher><![CDATA[".$valueObj->asString()."]]></publisher>";
			}
			
			$parts =& $record->getPartsByPartStructure($idManager->getId("dc.date"));
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$valueObj =& $part->getValue();
				$date =& $valueObj->asDate();
				print "\n\t\t\t<date><![CDATA[";
				print $date->asString();
				print "]]></date>";
			}
			
			print "\n\t\t</dublinCore>";
	 	}
 		
 		
		print "\n\t</asset>";
		
		return ob_get_clean();
	}
	
	/**
	 * Add a file record to an asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function addFileRecord ( &$asset ) {
		$idManager =& Services::getService("Id");
		
		$record =& $asset->createRecord($idManager->getId("FILE"));
		$this->updateFileRecord($asset, $record);
	}
	
	/**
	 * Add a Dublin Core Record to an asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function addDublinCoreRecord ( &$asset ) {
		$idManager =& Services::getService("Id");
		
		$record =& $asset->createRecord($idManager->getId("dc"));
		
		$this->updateDublinCoreRecord($asset, $record);
	}
	
	/**
	 * Update a file record from the file in the $_FILES array;
	 * 
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function updateFileRecord ( &$asset, &$record, $fieldName = 'media_file') {
		$idManager =& Services::getService("Id");
		
		$name = $_FILES[$fieldName]['name'];
		$tmpName = $_FILES[$fieldName]['tmp_name'];			
		$mimeType = $_FILES[$fieldName]['type'];
		// If we weren't passed a mime type or were passed the generic
		// application/octet-stream type, see if we can figure out the
		// type.
		if (!$mimeType || $mimeType == 'application/octet-stream') {
			$mime =& Services::getService("MIME");
			$mimeType = $mime->getMimeTypeForFileName($name);
		}
		
		$parts =& $record->getPartsByPartStructure($idManager->getId("FILE_DATA"));
		$part =& $parts->next();
		$part->updateValue(file_get_contents($tmpName));
		
		$parts =& $record->getPartsByPartStructure($idManager->getId("FILE_NAME"));
		$part =& $parts->next();
		$part->updateValue($name);
		
		$parts =& $record->getPartsByPartStructure($idManager->getId("MIME_TYPE"));
		$part =& $parts->next();
		$part->updateValue($mimeType);
		
		
		/*********************************************************
		 * Thumbnail Generation
		 *********************************************************/
		$imageProcessor =& Services::getService("ImageProcessor");
					
		// If our image format is supported by the image processor,
		// generate a thumbnail.
		if ($imageProcessor->isFormatSupported($mimeType)) {
			if ($tmpName) {
				$sourceData = file_get_contents($tmpName);
			} else {
				// Download the file data and temporarily store it in the results
				if (!isset($results['file_url']))
					$sourceData = file_get_contents($results['file_url']);
				
				$parts =& $record->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
				$part =& $parts->next();
				$part->updateValue(strval(strlen($sourceData)));
			}
		
			$thumbnailData = $imageProcessor->generateThumbnailData($mimeType, $sourceData);
		}
		
		$parts =& $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_DATA"));
		$thumbDataPart =& $parts->next();
		$parts =& $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_MIME_TYPE"));
		$thumbMimeTypePart =& $parts->next();
		
		if ($thumbnailData) {
			$thumbDataPart->updateValue($thumbnailData);
			$thumbMimeTypePart->updateValue($imageProcessor->getThumbnailFormat());
		}
		// just make our thumbnail results empty. Default icons will display
		// instead.
		else {
			$thumbDataPart->updateValue("");
			$thumbMimeTypePart->updateValue("NULL");
		}
	}
	
	/**
	 * Update the dublin core record with info from the form.
	 * 
	 * @param object Asset $asset
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function updateDublinCoreRecord ( &$asset, &$record ) {
		$idManager =& Services::getService("Id");
		
		$value = String::fromString($asset->getDisplayName());
		$id = $idManager->getId("dc.title");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString($asset->getDescription());
		$id = $idManager->getId("dc.description");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(RequestContext::value('creator'));
		$id = $idManager->getId("dc.creator");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(RequestContext::value('source'));
		$id = $idManager->getId("dc.source");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(RequestContext::value('publisher'));
		$id = $idManager->getId("dc.publisher");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = DateAndTime::fromString(RequestContext::value('date'));
		$id = $idManager->getId("dc.date");
		$this->updateSingleValuedPart($record, $id, $value);
	}
	
	/**
	 * Update a single-valued part
	 * 
	 * @param object Record $record
	 * @param object Id $partStructureId
	 * @param object $value
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function updateSingleValuedPart ( &$record, &$partStructureId, &$value ) {
		if (is_object($value) && $value->asString()) {
			$parts =& $record->getPartsByPartStructure($partStructureId);
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$part->updateValue($value);
			} else {
				$record->createPart($partStructureId, $value);
			}
		}
		
		// Remove existing parts
		else {
			$parts =& $record->getPartsByPartStructure($partStructureId);
			while ($parts->hasNext()) {
				$part =& $parts->next();
				$record->deletePart($part->getId());
			}
		}
	}
}

?>