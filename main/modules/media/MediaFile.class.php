<?php
/**
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaFile.class.php,v 1.1 2007/04/27 20:20:19 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaAsset.class.php");

/**
 * The MediaFile is a wrapper for a FILE Record that goes along with the MediaAsset.
 * The MediaFile provides a simplified way to access file properties.
 * 
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaFile.class.php,v 1.1 2007/04/27 20:20:19 adamfranco Exp $
 */
class MediaFile {
		
	/*********************************************************
	 * Static instance creation methods
	 *********************************************************/
	
	/**
	 * Answer a new MediaAsset that wraps the Asset identified with the passed Ids.
	 * 
	 * @param object Id $repositoryId
	 * @param object Id $assetId
	 * @return object MediaAsset
	 * @access public
	 * @since 4/27/07
	 * @static
	 */
	function &withIds ( &$repositoryId, &$assetId, &$recordId  ) {
		ArgumentValidator::validate($repositoryId, ExtendsValidatorRule::getRule("Id"));
		ArgumentValidator::validate($assetId, ExtendsValidatorRule::getRule("Id"));
		ArgumentValidator::validate($recordId, ExtendsValidatorRule::getRule("Id"));
		
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository($repositoryId);
		$asset =& $repository->getAsset($assetId);
		
		$mediaFile =& new MediaFile(
			MediaAsset::withAsset($asset),
			$asset->getRecord($recordId));
		return $mediaFile;
	}
	
	/**
	 * Answer a new MediaAsset that wraps the Asset identified with the passed Ids.
	 * 
	 * @param string $repositoryId
	 * @param string $assetId
	 * @return object MediaAsset
	 * @access public
	 * @since 4/27/07
	 * @static
	 */
	function &withIdStrings ( $repositoryId, $assetId, $recordId ) {
		ArgumentValidator::validate($repositoryId, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($assetId, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($recordId, NonZeroLengthStringValidatorRule::getRule());
		
		$idManager =& Services::getService("Id");
		
		$mediaFile =& MediaFile::withIds(
			$idManager->getId($repositoryId),
			$idManager->getId($assetId),
			$idManager->getId($recordId));
		
		return $mediaFile;
	}
	
	/*********************************************************
	 * Public Instance Methods
	 *********************************************************/
	
	/**
	 * Answer the MediaAsset for this MediaFile
	 * 
	 * @return object MediaAsset
	 * @access public
	 * @since 4/27/07
	 */
	function &getAsset () {
		return $this->_mediaAsset;
	}
	
	/**
	 * Answer the Id object for this file
	 * 
	 * @return object Id
	 * @access public
	 * @since 4/27/07
	 */
	function &getId () {
		return $this->_record->getId();
	}
	
	/**
	 * Answer the string Id for this file
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getIdString () {
		$id =& $this->getId();
		return $id->getIdString();
	}
	
	/**
	 * Answer the asset id string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getAssetIdString () {
		return $this->_mediaAsset->getIdString();
	}
	
	/**
	 * Answer the Repository id string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getRepositoryIdString () {
		return $this->_mediaAsset->getRepositoryIdString();
	}
	
	/**
	 * Answer the size of the file as a ByteSize object
	 * 
	 * @return object ByteSize
	 * @access public
	 * @since 4/27/07
	 */
	function &getSize () {
		$size =& ByteSize::withValue($this->_getPartValue('FILE_SIZE'));
		return $size;
	}
	
	/**
	 * Answer the file name string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getFilename () {
		return $this->_getPartValue('FILE_NAME');
	}
	
	/**
	 * Answer the mimetype string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getMimeType () {
		return $this->_getPartValue('MIME_TYPE');
	}
	
	/**
	 * Answer the contents of the file as a string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getFileContents () {
		return $this->_getPartValue('FILE_DATA');
	}
	
	/**
	 * Answer the url to the file
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getUrl () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewfile", 
				array(
					"repository_id" => $this->getRepositoryIdString(),
					"asset_id" => $this->getAssetIdString(),
					"record_id" => $this->getIdString()));
		$harmoni->request->endNamespace();
		
		return $url;
	}
	
	/**
	 * Answer the thumbnail url
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getThumbnailUrl () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewthumbnail", 
				array(
					"repository_id" => $this->getRepositoryIdString(),
					"asset_id" => $this->getAssetIdString(),
					"record_id" => $this->getIdString()));
		$harmoni->request->endNamespace();
		
		return $url;
	}
	
	/*********************************************************
	 * Private Instance Methods
	 *********************************************************/
	
	/**
	 * Constructor
	 * 
	 * @param object MediaAsset $mediaAsset
	 * @param object Record $record
	 * @return void
	 * @access public
	 * @since 4/27/07
	 */
	function MediaFile ( &$mediaAsset, &$record ) {
		$this->_record =& $record;
		$this->_mediaAsset =& $mediaAsset;
	}
	
	/**
	 * Answer the string value of a part
	 * 
	 * @param string $partIdString
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function _getPartValue ($partIdString) {
		$idManager =& Services::getService("Id");
		$parts =& $this->_record->getPartsByPartStructure(
					$idManager->getId($partIdString));
		while ($parts->hasNext()) {
			$part =& $parts->next();
			return $part->getValue();
		}
		
		return '';
	}
}

?>