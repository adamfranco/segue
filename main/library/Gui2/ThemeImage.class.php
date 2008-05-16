<?php
/**
 * @since 5/16/08
 * @package segue.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This class provides access to them images
 * 
 * @since 5/16/08
 * @package segue.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Gui2_ThemeImage
	extends Segue_Gui2_ThemeThumbnail
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
	public function __construct ($databaseIndex, $themeId, $path) {
		parent::__construct($databaseIndex, $themeId);
		
		ArgumentValidator::validate($path, NonzeroLengthStringValidatorRule::getRule());
		$this->path = $path;
	}
	
	/**
	 * Answer the file name (base-name), including any extension.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getBaseName () {
		return basename($this->path);
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
		$dir = dirname($this->getPath);
		if (strlen($dir))
			$path = $dir.'/'.$baseName;
		else
			$path = $baseName;
		$this->setPath($path);
	}
	
	/**
	 * Answer a full path to the file, including the file name.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getPath () {
		return $this->path;
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
		$query = new UpdateQuery();
		$query->setTable('segue_site_theme_image');
		$query->addValue('path', $path);
		$query->addWhereEqual('fk_theme', $this->themeId);
		$query->addWhereEqual('path', $this->path);
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		
		$this->path = $path;
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
		$query->addTable('segue_site_theme_image');
		$query->addColumn('data');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$query->addWhereEqual('path', $this->path);
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme image '".$this->path."' for theme '".$this->themeId."' does not exist.");
		
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
			$query->addWhereEqual('path', $this->path);
		} catch (UnknownIdException $e) {
			$query = new InsertQuery();
			$query->addValue('fk_theme', $this->themeId);
			$query->addValue('path', $this->path);
			$mime = Services::getService("MIME");
			$query->addValue('mime_type', $mime->getMIMETypeForFileName($this->getBaseName()));
		}
		
		$query->setTable('segue_site_theme_image');
		$query->addValue('data', base64_encode($contents));
		$query->addValue('size', strlen($contents));
		
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme image '".$this->path."' for theme '".$this->themeId."' does not exist.");
		
		return $result->field('data');
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
		$query->setTable('segue_site_theme_image');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$query->addWhereEqual('path', $this->path);
		$dbMgr = Services::getService("DatabaseManager");
		$dbMgr->query($query, $this->databaseIndex);
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
		$query->addTable('segue_site_theme_image');
		$query->addColumn('size');
		$query->addColumn('mime_type');
		$query->addColumn('modify_timestamp');
		
		$query->addWhereEqual('fk_theme', $this->themeId);
		$query->addWhereEqual('path', $this->path);
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme image '".$this->path."' for theme '".$this->themeId."' does not exist.");
		
		$row = $result->next();
		$this->size = $row['size'];
		$this->mimeType = $row['mime_type'];
		$this->modificationDate = DateAndTime::fromString($row['modify_timestamp']);
	}
}

?>