<?php
/**
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/TextTemplate.interface.php");

/**
 * The TextTemplate resolver handles the parsing and conversion of template markup
 * for the Segue wiki system. It handles only 'template' markup, not links.
 * 
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Wiki_TextTemplateResolver {
		
	/**
	 * @var array $textTemplates;  
	 * @access private
	 * @since 7/16/08
	 */
	private $textTemplates;
	
	/**
	 * Load the text templates
	 * 
	 * @return void
	 * @access protected
	 * @since 7/16/08
	 */
	protected function loadTextTemplates () {
		$this->textTemplates = array();
		foreach (scandir(MYDIR.'/text_templates-dist') as $file) {
			if (!preg_match('/^[a-z0-9_-]+\.class\.php$/i', $file))
				continue;
				
			// If we have a local version of the text-template, skip the
			// default one.
			if (!file_exists(MYDIR.'/text_templates-local/'.$file)) 
				$this->addTextTemplate(MYDIR.'/text_templates-dist/'.$file);
		}
		
		foreach (scandir(MYDIR.'/text_templates-local') as $file) {
			if (!preg_match('/^[a-z0-9_-]+\.class\.php$/i', $file))
				continue;
			$this->addTextTemplate(MYDIR.'/text_templates-local/'.$file);
		}
	}
	
	/**
	 * Add a Text template to our array
	 * 
	 * @param string $filePath
	 * @return void
	 * @access protected
	 * @since 7/15/08
	 */
	protected function addTextTemplate ($filePath) {
		require_once($filePath);
		$name = strtolower(basename($filePath, '.class.php'));
		$class = 'Segue_TextTemplates_'.$name;
		$template = new $class;
		
		if (!$template instanceof Segue_Wiki_TextTemplate)
			throw new Exception("$name must implement the Segue_Wiki_TextTemplate interface.");
		
		$this->textTemplates[$name] = $template;
		
		$this->configureTextTemplate($name);
	}
	
	/**
	 * Load any configuration files for the text-template
	 * 
	 * @param string $name
	 * @return void
	 * @access protected
	 * @since 7/16/08
	 */
	protected function configureTextTemplate ($name) {
		$name = strtolower($name);
		
		// Configure the template
		if (file_exists(MYDIR.'/config/text_template-'.$name.'.conf.php'))
			require_once (MYDIR.'/config/text_template-'.$name.'.conf.php');
		else if (file_exists(MYDIR.'/config/text_template-'.$name.'_default.conf.php'))
			require_once (MYDIR.'/config/text_template-'.$name.'_default.conf.php');
	}
	
	/**
	 * Answer a text-template
	 * 
	 * @param string $name
	 * @return object Segue_Wiki_TextTemplate
	 * @access public
	 * @since 7/14/08
	 */
	public function getTextTemplate ($name) {
		if (!isset($this->textTemplates))
			$this->loadTextTemplates();
		if (!isset($this->textTemplates[strtolower($name)]))
			throw new UnknownIdException('No Wiki text-template named \''.$name.'\' found.', 34563);
		return $this->textTemplates[strtolower($name)];
	}
	
	/**
	 * Parse the wiki-text and replace wiki markup with HTML markup.
	 * 
	 * @param string $text
	 * @param optional $editorSafeOnly If true, only templates that are safe for
	 *				working with in a WYSIWIG editor will be applied.
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function applyTextTemplates ($text, $editorSafeOnly = false) {
		$regexp = "/

(<nowiki>)?		# optional nowiki tag to prevent parsing.

{{	# The opening template tags

	\s*		# optional whitespace
	
	([a-z0-9_-]+)		# template name
	
	\s*		# optional whitespace

	(?: |([^}]+) )?		# A parameter list

}}	# The closing template tags

(<\/nowiki>)?	# optional closing nowiki tag to prevent parsing.

/xi";

		$paramRegexp = "/

	\s*					# optional whitespace
	
	([a-z0-9_-]+)		# param name
	
	\s*					# optional whitespace
	
	=					# Equals
	
	\s*					# optional whitespace

	([^|]+)				# param value

/xi";

		WikiResolver::mb_preg_match_all($regexp, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
// 		printpre($matches);
		
		// for each wiki template replace it with the HTML version
		$offsetDiff = 0;
		foreach ($matches as $match) {
			$offset = $match[0][1] + $offsetDiff;
			$wikiText = $match[0][0];
			$templateName = strtolower($match[2][0]);
			if (isset($match[3]))
				$paramString = trim($match[3][0]);
			else
				$paramString = '';
			
			// Ignore markup surrounded by nowiki tags
			if (!strlen($match[1][0]) && (!isset($match[4]) || !strlen($match[4][0]))) {
				try {
					$template = $this->getTextTemplate($templateName);
					if (!$editorSafeOnly || $template->isEditorSafe()) {
					
						// Build the parameter array
						$params = array();
						preg_match_all($paramRegexp, $paramString, $paramMatches);
						foreach ($paramMatches[1] as $j => $paramName) {
							if (!isset($params[$paramName]))
								$params[$paramName] = $paramMatches[2][$j];
							else if (is_array($params[$paramName]))
								$params[$paramName][] = $paramMatches[2][$j];
							else {
								$params[$paramName] = array($params[$paramName]);
								$params[$paramName][] = $paramMatches[2][$j];
							}
						}
						
						// Execute the template
						try {
							$output = $template->generate($params);
	// 						$output .= printpre(htmlentities(print_r($template->getHtmlMatches($output), true)), true);
							
							$offsetDiff = $offsetDiff + mb_strlen($output) - mb_strlen($wikiText);
							$text = substr_replace($text, $output, $offset, mb_strlen($wikiText));
						} catch (Exception $e) {
							print $e->getMessage();
						}
					}
				} catch (UnknownIdException $e) {
					if ($e->getCode() != 34563)
						throw $e;
				}
			} 
			// Remove the nowiki tag from the markup.
			else {
				$output = '{{'.$templateName.$paramString.'}}';
				
				$offsetDiff = $offsetDiff + mb_strlen($output) - mb_strlen($wikiText);
				$text = substr_replace($text, $output, $offset, mb_strlen($wikiText));
			}
		}
		
		return $text;
	}
	
	/**
	 * Parse and replace any text-templates that are safe for use in an WYSIWYG editor 
	 * with HTML markup. This can be used to allow WYSIWG editing of elements that
	 * will later be converted back to text-templates using unapplyTextTemplates().
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 8/20/08
	 */
	public function applyEditorSafeTextTemplates($text) {
		return $this->applyTextTemplates($text, true);
	}
	
	/**
	 * Go through the text passed and for each text-template, try to replace appropriate
	 * HTML with text-template markup for any text-templates which support this.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function unapplyTextTemplates ($text) {
		if (!isset($this->textTemplates))
			$this->loadTextTemplates();
		
		foreach ($this->textTemplates as $name => $template) {
			try {
				$replacements = $template->getHtmlMatches($text);
				foreach ($replacements as $html => $params) {
					$markup = '{{'.$name;
					foreach ($params as $key => $val) {
						if (is_array($val)) {
							foreach ($val as $arrayVal)
								$markup .= '|'.$key.'='.$arrayVal;
						} else
							$markup .= '|'.$key.'='.$val;
					}
					$markup .= '}}';
					$text = str_replace($html, $markup, $text);
				}
			} catch (UnimplementedException $e) {
			}
		}
		
		return $text;
	}
}

?>