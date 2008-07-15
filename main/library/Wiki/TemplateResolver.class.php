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

require_once(dirname(__FILE__)."/ContentTemplate.interface.php");

/**
 * The template resolver handles the parsing and conversion of template markup
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
class Segue_Wiki_TemplateResolver {
		
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 7/14/08
	 */
	public function __construct () {
		$this->templates = array ();
		
		foreach (scandir(MYDIR.'/content_templates-dist') as $file) {
			// Add each template found.
		}
	}
	
	/**
	 * Answer a template
	 * 
	 * @param string $name
	 * @return object Segue_Wiki_Template
	 * @access public
	 * @since 7/14/08
	 */
	public function getTemplate ($name) {
		if (!isset($this->templates[$name]))
			throw new UnknownIdException('No Wiki template named \''.$name.'\' found.', 34563);
		return $this->templates[$name];
	}
	
	/**
	 * Parse the wiki-text and replace wiki markup with HTML markup.
	 * 
	 * @param string $text
	 * @param optional boolean $onlyTwoWay If true, only templates that can find their
	 *		content in HTML markup and convert it back to wiki markup will be converted.
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function applyTemplates ($text, $onlyTwoWay = false) {
		$regexp = "/

{{	# The opening template tags

	\s*		# optional whitespace
	
	([a-z0-9_-]+)		# template name
	
	\s*		# optional whitespace

	(?: |([^}]+) )?		# A parameter list

}}	# The closing template tags

/xi";

		$paramRegexp = "/

	\s*					# optional whitespace
	
	([a-z0-9_-]+)		# param name
	
	\s*					# optional whitespace
	
	=					# Equals
	
	\s*					# optional whitespace

	([^|]+)				# param value

/xi";

		preg_match_all($regexp, $text, $matches);
		
		// for each wiki template replace it with the HTML version
		foreach ($matches[0] as $index => $wikiText) {
			try {
				$template = $this->getTemplate($matches[1][$index]);
				
				if (!$onlyTwoWay || $template->supportsHtmlMatching()) {
					// Build the parameter array
					$params = array();
					$paramString = trim($matches[2][$index]);
					preg_match_all($paramRegexp, $paramString, $paramMatches);
					foreach ($paramMatches[1] as $j => $paramName) {
						$params[$paramName] = $paramMatches[2];
					}
					
					// Execute the template
		// 			try {
						$text = str_replace($wikiText, $template->generate($params), $text);
		// 			} catch (Exception $e) {
		// 				
		// 			}
				}
			} catch (UnknownIdException $e) {
				if ($e->getCode() != 34563)
					throw $e;
			}
		}
		
		return $text;
	}
	
	/**
	 * Go through the text passed and for each template, try to replace appropriate
	 * HTML with template markup for any templates which support this.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function unapplyTemplates ($text) {
		foreach ($this->templates as $name => $template) {
			if ($template->supportsHtmlMatching()) {
				try {
					$replacements = $template->getHtmlMatches($text);
					foreach ($replacements as $html => $params) {
						$markup = '{{'.$name;
						foreach ($params as $key => $val) {
							$markup .= '|'.$key.'='.$val;
						}
						$markup .= '}}';
						$text = str_replace($html, $markup, $text);
					}
				} catch (UnimplementedException $e) {
				}
			}
		}
		
		return $text;
	}
}

?>