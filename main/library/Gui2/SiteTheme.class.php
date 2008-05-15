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

require_once(dirname(__FILE__).'/ThemeThumbnail.class.php');

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
	implements Harmoni_Gui2_ThemeInterface 
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
	 * Options - Internal
	 *********************************************************/
	/**
	 * Load the options.xml file if it exists
	 * 
	 * @return null
	 * @access protected
	 * @since 5/9/08
	 */
	protected function loadOptions () {
		try {
			$optionsString = $this->getThemeDataByType('options');
		} catch (OperationFailedException $e) {
			$this->options = array();
			return;
		}
		if (!strlen(trim($optionsString))) {
			$this->options = array();
			return;
		}
		
		$options = new Harmoni_DOMDocument;
		$options->loadXML($optionsString);
		$options->schemaValidateWithException(dirname(__FILE__).'/theme_options.xsd');
		
		
		$this->options = $this->buildOptionsFromDocument($options);
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
		throw new UnimplementedException();
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
			$contents = trim ($this->getThemeDataByType($type));
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
		return $row['data'];
	}
}

?>