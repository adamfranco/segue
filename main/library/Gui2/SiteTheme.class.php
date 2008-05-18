<?php
/**
 * @since 5/6/08
 * @package harmoni.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(HARMONI.'/Gui2/Theme.interface.php');
require_once(HARMONI.'/Gui2/Theme.abstract.php');
require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');
require_once(HARMONI.'/Gui2/HistoryEntry.class.php');
require_once(HARMONI.'/Gui2/ThemeOption.class.php');
require_once(HARMONI.'/Gui2/ThemeOptions.interface.php');
require_once(HARMONI.'/Gui2/ThemeModification.interface.php');

require_once(dirname(__FILE__).'/ThemeThumbnail.class.php');
require_once(dirname(__FILE__).'/ThemeImage.class.php');

/**
 * All GUI 2 themes must implement this interface
 * 
 * @since 5/6/08
 * @package harmoni.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Gui2_SiteTheme
	extends Harmoni_Gui2_ThemeAbstract
	implements Harmoni_Gui2_ThemeInterface, Harmoni_Gui2_ThemeOptionsInterface, Harmoni_Gui2_ThemeModificationInterface
{
	/**
	 * Constructor
	 * 
	 * @param string $path
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function __construct ($databaseIndex, $id) {
		ArgumentValidator::validate($databaseIndex, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($id, NonzeroLengthStringValidatorRule::getRule());
		
		$this->databaseIndex = $databaseIndex;
		$this->id = $id;
		
		$this->preHtml = array();
		$this->postHtml = array();
	}
	
	/**
	 * Answer the siteId
	 * 
	 * @return string
	 * @access protected
	 * @since 5/17/08
	 */
	protected function getSiteId () {
		if (!isset($this->siteId)) {
			$query = new SelectQuery;
			$query->addTable('segue_site_theme');
			$query->addColumn('fk_site');
			$query->addWhereEqual('id', $this->id);
			
			$dbMgr = Services::getService("DatabaseManager");
			$result = $dbMgr->query($query, $this->databaseIndex);
			if (!$result->hasNext())
				throw new UnknownIdException("Theme with id '".$this->id."' does not exist.");
			$row = $result->next();
			$result->free();
			$this->siteId = $row['fk_site'];
		}
		return $this->siteId;
	}
	
	/*********************************************************
	 * Output
	 *********************************************************/
	
	/**
	 * Answer a block of CSS for the theme
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getCss () {
		$allCss = '';
		foreach ($this->getCssFiles() as $cssFile) {
			try {
				$css = trim ($this->getThemeDataByType($cssFile));
			} catch (OperationFailedException $e) {
				$css = '';
			}

			// Replace any option-markers
			foreach($this->getOptions() as $option) {
				$choice = $option->getCurrentChoice();
				foreach($choice->getSettings() as $marker => $replacement) {
					$css = str_replace($marker, $replacement, $css);
				}
			}
			
			// Replace image urls
			$css = $this->replaceRelativeUrls($css);
			
			$allCss .= $css;
		}
		return $allCss;
	}
	
	/**
	 * Print out the component tree
	 * 
	 * @param object ComponentInterface $rootComponent
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function printPage (ComponentInterface $rootComponent) {
		$rootComponent->render($this, "\t\t");
	}
	
	/**
	 * Returns the HTML string that needs to be printed before successful rendering
	 * of components of the given type and index. Note: use of the PreHTML
	 * and PostHTML get/set methods is discouraged - use styles instead: see 
	 * <code>addStyleForComponentType()</code> and <code>getStylesForComponentType()</code>.
	 * @access public
	 * @param integer type The type of the component. One of BLANK, HEADING, HEADER, FOOTER,
	 * BLOCK, MENU, MENU_ITEM_LINK_UNSELECTED, MENU_ITEM_LINK_SELECTED, MENU_ITEM_HEADING, OTHER.
	 * @param integer index The index that will determine which HTML string to return
	 * If the given index is greater than the maximal registered index
	 * for the given component type, then the highest index availible will be used.
	 * @return string The HTML string.
	 * @access public
	 * @since 5/6/08
	 */
	public function getPreHTMLForComponentType ($type, $index) {
		$type = $this->resolveType($type, $index);
		return $this->getPreHtml($type);
	}
	
	/**
	 * Returns the HTML string that needs to be printed after successful rendering
	 * of components of the given type and index. Note: use of the PreHTML
	 * and PostHTML get/set methods is discouraged - use styles instead: see 
	 * <code>addStyleForComponentType()</code> and <code>getStylesForComponentType()</code>.
	 * @access public
	 * @param integer type The type of the component. One of BLANK, HEADING, HEADER, FOOTER,
	 * BLOCK, MENU, MENU_ITEM_LINK_UNSELECTED, MENU_ITEM_LINK_SELECTED, MENU_ITEM_HEADING, OTHER.
	 * @param integer index The index that will determine which HTML string to return
	 * If the given index is greater than the maximal registered index
	 * for the given component type, then the highest index availible will be used.
	 * @return string The HTML string.
	 * @access public
	 * @since 5/6/08
	 */
	public function getPostHTMLForComponentType ($type, $index) {
		$type = $this->resolveType($type, $index);
		return $this->getPostHtml($type);
	}
	
	/**
	 * This method is just here for compatability with the original
	 * GUIManager Components, should just return an empty array
	 * 
	 * @param integer type The type of the component. One of BLANK, HEADING, HEADER, FOOTER,
	 * BLOCK, MENU, MENU_ITEM_LINK_UNSELECTED, MENU_ITEM_LINK_SELECTED, MENU_ITEM_HEADING, OTHER.
	 * @param integer index The index that will determine which HTML string to return
	 * @return array
	 * @access public
	 * @since 5/6/08
	 */
	public function getStylesForComponentType ($type, $index) {
		return array();
	}
	
	/**
	 * Answer an image file for the image with the file-name specified
	 * 
	 * @param string $filename
	 * @return Harmoni_FileInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getImage ($filename) {
		return new Segue_Gui2_ThemeImage($this->databaseIndex, $this->id, $filename);
	}
	
	/*********************************************************
	 * Information
	 *********************************************************/
	
	/**
	 * Answer the Id of this theme.
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getIdString () {
		return 'site_theme-'.$this->id;
	}
	
	/**
	 * Answer the display name of this theme
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getDisplayName () {
		if (!isset($this->displayName))
			$this->loadInfo();
		if (is_null($this->displayName))
			return _("Untitled");
		
		return $this->displayName;
	}
	
	/**
	 * Answer a description of this theme
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getDescription () {
		if (!isset($this->description))
			$this->loadInfo();
		if (is_null($this->description))
			return '';
		
		return $this->description;
	}
	
	/**
	 * Answer a thumbnail file.
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getThumbnail () {
		return new Segue_Gui2_ThemeThumbnail($this->databaseIndex, $this->id);
	}
	
	/**
	 * Answer the date when this theme was last modified.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	public function getModificationDate () {
		throw new UnimplementedException();
	}
	
	/**
	 * Answer an array of ThemeHistory items, in reverse chronological order.
	 * 
	 * @return array
	 * @access public
	 * @since 5/8/08
	 */
	public function getHistory () {
		throw new UnimplementedException();
	}
	
	/*********************************************************
	 * Theme Modification
	 *********************************************************/
	
	/**
	 * Answer true if this theme supports modification.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/15/08
	 */
	public function supportsModification () {
		return true;
	}
	
	/**
	 * Answer an object that implements the ThemeModificationInterface
	 * for this theme. This could be the same or a different object.
	 * 
	 * @return object Harmoni_Gui2_ThemeModificationInterface
	 * @access public
	 * @since 5/15/08
	 */
	public function getModificationSession () {
		return $this;
	}
	
	/*********************************************************
	 * internal
	 *********************************************************/
	
	/**
	 * Load the information XML file
	 * 
	 * @return null
	 * @access protected
	 * @since 5/7/08
	 */
	protected function loadInfo () {
		$query = new SelectQuery();
		$query->addTable('segue_site_theme');
		$query->addColumn('display_name');
		$query->addColumn('description');
		$query->addColumn('modify_timestamp');
		$query->addWhereEqual('id', $this->id);
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new UnknownIdException("Theme with id '".$this->id."' does not exist.");
		$row = $result->next();
		$result->free();
		$this->displayName = $row['display_name'];
		$this->description = $row['description'];
		$this->modificationDate = DateAndTime::fromString($row['modify_timestamp']);
	}
	
	/**
	 * Answer a list of required CSS data types
	 * 
	 * @return array
	 * @access protected
	 * @since 5/6/08
	 */
	protected function getCssFiles () {
		$types = array_merge(array('Global'), $this->getComponentTypes());
		foreach ($types as $key => $type)
			$types[$key] = $type.'.css';
		
		return $types;
	}
	
	/**
	 * Answer a list of required template files
	 * 
	 * @return array
	 * @access protected
	 * @since 5/6/08
	 */
	protected function getTemplateFiles () {
		$types = $this->getComponentTypes();
		foreach ($types as $key => $type)
			$types[$key] = $type.'.html';
		
		return $types;
	}
	
	/**
	 * Answer Pre-HTML for the type specified.
	 * 
	 * @param string $type
	 * @return string
	 * @access protected
	 * @since 5/6/08
	 */
	protected function getPreHtml ($type) {
		if ($type == 'Blank')
			return '';
		
		if (!isset($this->preHtml[$type]))
			$this->loadType($type);
			
		return $this->preHtml[$type];
	}
	
	/**
	 * Answer Post-HTML for the type specified.
	 * 
	 * @param string $type
	 * @return string
	 * @access protected
	 * @since 5/6/08
	 */
	protected function getPostHtml ($type) {
		if ($type == 'Blank')
			return '';
		
		if (!isset($this->postHtml[$type]))
			$this->loadType($type);
			
		return $this->postHtml[$type];
	}
	
	/**
	 * @var array $preHtml;  
	 * @access private
	 * @since 5/6/08
	 */
	private $preHtml;
	
	/**
	 * @var array $postHtml;  
	 * @access private
	 * @since 5/6/08
	 */
	private $postHtml;
	
	/**
	 * Parse a type file and load its contents into our arrays
	 * 
	 * @param string $type
	 * @return null
	 * @access protected
	 * @since 5/6/08
	 */
	protected function loadType ($type) {
		if (!in_array($type, $this->getComponentTypes()))
			throw new InvalidArgumentException("Invalid type, '$type'.");
		
		try {
			$contents = trim ($this->getThemeDataByType($type.'.html'));
		} catch (OperationFailedException $e) {
			$contents = '';
		}
		
		// Set to empty for empty files.
		if (!strlen($contents)) {
			$this->preHtml[$type] = '';
			$this->postHtml[$type] = '';
			return;
		}
		
		// Verify that a placeholder exists
		if (strpos($contents, '[[CONTENT]]') === false)
			throw new OperationFailedException("Required template file, '{$type}.html' is missing a '[[CONTENT]]' placeholder in theme '".$this->getIdString()."'.");
		
		// Replace any option-markers
		foreach($this->getOptions() as $option) {
			$choice = $option->getCurrentChoice();
			foreach($choice->getSettings() as $marker => $replacement) {
				$contents = str_replace($marker, $replacement, $contents);
			}
		}
		
		// Replace image urls
		$contents = $this->replaceRelativeUrls($contents);
		
		// Save our pieces
		$this->preHtml[$type] = substr($contents, 0, strpos($contents, '[[CONTENT]]'));
		$this->postHtml[$type] = substr($contents, strpos($contents, '[[CONTENT]]') + 11);
	}
	
	/**
	 * Answer some theme data by type
	 * 
	 * @param string $type
	 * @return string
	 * @access protected
	 * @since 5/15/08
	 */
	protected function getThemeDataByType ($type) {
		$query = new SelectQuery();
		$query->addTable('segue_site_theme_data');
		$query->addColumn('data');
		$query->addWhereEqual('fk_theme', $this->id);
		$query->addWhereRawEqual('fk_type', "(SELECT id FROM segue_site_theme_data_type WHERE data_type = '".addslashes($type)."')");
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		if (!$result->hasNext())
			throw new OperationFailedException("Required template data, '{$type}' is missing from theme '".$this->getIdString()."'.");
			
		$row = $result->next();
		$result->free();
		return $row['data'];
	}
	
	/**
	 * Answer some theme data by type
	 * 
	 * @param string $type
	 * @return string
	 * @access protected
	 * @since 5/15/08
	 */
	protected function updateThemeDataByType ($type, $data) {
		$typeId = $this->getTypeId($type);
		try {
			$this->getThemeDataByType($type);
			
			$query = new UpdateQuery;
			$query->addWhereEqual('fk_theme', $this->id);
			$query->addWhereEqual('fk_type', $typeId);
		} catch (OperationFailedException $e) {
			$query = new InsertQuery;
			$query->addValue('fk_theme', $this->id);
			$query->addValue('fk_type', $typeId);
		}
		$query->setTable('segue_site_theme_data');
		$query->addValue('data', $data);
		
		$dbMgr = Services::getService("DatabaseManager");
		$dbMgr->query($query, $this->databaseIndex);
	}
	
	/**
	 * Answer the id of a data type
	 * 
	 * @param string $type
	 * @return string
	 * @access protected
	 * @since 5/15/08
	 */
	protected function getTypeId ($type) {
		$dbMgr = Services::getService("DatabaseManager");
		
		// Get the type Id
		$query = new SelectQuery;
		$query->addTable('segue_site_theme_data_type');
		$query->addColumn('id');
		$query->addWhereEqual('data_type', $type);
		$result = $dbMgr->query($query, $this->databaseIndex);
		
		if ($result->hasNext()) {
			$row = $result->next();
			$result->free();
			return $row['id'];
		} else {
			$result->free();
			
			$query = new InsertQuery;
			$query->setTable('segue_site_theme_data_type');
			$query->addValue('data_type', $type);
			$result = $dbMgr->query($query, $this->databaseIndex);
			return $result->getLastAutoIncrementValue();
		}
	}
	
