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
require_once(HARMONI.'/utilities/Filing/FileSystemFile.class.php');
require_once(HARMONI.'/Gui2/HistoryEntry.class.php');
require_once(HARMONI.'/Gui2/ThemeOption.class.php');

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
	public function __construct ($path) {
		if (!file_exists($path))
			throw new UnknownIdException("No theme found with Id, '".basename($path)."'.");
		if (!is_dir($path) || !is_readable($path))
			throw new ConfigurationErrorException("'$path' is not a readable theme directory.");
		
		$this->path = $path;
		$this->optionsPath = $this->path.'/options.xml';
		
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
			if (!file_exists($this->path.'/'.$cssFile)) 
				throw new OperationFailedException("Required CSS file  '$cssFile' was not found in the '".$this->getIdString()."' theme.");
			$css = trim (file_get_contents($this->path.'/'.$cssFile));

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
		// Ensure that no directory traversal requested
		$relPathParts = explode('/', $filename);
		foreach ($relPathParts as $part) {
			if ($part == '..')
				throw new InvalidArgumentException("Directory traversal is not allowed.");
		}
		
		$path = $this->path.'/images/'.$filename;
		if (!file_exists($path))
			throw new UnknownIdException("No image found with name '$filename' in theme '".$this->getIdString()."'");
		
		return new Harmoni_Filing_FileSystemFile($path);
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
		return basename($this->path);
	}
	
	/**
	 * Answer the display name of this theme
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getDisplayName () {
		if (!isset($this->info))
			$this->loadInfo();
		if (is_null($this->info))
			return _("Untitled");
		
		$xpath = new DOMXPath($this->info);
		return $xpath->query('/ThemeInfo/DisplayName')->item(0)->nodeValue;
	}
	
	/**
	 * Answer a description of this theme
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getDescription () {
		if (!isset($this->info))
			$this->loadInfo();
		if (is_null($this->info))
			return '';
		
		$xpath = new DOMXPath($this->info);
		return $xpath->query('/ThemeInfo/Description')->item(0)->nodeValue;
	}
	
	/**
	 * Answer a thumbnail file.
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getThumbnail () {
		$file = $this->path.'/thumbnail.png';
		if (!file_exists($file))
			throw new OperationFailedException("Required thumbnail file, 'thumbnail.png' is missing from theme '".$this->getIdString()."'.");
		if (!is_readable($file))
			throw new OperationFailedException("Required thumbnail file, 'thumbnail.png' is not readable in theme '".$this->getIdString()."'.");
		
		return new Harmoni_Filing_FileSystemFile($file);
	}
	
	/**
	 * Answer an array of ThemeHistory items, in reverse chronological order.
	 * 
	 * @return array
	 * @access public
	 * @since 5/8/08
	 */
	public function getHistory () {
		if (!isset($this->info))
			$this->loadInfo();
		if (is_null($this->info))
			return array();
		
		
		$xpath = new DOMXPath($this->info);
		$entryElements = $xpath->query('/ThemeInfo/History/Entry');
		$history = array();
		$dates = array();
		foreach ($entryElements as $entryElement) {
			$comment = $xpath->query('Comment', $entryElement)->item(0)->nodeValue;
			$name = $xpath->query('Name', $entryElement)->item(0)->nodeValue;
			$email = $xpath->query('EMail', $entryElement)->item(0)->nodeValue;
			$date = DateAndTime::fromString($entryElement->getAttribute('date'));
			
			$history[] = new Harmoni_Gui2_HistoryEntry($date, $comment, $name, $email);
			$dates[] = $date->asString();
		}
		$unique = array_keys($history);
		array_multisort($dates, SORT_DESC, $unique, $history);
		
		return $history;
	}
	
	/*********************************************************
	 * Theme options
	 *********************************************************/
	
	/**
	 * Answer true if this theme supports options.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/6/08
	 */
	public function supportsOptions () {
		return true;
	}
	
	/**
	 * Answer an object that implements the ThemeOptionsInterface
	 * for this theme. This could be the same or a different object.
	 * 
	 * @return object Harmoni_Gui2_ThemeOptionsInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getOptionsSession () {
		return $this;
	}
	
	/*********************************************************
	 * Theme Options Session methods
	 *********************************************************/
	/**
	 * Answer an array of ThemeOption objects
	 * 
	 * @return array of Harmoni_Gui2_ThemeOptionInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getOptions () {
		if (!isset($this->options))
			$this->loadOptions();
		return $this->options;
	}
	
	/**
	 * Answer an option by Id-string
	 * 
	 * @param string $id
	 * @return Harmoni_Gui2_ThemeOptionInterface
	 * @access public
	 * @since 5/9/08
	 */
	public function getOption ($id) {
		foreach ($this->getOptions() as $option) {
			if ($option->getIdString() == $id)
				return $option;
		}
		
		throw new UnknownIdException("No option known with id '$id' in theme '".$this->getIdString()."'.");
	}
	
	/**
	 * Answer true if the current options value is to use defaults.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/6/08
	 */
	public function usesDefaults () {
		foreach ($this->getOptions() as $option) {
			if ($option->getValue() != $option->getDefaultValue())
				return false;
		}
		
		return true;
	}
	
	/**
	 * Answer a string version of the current option-values that
	 * can be fed back into setOptions() to return to the current
	 * state.
	 * 
	 * @return string
	 * @access public
	 * @since 5/6/08
	 */
	public function getOptionsValue () {
		$value = '';
		
		foreach ($this->getOptions() as $option) {
			if ($option->getValue() != $option->getDefaultValue())
				$value .= '&amp;'.$option->getIdString().'='.$option->getValue();
		}
		
		return $value;
	}
	
	/**
	 * Given a string created by getOptionsValue(), set the current
	 * state of the options to match.
	 * 
	 * @param string $optionsValue
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function setOptionsValue ($optionsValue) {
		$options = $this->getOptions();
		if (!count($options)) {
			if (strlen($optionsValue))
				throw new OperationFailedException("No options to accept the value passed.");
			return;	
		}
		
		if (!preg_match_all('/&amp;([a-zA-Z_-]{1,50})=([a-zA-Z_-]{1,50})/', $optionsValue, $matches))
			throw new InvalidArgumentException("'$optionsValue' is not a valid options value.");
		
		for ($i = 0; $i < count($matches[1]); $i++) {
			$option = $this->getOption($matches[1][$i]);
			$option->setValue($matches[2][$i]);
		}
	}
	
	/**
	 * Set all options to use their defaults
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function useDefaults () {
		$this->loadOptions();
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
		if (!file_exists($this->optionsPath)) {
			$this->options = array();
			return;
		}
		
		$options = new Harmoni_DOMDocument;
		$options->load($this->optionsPath);
		$options->schemaValidateWithException(dirname(__FILE__).'/theme_options.xsd');
		$xpath = new DOMXPath($options);
		
		$this->options = array();
		foreach ($xpath->query('/options/option') as $optionElement) {
			$displayName = $this->getPathLangVersion($xpath, './displayName', $optionElement);
			$description = $this->getPathLangVersion($xpath, './description', $optionElement);
			
			$choices = array();
			foreach ($xpath->query('./choice', $optionElement) as $choiceElement) {
				$choice = new Harmoni_Gui2_Choice;
				$choice->key = $xpath->query('./key', $choiceElement)->item(0)->nodeValue;
				$choice->label = $this->getPathLangVersion($xpath, './label', $choiceElement);
				if ($choiceElement->hasAttribute('isDefault') 
						&& $choiceElement->getAttribute('isDefault') == 'true')
					$choice->isDefault = true;
				else
					$choice->isDefault = false;
				
				$choice->settings = array();
				foreach ($xpath->query('./setting', $choiceElement) as $settingElement) {
					$choice->settings[$settingElement->getAttribute('marker')] = $settingElement->nodeValue;
				}
				
				$choices[] = $choice;
			}
			$this->options[] = new Harmoni_Gui2_ThemeOption($optionElement->getAttribute('id'), $displayName, $description, $choices);
		}
	}
	
	/**
	 * Get the version of an element in the best language available
	 * 
	 * @param object DOMXPath $xpath
	 * @param string $path
	 * @param object DOMElement $element
	 * @return string
	 * @access protected
	 * @since 5/9/08
	 */
	protected function getPathLangVersion (DOMXPath $xpath, $path, DOMElement $element) {
			$langMgr = Services::getService("LanguageManager");
			$lang = $langMgr->getLanguage();
			
			// Current language
			if ($xpath->query($path."[@lang = '$lang']", $element)->length) {
				return $xpath->query($path."[@lang = '$lang']", $element)->item(0)->nodeValue;
			}
			
			// Try another country's version of the same language
			$langOnly = substr($lang, 0, strpos($lang, '_'));
			$regex = '/'.$langOnly.'_.+/';
			foreach ($xpath->query($path, $element) as $elem) {
				if (preg_match($regex, $elem->getAttribute('lang'))) {
					return $elem->nodeValue;
				}
			}
			
			// Default to english if available
			if ($xpath->query($path."[@lang = 'en_US']", $element)->length) {
				return  $xpath->query($path."[@lang = 'en_US']", $element)->item(0)->nodeValue;
			}
			
			// Answer the first one
			if ($xpath->query($path, $element)->length) {
				return  $xpath->query($path, $element)->item(0)->nodeValue;
			}
			
			throw new OperationFailedException("No elements found that match '$path'.");
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
		$path = $this->path.'/info.xml';
		if (!file_exists($path))
			throw new OperationFailedException("Theme '".$this->getIdString()."' is missing its info.xml file.");
		
		$this->info = new Harmoni_DOMDocument;
		$this->info->load($path);
		$this->info->schemaValidateWithException(dirname(__FILE__).'/theme_info.xsd');
	}
	
	/**
	 * Answer a list of required CSS files
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
	 * Answer an array of Component types
	 * 
	 * @return array
	 * @access protected
	 * @since 5/6/08
	 */
	protected function getComponentTypes () {
		return array (	'Block_Background',
						'Block_Standard',
						'Block_Emphasized',
						'Block_Alert',
						
						'Menu',
						'Menu_Sub',
						'MenuItem_Link_Selected',
						'MenuItem_Link_Unselected',
						'MenuItem_Heading',
						
						'Heading_1',
						'Heading_2',
						'Heading_3',
						'Heading_4',
						
						'Header',
						'Footer'
					);
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
		
		$file = $this->path.'/'.$type.'.html';
		if (!file_exists($file))
			throw new OperationFailedException("Required template file, '{$type}.html' is missing from theme '".$this->getIdString()."'.");
		if (!is_readable($file))
			throw new OperationFailedException("Required template file, '{$type}.html' is not readable in theme '".$this->getIdString()."'.");
			
		$contents = trim(file_get_contents($file));
		
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
	 * Replace relative URLs with one that will work.
	 * 
	 * @param string $templateContent
	 * @return string
	 * @access protected
	 * @since 5/6/08
	 */
	protected function replaceRelativeUrls ($templateContent) {
		$srcRegex = '/
src=[\'"]

(?: \.\/ )?	# Optional current directy marker
images\/
([a-z0-9\.\/_-]+)

[\'"]

/ix';
		$urlRegex = '/
url\([\'"]?

(?: \.\/ )?	# Optional current directy marker
images\/
([a-z0-9\.\/_-]+)

[\'"]?\)

/ix';

		$harmoni = Harmoni::instance();
		preg_match_all($srcRegex, $templateContent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$replacement = 'src="'
				.$harmoni->request->quickURL('gui2', 'theme_image', 
					array('theme' => $this->getIdString(), 'file' => $matches[1][$i]))
				.'"';
			$templateContent = str_replace($matches[0][$i], $replacement, $templateContent);
		}
		
		preg_match_all($urlRegex, $templateContent, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			$replacement = "url('"
				.str_replace('&amp;', '&', $harmoni->request->quickURL('gui2', 'theme_image', 
					array('theme' => $this->getIdString(), 'file' => $matches[1][$i])))
				."')";
			$templateContent = str_replace($matches[0][$i], $replacement, $templateContent);
		}
		
		return $templateContent;
	}
	
	/**
	 * Resolve a type and index into one of our component types
	 * 
	 * @param int $type
	 * @param int $index
	 * @return string
	 * @access protected
	 * @since 5/6/08
	 */
	protected function resolveType ($type, $index) {
		// ** parameter validation
		$rule = ChoiceValidatorRule::getRule(BLANK, HEADING, HEADER, FOOTER, BLOCK, MENU, 
										SUB_MENU, MENU_ITEM_LINK_UNSELECTED,
										MENU_ITEM_LINK_SELECTED, MENU_ITEM_HEADING, OTHER);
		ArgumentValidator::validate($type, $rule, true);
		ArgumentValidator::validate($index, IntegerValidatorRule::getRule(), true);
		
		switch ($type) {
			case BLANK:
			case OTHER:
				return 'Blank';
			
			case BLOCK:
				switch ($index) {
					case 1:
						return 'Block_Background';
					case 2:
						return 'Block_Standard';
					case 3:
						return 'Block_Emphasized';
					default:
						return 'Block_Alert';
				}
			
			case MENU:
				return 'Menu';
			case SUB_MENU:
				return 'Menu_Sub';
			case MENU_ITEM_LINK_UNSELECTED:
				return 'MenuItem_Link_Unselected';
			case MENU_ITEM_LINK_SELECTED:
				return 'MenuItem_Link_Selected';
			case MENU_ITEM_HEADING:
				return 'MenuItem_Heading';
				
			case HEADING:
				switch ($index) {
					case 1:
						return 'Heading_1';
					case 2:
						return 'Heading_2';
					case 3:
						return 'Heading_3';
					default:
						return 'Heading_4';
			}
			
			case HEADER:
				return 'Header';
			case FOOTER:
				return 'Footer';
			
			default:
				throw new InvalidArgumentException("Usuported type, $type.");
		}
	}
}

?>