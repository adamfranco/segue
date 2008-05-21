<?php
/**
 * @since 5/15/08
 * @package segue.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This class represents a theme thumbnail file in the database.
 * 
 * @since 5/15/08
 * @package segue.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Gui2_ThemeThumbnail
	implements Harmoni_Filing_FileInterface
{
	
	/**
	 * Constructor
	 * 
	 * @param int $databaseIndex
	 * @param int $themeId
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function __construct ($databaseIndex, $themeId) {
		ArgumentValidator::validate($databaseIndex, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($themeId, IntegerValidatorRule::getRule());
		
		$this->databaseIndex = $databaseIndex;
		$this->themeId = $themeId;
	}
	
	/**
	 * Answer the MIME type of this file.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getMimeType () {
		if (!isset($this->mimeType))
			$this->loadInfo();
		
		return $this->mimeType;
	}
	
	/**
	 * Set the MIME type of the file
	 * 
	 * @param string $mimeType
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function setMimeType ($mimeType) {
		if (!preg_match('/^(text|image|audio|video|application)/[a-z0-9_-]+$', $mimeType))
			throw new OperationFailedException("Invalid MIME Type '$mimeType'.");
		$this->mimeType = $mimeType;
	}
	
	/**
	 * Answer the file name (base-name), including any extension.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getBaseName () {
		$mimeMgr = Services::getService('MIME');
		return 'thumbnail.'.$mimeMgr->getExtensionForMIMEType($this->getMimeType());
	}
	
	/**
	 * [Re]Set the base name for the file
	 * 
	 * @param string $baseName
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function setBaseName ($baseName) {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer a full path to the file, including the file name.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getPath () {
		return $this->getBaseName();
	}
	
	/**
	 * [Re]Set a full path to the file, including the file name.
	 * 
	 * @param string $path
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function setPath ($path) {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer the size (bytes) of the file
	 * 
	 * @return int
	 * @access public
	 * @since 5/15/08
	 */
	public function getSize () {
		if (!isset($this->size))
			$this->loadInfo();
		return $this->size;
	}
	
	/**
	 * Answer the contents of the file
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getContents () {
		$query = new SelectQuery();
		$query->addTable('segue_site_theme_thumbnail');
		$query->addColumn('data');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme thumbnail for theme '".$this->themeId."' does not exist.");
		
		return base64_decode($result->field('data'));
	}
	
	/**
	 * Set the contents of the file
	 * 
	 * @param string $contents
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function setContents ($contents) {
		try {
			$this->getContents();
			
			$query = new UpdateQuery();
			$query->addWhereEqual('fk_theme', $this->themeId);
		} catch (UnknownIdException $e) {
			$query = new InsertQuery();
			$query->addValue('fk_theme', $this->themeId);
			$mime = Services::getService("MIME");
			$query->addValue('mime_type', $mime->getMIMETypeForFileName($this->getBaseName()));
		}
		
		$query->setTable('segue_site_theme_thumbnail');
		$query->addValue('data', base64_encode($contents));
		$query->addValue('size', strlen($contents));
		
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme thumbnail for theme '".$this->themeId."' does not exist.");
		
		return $result->field('data');
	}
	
	/**
	 * Set the contents of the file. Alias for setContents()
	 * 
	 * @param string $contents
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function putContents ($contents) {
		$this->setContents($contents);
	}
	
	/**
	 * Delete the file.
	 * 
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function delete () {
		$query = new DeleteQuery();
		$query->setTable('segue_site_theme_thumbnail');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$dbMgr = Services::getService("DatabaseManager");
	}
	
	/**
	 * Answer the modification date/time
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	public function getModificationDate () {
		if (!isset($this->modificationDate))
			$this->loadInfo();
		return $this->modificationDate;
	}
	
	/**
	 * load the info for this file
	 * 
	 * @return null
	 * @access protected
	 * @since 5/15/08
	 */
	protected function loadInfo () {
		$query = new SelectQuery();
		$query->addTable('segue_site_theme_thumbnail');
		$query->addColumn('size');
		$query->addColumn('mime_type');
		$query->addColumn('modify_timestamp');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme thumbnail for theme '".$this->themeId."' does not exist.");
		
		$row = $result->next();
		$this->size = $row['size'];
		$this->mimeType = $row['mime_type'];
		$this->modificationDate = DateAndTime::fromString($row['modify_timestamp']);
	}
	
}

?>