/*********************************************************
 * Theme Modification
 *********************************************************/
 	/**
	 * Answer false if it is known that all methods will result in PermissionDeniedExceptions
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/17/08
	 */
	public function canModify () {
		$az = Services::getService("AuthZ");
		$id = Services::getService("Id");
		return $az->isUserAuthorized(
			$id->getId('edu.middlebury.authorization.modify'),
			$id->getId($this->getSiteId()));
	}
	
	/**
	 * Delete the theme for this session
	 * 
	 * @return null
	 * @access public
	 * @since 5/17/08
	 */
	public function delete () {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$query = new DeleteQuery;
		$query->setTable('segue_site_theme');
		$query->addWhereEqual('id', $this->id);
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
	}
	
 	/*********************************************************
	 * Info
	 *********************************************************/
	
	/**
	 * Set the display name.
	 * 
	 * @param string $displayName
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateDisplayName ($displayName) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$this->displayName = $displayName;
		$query = new UpdateQuery;
		$query->setTable('segue_site_theme');
		$query->addValue('display_name', $displayName);
		$query->addWhereEqual('id', $this->id);
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
	}
	
	/**
	 * Update the description
	 * 
	 * @param string $description
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateDescription ($description) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$this->description = $description;
		$query = new UpdateQuery;
		$query->setTable('segue_site_theme');
		$query->addValue('description', $description);
		$query->addWhereEqual('id', $this->id);
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
	}
	
	/**
	 * Update the thumbnail
	 * 
	 * @param object Harmoni_Filing_FileInterface $thumbnail
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateThumbnail (Harmoni_Filing_FileInterface $thumbnail) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		// Delete the old thumbnail
		$query = new DeleteQuery;
		$query->setTable('segue_site_theme_thumbnail');
		$query->addWhereEqual('fk_theme', $this->id);
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query);
		
		$query = new InsertQuery;
		$query->setTable('segue_site_theme_thumbnail');
		$query->addValue('fk_theme', $this->id);
		$query->addValue('mime_type', $thumbnail->getMimeType());
		$query->addValue('size', $thumbnail->getSize());
		$query->addValue('data', base64_encode($thumbnail->getContents()));
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
	}
	
	/*********************************************************
	 * Options
	 *********************************************************/
	
	/**
	 * Answer an XML document for the options for this theme
	 * 
	 * @return object Harmoni_DOMDocument
	 * @access public
	 * @since 5/15/08
	 */
	public function getOptionsDocument () {
		try {
			$optionsString = $this->getThemeDataByType('options.xml');
			$doc = new Harmoni_DOMDocument;
			$doc->preserveWhiteSpace = false;
			if (strlen($optionsString))
				$doc->loadXML($optionsString);
			return $doc;
		} catch (OperationFailedException $e) {
			return new Harmoni_DOMDocument;
		}
	}
	
	/**
	 * Update the options XML with a new document
	 * 
	 * @param object Harmoni_DOMDocument $optionsDocument
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateOptionsDocument (Harmoni_DOMDocument $optionsDocument) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		// Unset the options if we have an empty document.
		if (!isset($optionsDocument->documentElement)) {
			$this->updateThemeDataByType('options.xml', '');
			return;
		}
		
		// Validate any options document given.
		$optionsDocument->schemaValidateWithException(HARMONI.'/Gui2/theme_options.xsd');
		$this->updateThemeDataByType('options.xml', $optionsDocument->saveXML());
	}
	
	/*********************************************************
	 * CSS and HTML Templates
	 *********************************************************/
	
	/**
	 * Answer the global CSS string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getGlobalCss () {
		try {
			return $this->getThemeDataByType('Global.css');
		} catch (OperationFailedException $e) {
			return '';
		}
	}
	
	/**
	 * Set the global CSS string
	 * 
	 * @param string $css
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateGlobalCss ($css) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$this->updateThemeDataByType('Global.css', $css);
	}
	
	/**
	 * Get the CSS for a component Type.
	 * 
	 * @param string $componentType
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getCssForType ($componentType) {
		try {
			return $this->getThemeDataByType($componentType.'.css');
		} catch (OperationFailedException $e) {
			return '';
		}
	}
	
	/**
	 * Set the CSS for a component Type
	 * 
	 * @param string $componentType
	 * @param string $css
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateCssForType ($componentType, $css) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$this->updateThemeDataByType($componentType.'.css', $css);
	}
	
	/**
	 * Get the HTML template for a component Type.
	 * 
	 * @param string $componentType
	 * @return string
	 * @access public
	 * @since 5/15/08
	 */
	public function getTemplateForType ($componentType) {
		try {
			return $this->getThemeDataByType($componentType.'.html');
		} catch (OperationFailedException $e) {
			return '';
		}
	}
	
	/**
	 * Set the CSS for a component Type
	 * 
	 * @param string $componentType
	 * @param string $templateHtml
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function updateTemplateForType ($componentType, $templateHtml) {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		$this->updateThemeDataByType($componentType.'.html', $templateHtml);
	}
	
	/*********************************************************
	 * Images
	 *********************************************************/
	
	/**
	 * Answer the images for this theme
	 * 
	 * @return array of Harmoni_Filing_FileInterface objects
	 * @access public
	 * @since 5/15/08
	 */
	public function getImages () {
		$query = new SelectQuery();
		$query->addTable('segue_site_theme_image');
		$query->addColumn('path');
		$query->addWhereEqual('fk_theme', $this->id);
		
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		$images = array();
		while ($result->hasNext()) {
			$row = $result->next();
			$images[] = new Segue_Gui2_ThemeImage($this->databaseIndex, $this->id, $row['path']);
		}
		$result->free();
		return $images;
	}
	
	/**
	 * Add a new image at the path specified.
	 * 
	 * @param object Harmoni_Filing_FileInterface $image
	 * @param string $filename
	 * @param string $prefixPath
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function addImage (Harmoni_Filing_FileInterface $image, $filename, $prefixPath = '') {
		if (!$this->canModify())
			throw new PermissionDeniedException();
		
		ArgumentValidator::validate($filename, NonzeroLengthStringValidatorRule::getRule());
		
		$path = trim($prefixPath, '/');
		if (strlen($path))
			$path = $path.'/'.$filename;
		else
			$path = $filename;
		
		// Delete the old image
		$query = new DeleteQuery;
		$query->setTable('segue_site_theme_image');
		$query->addWhereEqual('fk_theme', $this->id);
		$query->addWhereEqual('path', $path);
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
		
		$query = new InsertQuery;
		$query->setTable('segue_site_theme_image');
		$query->addValue('fk_theme', $this->id);
		$query->addValue('mime_type', $image->getMimeType());
		$query->addValue('path', $path);
		$query->addValue('size', $image->getSize());
		$query->addValue('data', base64_encode($image->getContents()));
		$dbc = Services::getService('DatabaseManager');
		$dbc->query($query, $this->databaseIndex);
	}
	
	/**
	 * Delete an image at the path specified.
	 * 
	 * @param string $path
	 * @return null
	 * @access public
	 * @since 5/15/08
	 */
	public function deleteImage ($path) {
		throw new UnimplementedException();
	}

}

?>