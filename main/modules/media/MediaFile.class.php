<?php
/**
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaFile.class.php,v 1.9 2008/03/20 15:43:56 adamfranco Exp $
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
 * @version $Id: MediaFile.class.php,v 1.9 2008/03/20 15:43:56 adamfranco Exp $
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
	 * @static
	 */
	public static function withIdString ( $idString) {
		if (preg_match('/^(?:(?:repositoryId=(.+)&(?:amp;)?)|(?:&(?:amp;)?))?assetId=(.+)&(?:amp;)?recordId=([^&]+)/', $idString, $matches))
			return MediaFile::withIdStrings($matches[1], $matches[2], $matches[3]);
		else if (preg_match('/^(?:(?:repository_id=(.+)&(?:amp;)?)|(?:&(?:amp;)?))?asset_id=(.+)&(?:amp;)?record_id=([^&]+)/', $idString, $matches))
			return MediaFile::withIdStrings($matches[1], $matches[2], $matches[3]);
		else
			throw new InvalidArgumentException("Invalid Id format, '".$idString."'");
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
	public static function withIds ( $repositoryId, $assetId, $recordId  ) {
		ArgumentValidator::validate($repositoryId, ExtendsValidatorRule::getRule("Id"));
		ArgumentValidator::validate($assetId, ExtendsValidatorRule::getRule("Id"));
		ArgumentValidator::validate($recordId, ExtendsValidatorRule::getRule("Id"));
		
		$repositoryManager = Services::getService("Repository");
		$repository = $repositoryManager->getRepository($repositoryId);
		$asset = $repository->getAsset($assetId);
		
		$mediaFile = new MediaFile(
			MediaAsset::withAsset($asset),
			$asset->getRecord($recordId));
		return $mediaFile;
	}
	
	/**
	 * Answer a new MediaAsset that wraps the Asset identified with the passed Ids.
	 * 
	 * @param string $repositoryId May be null of an empty string.
	 * @param string $assetId
	 * @return object MediaAsset
	 * @access public
	 * @since 4/27/07
	 * @static
	 */
	public static function withIdStrings ( $repositoryId, $assetId, $recordId ) {
		ArgumentValidator::validate($repositoryId, OptionalRule::getRule(StringValidatorRule::getRule()));
		ArgumentValidator::validate($assetId, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($recordId, NonZeroLengthStringValidatorRule::getRule());
		
		$idManager = Services::getService("Id");
		
		if (!$repositoryId)
			$repositoryId = 'edu.middlebury.segue.sites_repository';
		
		$mediaFile = MediaFile::withIds(
			$idManager->getId($repositoryId),
			$idManager->getId($assetId),
			$idManager->getId($recordId));
		
		return $mediaFile;
	}
	
	/**
	 * Answer a new MediaFile Id with any ids found in the id map passed converted
	 * to their new equivalents
	 * 
	 * @param array $idMap An associative array of old id-strings to new id-strings.
	 * @param string $idString
	 * @return string The resulting id string
	 * @access public
	 * @since 1/24/08
	 * @static
	 */
	public static function getMappedIdString (array $idMap, $idString) {
		ArgumentValidator::validate($idString, NonZeroLengthStringValidatorRule::getRule());
		if (!preg_match('/^(?:(?:repositoryId=(.+)&(?:amp;)?)|(?:&(?:amp;)?))?assetId=(.+)&(?:amp;)?recordId=(.+)$/', $idString, $matches)) 
			throw new InvalidArgumentException("Invalid Id format, '$idString'");
		
// 		printpre($matches);
			
		if (isset($matches[1]) && $matches[1]) {
			if (isset($idMap[$matches[1]]))
				$id = "repositoryId=".$idMap[$matches[1]]."&";
			else
				$id = "repositoryId=".$matches[1]."&";
		} else
			$id = "";
		
		if (isset($idMap[$matches[2]]))
			$id .= "assetId=".$idMap[$matches[2]];
		else
			$id .= "assetId=".$matches[2];
		
		if (isset($idMap[$matches[3]]))
			$id .= "&recordId=".$idMap[$matches[3]];
		else
			$id .= "&recordId=".$matches[3];
		
		return $id;
	}
	
	/**
	 * Answer an IdString from a URL written by this class
	 * 
	 * @param string $url
	 * @return string An Id string
	 * @access public
	 * @since 3/18/08
	 * @static
	 */
	public static function getIdStringFromUrl ($url) {
		$harmoni = Harmoni::instance();
		$fileParams = $harmoni->request->getNamespaceParameterArrayFromUrl('polyphony-repository', $url);
		$pairs = array();
		foreach ($fileParams as $key => $val)
			$pairs[] = $key."=".urlencode($val);
		$idString = implode('&amp;', $pairs);
		
		// Try running this string through our methods to clean it up.
		try {
			$mediaFile = self::withIdString($idString);
			return $mediaFile->getIdString();
		} catch (InvalidArgumentException $e) {
			return $idString;
		} catch (UnknownIdException $e) {
			return $idString;
		}
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
		if ($this->_getRepositoryIdString() == 'edu.middlebury.segue.sites_repository')
			return "assetId=".$this->_getAssetIdString()
				."&recordId=".$this->_getRecordIdString();
		else
			return "repositoryId=".$this->_getRepositoryIdString()
				."&assetId=".$this->_getAssetIdString()
				."&recordId=".$this->_getRecordIdString();
	}
	
	/**
	 * Answer the size of the file as a ByteSize object
	 * 
	 * @return object ByteSize
	 * @access public
	 * @since 4/27/07
	 */
	function getSize () {
		$size = ByteSize::withValue($this->_getPartValue('FILE_SIZE'));
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
	 * Answer the mimetype string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getThumbnailMimeType () {
		return $this->_getPartValue('THUMBNAIL_MIME_TYPE');
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
		$harmoni = Harmoni::instance();
		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewfile", 
				array(
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString(),
					"file_name" => $this->getFilename()));
		$harmoni->request->endNamespace();
		
		return $url;
	}
	
	/**
	 * Answer the url to the file that can be used by flash objects with limited
	 * cookie-sending abilities.
	 * 
	 * @return string
	 * @access public
	 * @since 6/20/08
	 */
	public function getUrlForFlash () {
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace(null);
		$harmoni->request->set(session_name(), session_id());
		$harmoni->request->endNamespace();
		
		$harmoni->request->startNamespace('polyphony-repository');
		$url = $harmoni->request->mkURL("repository", "viewfile_flash", 
				array(
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString(),
					"file_name" => $this->getFilename()));
		$harmoni->request->endNamespace();
		
		$url = $url->write();
		
		$harmoni->request->startNamespace(null);
		$harmoni->request->forget(session_name());
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
		$harmoni = Harmoni::instance();
		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewthumbnail", 
				array(
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString()));
		$harmoni->request->endNamespace();
		
		return $url;
	}
	
	/**
	 * Answer the url to the thumbnail that can be used by flash objects with limited
	 * cookie-sending abilities.
	 * 
	 * @return string
	 * @access public
	 * @since 6/20/08
	 */
	public function getThumbnailUrlForFlash () {
		$harmoni = Harmoni::instance();
		
		$harmoni->request->startNamespace(null);
		$harmoni->request->set(session_name(), session_id());
		$harmoni->request->endNamespace();
		
		$harmoni->request->startNamespace('polyphony-repository');
		$url = $harmoni->request->mkURL("repository", "viewthumbnail_flash", 
				array(
					"repository_id" => $this->_getRepositoryIdString(),
					"asset_id" => $this->_getAssetIdString(),
					"record_id" => $this->_getRecordIdString()));
		$harmoni->request->endNamespace();
		
		$url = $url->write();
		
		$harmoni->request->startNamespace(null);
		$harmoni->request->forget(session_name());
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
	function MediaFile ( $mediaAsset, $record ) {
		$this->_record = $record;
		$this->_mediaAsset = $mediaAsset;
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
		$idManager = Services::getService("Id");
		$parts = $this->_record->getPartsByPartStructure(
					$idManager->getId($partIdString));
		while ($parts->hasNext()) {
			$part = $parts->next();
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
		$id = $this->_record->getId();
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
	function _getAsset () {
		return $this->_mediaAsset;
	}
}

?>