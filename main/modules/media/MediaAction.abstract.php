<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.3 2007/02/13 22:12:59 adamfranco Exp $
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
 * @version $Id: MediaAction.abstract.php,v 1.3 2007/02/13 22:12:59 adamfranco Exp $
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
		ob_start();
		
		$assetId =& $asset->getId();
		print "\n\t<asset id=\"".$assetId->getIdString()."\">";
		
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
}

?>