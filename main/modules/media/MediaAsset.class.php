<?php
/**
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAsset.class.php,v 1.6 2008/01/24 17:07:15 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaFile.class.php");

/**
 * The MediaAsset is a wrapper for an asset that represents a media file in Segue.
 * MediaAssets have a single DublinCore record the parts of which are
 * accessed through direct accessor methods rather than the OSID Asset/Record methods.
 * A MediaAsset may of zero or more MediaFiles.
 *
 * A MediaAsset object is read-only. Access the its asset directly for write actions.
 * 
 * @since 4/27/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAsset.class.php,v 1.6 2008/01/24 17:07:15 adamfranco Exp $
 */
class MediaAsset {
		
	/*********************************************************
	 * Static instance creation methods
	 *********************************************************/
	
	/**
	 * Answer a new MediaAsset that wraps the Asset passed
	 * 
	 * @param object Asset $asset
	 * @return object MediaAsset
	 * @access public
	 * @since 4/27/07
	 * @static
	 */
	public static function withAsset ( $asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		
		$mediaAsset = new MediaAsset($asset);
		return $mediaAsset;
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
	public static function withIds ( $repositoryId, $assetId ) {
		ArgumentValidator::validate($repositoryId, ExtendsValidatorRule::getRule("Id"));
		ArgumentValidator::validate($assetId, ExtendsValidatorRule::getRule("Id"));
		
		$repositoryManager = Services::getService("Repository");
		$repository = $repositoryManager->getRepository($repositoryId);
		
		$mediaAsset = new MediaAsset($repository->getAsset($assetId));
		return $mediaAsset;
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
	public static function withIdStrings ( $repositoryId, $assetId ) {
		ArgumentValidator::validate($repositoryId, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($assetId, NonZeroLengthStringValidatorRule::getRule());
		
		$idManager = Services::getService("Id");
		
		$mediaAsset = MediaAsset::withIds(
			$idManager->getId($repositoryId),
			$idManager->getId($assetId));
		
		return $mediaAsset;
	}
	
	/**
	 * Create a new Asset attached to a content asset. To access this asset as
	 * a mediaAsset, use 
	 * 		MediaAsset::withAsset(MediaAsset::createForContentAsset($contentAsset));
	 * 
	 * @param object Asset $contentAsset
	 * @return object Asset
	 * @access public
	 * @since 1/24/08
	 * @static
	 */
	public static function createForContentAsset (Asset $contentAsset) {
		$repository = $contentAsset->getRepository();
		
		// Create the asset
		$asset = $repository->createAsset(
					"Untitled",
					'',
					self::getMediaFileType());
		
		$contentAsset->addAsset($asset->getId());
		
		return $asset;
	}
	
	/**
	 * Answer the asset type for media assets.
	 *
	 * @return object Type
	 * @access public
	 * @since 1/24/08
	 * @static
	 */
	public static function getMediaFileType () {
		return new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
	}
	
	/*********************************************************
	 * Public Instance Methods
	 *********************************************************/
	
	/**
	 * Answer the display name
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getDisplayName () {
		return HtmlString::getSafeHtml($this->_asset->getDisplayName());
	}
	
	/**
	 * Answer the getDescription
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getDescription () {
		return HtmlString::getSafeHtml($this->_asset->getDescription());
	}
	
	/**
	 * Answer the Id
	 * 
	 * @return object Id
	 * @access public
	 * @since 4/27/07
	 */
	function getId () {
		return $this->_asset->getId();
	}
	
	/**
	 * Answer the Id string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getIdString () {
		$id = $this->_asset->getId();
		return $id->getIdString();
	}
	
	/**
	 * Answer the Repository id string
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getRepositoryIdString () {
		$repository = $this->_asset->getRepository();
		$repositoryId = $repository->getId();
		return $repositoryId->getIdString();
	}
	
	/**
	 * Answer the modification date object
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 4/27/07
	 */
	function getModificationDate () {
		return $this->_asset->getModificationDate();
	}
	
	/**
	 * Answer a MediaFile in this Asset
	 * 
	 * @param object Id $id
	 * @return object MediaFile
	 * @access public
	 * @since 4/27/07
	 */
	function getFileById ( $id ) {
		ArgumentValidator::validate($id, ExtendsValidatorRule::getRule("Id"));
		
		if (!$this->_files[$id->getIdString()]) {
			$record = $this->_asset->getRecord($id);
			$this->_files[$id->getIdString()] = new MediaFile ($this, $record);
		}
		
		return $this->_files[$id->getIdString()];
	}
	
	/**
	 * Answer a MediaFile in this Asset
	 * 
	 * @param string $id
	 * @return object MediaFile
	 * @access public
	 * @since 4/27/07
	 */
	function getFileByIdString ( $id ) {
		ArgumentValidator::validate($id, NonZeroLengthStringValidatorRule::getRule());
		
		$idManager = Services::getService('Id');
		return $this->getFileById($idManager->getId($id));
	}
	
	/**
	 * Answer all of the MediaFiles for this asset
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 4/27/07
	 */
	function getFiles () {
		$idManager = Services::getService('Id');
		$records = new MultiIteratorIterator;
		$records->addIterator(
			$this->_asset->getRecordsByRecordStructure(
				$idManager->getId('FILE')));
		$records->addIterator(
			$this->_asset->getRecordsByRecordStructure(
				$idManager->getId('REMOTE_FILE')));
		
		$files = array();
		while ($records->hasNext()) {
			$files[] = new MediaFile($this, $records->next());
		}
		
		$mediaFiles = new HarmoniIterator($files);
		return $mediaFiles;
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
		return $this->_getFirstDCValue('dc.title');
	}
	/**
	 * Answer an array of DublinCore Title strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getTitles () {
		return $this->_getAllDCValues('dc.title');
	}
	
	/**
	 * Answer the first DublinCore Creator
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getCreator () {
		return $this->_getFirstDCValue('dc.creator');
	}
	/**
	 * Answer an array of DublinCore Creator strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getCreators () {
		return $this->_getAllDCValues('dc.creator');
	}
	
	/**
	 * Answer the first DublinCore Source
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getSource () {
		return $this->_getFirstDCValue('dc.source');
	}
	/**
	 * Answer an array of DublinCore Source strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getSources () {
		return $this->_getAllDCValues('dc.source');
	}
	
	/**
	 * Answer the first DublinCore Publisher
	 * 
	 * @return string
	 * @access public
	 * @since 4/27/07
	 */
	function getPublisher () {
		return $this->_getFirstDCValue('dc.publisher');
	}
	/**
	 * Answer an array of DublinCore Publisher strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getPublishers () {
		return $this->_getAllDCValues('dc.publisher');
	}
	
	/**
	 * Answer the first DublinCore Date
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 4/27/07
	 */
	function getDate () {
		return $this->_getFirstDCValue('dc.date');
	}
	/**
	 * Answer an array of DublinCore Date objects
	 * 
	 * @return array
	 * @access public
	 * @since 4/27/07
	 */
	function getDates () {
		return $this->_getAllDCValues('dc.date');
	}
	
	/*********************************************************
	 * Private Instance variables and methods
	 *********************************************************/
	
	/**
	 * Constructor
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access protected
	 * @since 4/27/07
	 */
	function __construct ( $asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		
		$this->_asset = $asset;
	}
	
	/**
	 * Answer the first Dublin Core Value for the part id string
	 * 
	 * @param string partIdString
	 * @return string
	 * @access private
	 * @since 4/27/07
	 */
	function _getFirstDCValue ( $partIdString ) {
		$this->_loadDC();
		
		if (count($this->_dcValues[$partIdString])) {
			return $this->_dcValues[$partIdString][0];
		} else {
			return "";
		}
	}
	
	/**
	 * Answer an array of Dublin Core Values for the part id string
	 * 
	 * @param string partIdString
	 * @return string
	 * @access private
	 * @since 4/27/07
	 */
	function _getAllDCValues ( $partIdString ) {
		$this->_loadDC();
		
		return $this->_dcValues[$partIdString];
	}
	
	/**
	 * Load the DC values for this asset
	 * 
	 * @return void
	 * @access private
	 * @since 4/27/07
	 */
	function _loadDC () {
		if (!isset($this->_dcValues)) {
			$this->_dcValues = array(
				'dc.title'			=> array(),
				'dc.creator'		=> array(),
				'dc.contributor'	=> array(),
				'dc.description'	=> array(),
				'dc.subject'		=> array(),
				'dc.format'			=> array(),
				'dc.publisher'		=> array(),
				'dc.source'			=> array(),
				'dc.rights'			=> array()
			);
			
			$idManager = Services::getService("Id");
			$dcRecords = $this->_asset->getRecordsByRecordStructure(
				$idManager->getId("dc"));
		
			if ($dcRecords->hasNext()) {
				$record = $dcRecords->next();
				foreach (array_keys($this->_dcValues) as $partIdString) {
					$parts = $record->getPartsByPartStructure(
						$idManager->getId($partIdString));
					while ($parts->hasNext()) {
						$part = $parts->next();
						$value = $part->getValue();
						$this->_dcValues[$partIdString][] = HtmlString::getSafeHtml($value->asString());
					}
				}
				
				// Add on date objects
				$this->_dcValues['dc.date'] = array();
				$partIdString = 'dc.date';
				$parts = $record->getPartsByPartStructure(
					$idManager->getId($partIdString));
				while ($parts->hasNext()) {
					$part = $parts->next();
					$this->_dcValues[$partIdString][] = $part->getValue();
				}
			} else {
				$this->_dcValues['dc.date'] = array();
			}
		}
	}
}

?>