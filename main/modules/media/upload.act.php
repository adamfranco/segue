<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: upload.act.php,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
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
 * @version $Id: upload.act.php,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
 */
class uploadAction
	extends MediaAction
{
		
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
			$this->error('An error has occured, no file uploaded.');
			
		if (!$_FILES['media_file']['size'])
			$this->error('Uploaded file is empty');
		
		ob_start();
		$newFileAsset =& $this->createFileAsset();
		if ($error = ob_get_clean())
			$this->error($error);
		
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
	function &createFileAsset () {
		$contentAsset =& $this->getContentAsset();
		$repository =& $contentAsset->getRepository();
		
		if (!($displayName = RequestContext::value('displayName')))
			$displayName = $_FILES['media_file']['name'];
		
		if (!($description = RequestContext::value('description')))
			$description = '';
		
		// Create the asset
		$asset =& $repository->createAsset(
					$displayName,
					$description,
					$this->mediaFileType);
		
		$contentAsset->addAsset($asset->getId());
		
		$this->addFileRecord($asset);
		
		$this->addDublinCoreRecord($asset);
		
		return $asset;
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
	 * Update a file record from the file in the $_FILES array;
	 * 
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function updateFileRecord ( &$asset, &$record ) {
		$idManager =& Services::getService("Id");
		
		$name = $_FILES['media_file']['name'];
		$tmpName = $_FILES['media_file']['tmp_name'];			
		$mimeType = $_FILES['media_file']['type'];
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