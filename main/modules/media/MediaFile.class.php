<?php
/**
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaFile.class.php,v 1.2 2007/04/30 16:29:27 adamfranco Exp $
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
 * @version $Id: MediaFile.class.php,v 1.2 2007/04/30 16:29:27 adamfranco Exp $
 */
class MediaFile {
		
	/*********************************************************
	 * Static instance creation methods
	 *********************************************************/
	
	/**
	 * Answer a new MediaFile from a MediaFile id string
	 * 
	 * @param string $idString
	 * @return object MediaFile
	 * @access public
	 * @since 4/30/07
	 */
	function &withIdString ( $idString) {
		if (preg_match('/^repositoryId=(.+)&assetId=(.+)&recordId=(.+)$/', 
			$idString, $matches)) 
		{
			$obj =& MediaFile::withIdStrings($matches[1], $matches[2], $matches[3]);
			return $obj;
		} else {
			$null = null;
			return $null;
		}
	}
	
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
	 * Answer the string Id for this file
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getIdString () {
		return "repositoryId=".$this->_getRepositoryIdString()
			."&assetId=".$this->_getIdString()
			."&recordId=".$this->_getRecordIdString();
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
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString()));
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
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString()));
		$harmoni->request->endNamespace();
		
		return $url;
	}
	
	/*********************************************************
	 * Public Instance Methods: Dublin Core
	 *********************************************************/
	
	/**
	 * Answer the first DublinCore Title
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getTitle () {
		return $this->_mediaAsset->getTitle();
	}
	/**
	 * Answer an array of DublinCore Title strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getTitles () {
		return $this->_mediaAsset->getTitles();
	}
	
	/**
	 * Answer the first DublinCore Creator
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getCreator () {
		return $this->_mediaAsset->getCreator();
	}
	/**
	 * Answer an array of DublinCore Creator strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getCreators () {
		return $this->_mediaAsset->getCreators();
	}
	
	/**
	 * Answer the first DublinCore Source
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getSource () {
		return $this->_mediaAsset->getSource();
	}
	/**
	 * Answer an array of DublinCore Source strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getSources () {
		return $this->_mediaAsset->getSources();
	}
	
	/**
	 * Answer the first DublinCore Publisher
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getPublisher () {
		return $this->_mediaAsset->getPublisher();
	}
	/**
	 * Answer an array of DublinCore Publisher strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getPublishers () {
		return $this->_mediaAsset->getPublishers();
	}
	
	/**
	 * Answer the first DublinCore Date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 4/27/07
	 */
	function getDate () {
		return $this->_mediaAsset->getDate();
	}
	/**
	 * Answer an array of DublinCore Date objects
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getDates () {
		return $this->_mediaAsset->getDates();
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
	
	/**
	 * Answer the record Id string
	 * 
	 * @return string
	 * @access private
	 * @since 4/30/07
	 */
	function _getRecordIdString () {
		$id =& $this->_record->getId();
		return $id->getIdString();
	}
	
	/**
	 * Answer the asset id string
	 * 
	 * @return string
	 * @access private
	 * @since 4/27/07
	 */
	function _getAssetIdString () {
		return $this->_mediaAsset->getIdString();
	}
	
	/**
	 * Answer the Repository id string
	 * 
	 * @return string
	 * @access private
	 * @since 4/27/07
	 */
	function _getRepositoryIdString () {
		return $this->_mediaAsset->getRepositoryIdString();
	}
	
	/*********************************************************
	 * Protected Instance methods. To be used only internally 
	 * within this package.
	 *********************************************************/
	/**
	 * Answer the MediaAsset for this MediaFile
	 * 
	 * @return object MediaAsset
	 * @access protected
	 * @since 4/27/07
	 */
	function &_getAsset () {
		return $this->_mediaAsset;
	}
}

?>