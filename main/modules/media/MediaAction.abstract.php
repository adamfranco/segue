<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.15 2008/04/11 21:50:56 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");
require_once(dirname(__FILE__)."/MediaAsset.class.php");


/**
 * This Abstract class provides access to media assets within a parent.
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.15 2008/04/11 21:50:56 adamfranco Exp $
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
	function __construct () {
		$this->mediaFileType = MediaAsset::getMediaFileType();
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$contentAsset = $this->getContentAsset();
		
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
	function getContentAsset () {
		if (!isset($this->_contentAsset)) {
			$idManager = Services::getService("Id");
			$repositoryManager = Services::getService("Repository");
			$repository = $repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository'));
			$this->_contentAsset = $repository->getAsset(
				$idManager->getId(RequestContext::value('assetId')));
		}
		
		return $this->_contentAsset;
	}
	
	/**
	 * Answer the slot for the site.
	 *
	 * @return object Slot
	 * @access protected
	 * @since 3/26/08
	 */
	protected function getSlot () {
		if (!isset($this->slot)) {			
			$slotMgr = SlotManager::instance();
			$this->slot = $slotMgr->getSlotBySiteId($this->getSiteAsset($this->getContentAsset())->getId());
		
		}
		
		return $this->slot;
	}
	
	/**
	 * Answer the Site asset for a content asset
	 * 
	 * @param object Asset $asset
	 * @return object Asset
	 * @access protected
	 * @since 2/26/07
	 */
	protected function getSiteAsset ( Asset $asset ) {
		$siteType = new Type ('segue', 'edu.middlebury', 'SiteNavBlock');
		if ($siteType->isEqual($asset->getAssetType())) {
			return $asset;
		} else {
			$parents = $asset->getParents();
			while ($parents->hasNext()) {
				$result = $this->getSiteAsset($parents->next());
				if ($result)
					return $result;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer all media assets below the specified asset
	 * 
	 * @param object Asset $asset
	 * @param optional object Id $excludeId
	 * @return object Iterator
	 * @access protected
	 * @since 2/26/07
	 */
	protected function getAllMediaAssets ( Asset $asset, $excludeId = null ) {
		if ($excludeId && $excludeId->isEqual($asset->getId())) {
			return false;
		}
		
		if ($this->mediaFileType->isEqual($asset->getAssetType())) {
			$tmp = array();
			$tmp[] = $asset;
			$iterator = new HarmoniIterator($tmp);
			return $iterator;
		} else {
			$iterator = new MultiIteratorIterator();
			$children = $asset->getAssets();
			while ($children->hasNext()) {
				$result = $this->getAllMediaAssets($children->next(), $excludeId);
				if ($result) {
					$iterator->addIterator($result);
				}
			}
			
			return $iterator;
		}
	}
	
	/**
	 * Answer elements for the quota of the current Site.
	 * 
	 * @return void
	 * @access protected
	 * @since 3/26/08
	 */
	protected function getQuota () {
		$slot = $this->getSlot();
		$quota = $slot->getMediaQuota();
		print "\n\t<quota slot='".$slot->getShortname()."' quota='".$quota->value()."'  quotaUsed='".$this->getQuotaUsed()."' />";
	}
	
	/**
	 * Answer the size of media used in the site
	 * 
	 * @return int
	 * @access protected
	 * @since 3/26/08
	 */
	protected function getQuotaUsed () {
		$total = 0;
		$idManager = Services::getService("Id");
		$mediaAssets = $this->getAllMediaAssets($this->getSiteAsset($this->getContentAsset()));
		while ($mediaAssets->hasNext()) {
			$mediaAsset = $mediaAssets->next();
			$fileRecords = $mediaAsset->getRecordsByRecordStructure($idManager->getId('FILE'));
			while ($fileRecords->hasNext()) {
				$fileRecord = $fileRecords->next();
				$parts = $fileRecord->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
				$part = $parts->next();
				$total = $total + intval($part->getValue());
			}
		}
		return $total;
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
	function getAssetXml ($asset) {
		try {
			$idManager = Services::getService("Id");
			$authZ = Services::getService("AuthZ");
			
			if (!$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$asset->getId()))
			{
				return '';
			}
			
			ob_start();
			
			$assetId = $asset->getId();
			$repository = $asset->getRepository();
			$repositoryId = $repository->getId();
			print "\n\t<asset id=\"".$assetId->getIdString()."\" repositoryId=\"".$repositoryId->getIdString()."\">";
			
			print "\n\t\t<displayName><![CDATA[";
			print HtmlString::getSafeHtml($asset->getDisplayName());		
			print "]]></displayName>";
			
			print "\n\t\t<description><![CDATA[";
			print HtmlString::getSafeHtml($asset->getDescription());
			print "]]></description>";
			
			print "\n\t\t<modificationDate><![CDATA[";
			$date = $asset->getModificationDate();
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
			$fileRecords = $asset->getRecordsByRecordStructure(
				$idManager->getId('FILE'));
			while ($fileRecords->hasNext()) {
				$fileRecord = $fileRecords->next();
				$fileRecordId = $fileRecord->getId();
				print "\n\t\t<file id=\"".$fileRecordId->getIdString()."\"";
				print " mimetype=\"".$fileRecord->getPartsByPartStructure($idManager->getId("MIME_TYPE"))->next()->getValue()."\"";
				print ">";
				
				$parts = $fileRecord->getPartsByPartStructure($idManager->getId("FILE_NAME"));
				$part = $parts->next();
				print "\n\t\t\t<name><![CDATA[".HtmlString::getSafeHtml($part->getValue())."]]></name>";
				
				$parts = $fileRecord->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
				$part = $parts->next();
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
			$records = $asset->getRecordsByRecordStructure(
				$idManager->getId('dc'));
			if ($records->hasNext()) {
				$record = $records->next();
				$recordId = $record->getId();
				print "\n\t\t<dublinCore id=\"".$recordId->getIdString()."\">";
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.title"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					print "\n\t\t\t<title><![CDATA[".HtmlString::getSafeHtml($valueObj->asString())."]]></title>";
				}
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.description"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					print "\n\t\t\t<description><![CDATA[".HtmlString::getSafeHtml($valueObj->asString())."]]></description>";
				}
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.creator"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					print "\n\t\t\t<creator><![CDATA[".HtmlString::getSafeHtml($valueObj->asString())."]]></creator>";
				}
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.source"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					print "\n\t\t\t<source><![CDATA[".HtmlString::getSafeHtml($valueObj->asString())."]]></source>";
				}
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.publisher"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					print "\n\t\t\t<publisher><![CDATA[".HtmlString::getSafeHtml($valueObj->asString())."]]></publisher>";
				}
				
				$parts = $record->getPartsByPartStructure($idManager->getId("dc.date"));
				if ($parts->hasNext()) {
					$part = $parts->next();
					$valueObj = $part->getValue();
					$date = $valueObj->asDate();
					print "\n\t\t\t<date><![CDATA[";
					print $date->asString();
					print "]]></date>";
				}
				
				print "\n\t\t</dublinCore>";
			}
			
			
			print "\n\t\t<permsHtml><![CDATA[";
			print AuthZPrinter::getAZIcon($asset->getId());		
			print "]]></permsHtml>";
			
			
			print "\n\t</asset>";
			
			return ob_get_clean();
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
			$this->error($e->getMessage());
		}
	}
	
	/**
	 * Add a file record to an asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function addFileRecord ( $asset ) {
		$idManager = Services::getService("Id");
		
		$record = $asset->createRecord($idManager->getId("FILE"));
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
	function addDublinCoreRecord ( $asset ) {
		$idManager = Services::getService("Id");
		
		$record = $asset->createRecord($idManager->getId("dc"));
		
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
	function updateFileRecord ( $asset, $record, $fieldName = 'media_file') {
		$idManager = Services::getService("Id");
		
		$name = strip_tags($_FILES[$fieldName]['name']);
		$tmpName = $_FILES[$fieldName]['tmp_name'];			
		$mimeType = $_FILES[$fieldName]['type'];
		// If we weren't passed a mime type or were passed the generic
		// application/octet-stream type, see if we can figure out the
		// type.
		if (!$mimeType || $mimeType == 'application/octet-stream') {
			$mime = Services::getService("MIME");
			$mimeType = $mime->getMimeTypeForFileName($name);
		}
		
		
		// Check the quota
		$parts = $record->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
		$part = $parts->next();
		$currentSize = $part->getValue();
		
		$slot = $this->getSlot();
		if ($this->getQuotaUsed() + $_FILES[$fieldName]['size'] - $currentSize >  $slot->getMediaQuota()->value())
			throw new Exception("Cannot add File, $name, quota of ".$slot->getMediaQuota()->asString()." exceeded.");
			
		
		$parts = $record->getPartsByPartStructure($idManager->getId("FILE_DATA"));
		$part = $parts->next();
		$part->updateValue(file_get_contents($tmpName));
		
		$parts = $record->getPartsByPartStructure($idManager->getId("FILE_NAME"));
		$part = $parts->next();
		$part->updateValue($name);
		
		$parts = $record->getPartsByPartStructure($idManager->getId("MIME_TYPE"));
		$part = $parts->next();
		$part->updateValue($mimeType);
		
		
		/*********************************************************
		 * Thumbnail Generation
		 *********************************************************/
		$imageProcessor = Services::getService("ImageProcessor");
					
		// If our image format is supported by the image processor,
		// generate a thumbnail.
		if ($imageProcessor->isFormatSupported($mimeType)) {
			if ($tmpName) {
				$sourceData = file_get_contents($tmpName);
			} else {
				// Download the file data and temporarily store it in the results
				if (!isset($results['file_url']))
					$sourceData = file_get_contents($results['file_url']);
				
				$parts = $record->getPartsByPartStructure($idManager->getId("FILE_SIZE"));
				$part = $parts->next();
				$part->updateValue(strval(strlen($sourceData)));
			}
		
			$thumbnailData = $imageProcessor->generateThumbnailData($mimeType, $sourceData);
		}
		
		$parts = $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_DATA"));
		$thumbDataPart = $parts->next();
		$parts = $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_MIME_TYPE"));
		$thumbMimeTypePart = $parts->next();
		
		if (isset($thumbnailData) && $thumbnailData) {
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
	function updateDublinCoreRecord ( $asset, $record ) {
		$idManager = Services::getService("Id");
		
		$value = String::fromString(HtmlString::getSafeHtml($asset->getDisplayName()));
		$id = $idManager->getId("dc.title");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(HtmlString::getSafeHtml($asset->getDescription()));
		$id = $idManager->getId("dc.description");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(HtmlString::getSafeHtml(RequestContext::value('creator')));
		$id = $idManager->getId("dc.creator");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(HtmlString::getSafeHtml(RequestContext::value('source')));
		$id = $idManager->getId("dc.source");
		$this->updateSingleValuedPart($record, $id, $value);
		
		$value = String::fromString(HtmlString::getSafeHtml(RequestContext::value('publisher')));
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
	function updateSingleValuedPart ( $record, $partStructureId, $value ) {
		if (is_object($value) && $value->asString()) {
			$parts = $record->getPartsByPartStructure($partStructureId);
			if ($parts->hasNext()) {
				$part = $parts->next();
				$part->updateValue($value);
			} else {
				$record->createPart($partStructureId, $value);
			}
		}
		
		// Remove existing parts
		else {
			$parts = $record->getPartsByPartStructure($partStructureId);
			while ($parts->hasNext()) {
				$part = $parts->next();
				$record->deletePart($part->getId());
			}
		}
	}
}

?>