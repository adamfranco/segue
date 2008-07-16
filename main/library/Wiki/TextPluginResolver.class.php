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

require_once(dirname(__FILE__)."/TextPlugin.interface.php");

/**
 * The TextPlugin resolver handles the parsing and conversion of template markup
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
class Segue_Wiki_TextPluginResolver {
		
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 7/14/08
	 */
	public function __construct () {
		$this->textPlugins = array ();
		
		foreach (scandir(MYDIR.'/text_plugins-dist') as $file) {
			if (!preg_match('/^[a-z0-9_-]+\.class\.php$/i', $file))
				continue;
				
			// If we have a local version of the text-plugin, skip the
			// default one.
			if (!file_exists(MYDIR.'/text_plugins-local/'.$file)) 
				$this->addTextPlugin(MYDIR.'/text_plugins-dist/'.$file);
		}
		
		foreach (scandir(MYDIR.'/text_plugins-local') as $file) {
			if (!preg_match('/^[a-z0-9_-]+\.class\.php$/i', $file))
				continue;
			$this->addTextPlugin(MYDIR.'/text_plugins-local/'.$file);
		}
	}
	
	/**
	 * Add a Text plugin to our array
	 * 
	 * @param string $filePath
	 * @return void
	 * @access protected
	 * @since 7/15/08
	 */
	protected function addTextPlugin ($filePath) {
		require_once($filePath);
		$name = basename($filePath, '.class.php');
		$class = 'Segue_TextPlugins_'.$name;
		$plugin = new $class;
		if (!$plugin instanceof Segue_Wiki_TextPlugin)
			throw new Exception("$name must implement the Segue_Wiki_TextPlugin interface.");
		$this->textPlugins[strtolower($name)] = $plugin;
	}
	/**
	 * Answer a text-plugin
	 * 
	 * @param string $name
	 * @return object Segue_Wiki_TextPlugin
	 * @access public
	 * @since 7/14/08
	 */
	public function getTextPlugin ($name) {
		if (!isset($this->textPlugins[$name]))
			throw new UnknownIdException('No Wiki text-plugin named \''.$name.'\' found.', 34563);
		return $this->textPlugins[$name];
	}
	
	/**
	 * Parse the wiki-text and replace wiki markup with HTML markup.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function applyTextPlugins ($text) {
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
				$plugin = $this->getTextPlugin($matches[1][$index]);
				
				// Build the parameter array
				$params = array();
				$paramString = trim($matches[2][$index]);
				preg_match_all($paramRegexp, $paramString, $paramMatches);
				foreach ($paramMatches[1] as $j => $paramName) {
					$params[$paramName] = $paramMatches[2][$j];
				}
				
				// Execute the plugin
				try {
					$text = str_replace($wikiText, $plugin->generate($params), $text);
				} catch (Exception $e) {
					
				}
			} catch (UnknownIdException $e) {
				if ($e->getCode() != 34563)
					throw $e;
			}
		}
		
		return $text;
	}
	
	/**
	 * Go through the text passed and for each text-plugin, try to replace appropriate
	 * HTML with text-plugin markup for any text-plugins which support this.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function unapplyTextPlugins ($text) {
		foreach ($this->textPlugins as $name => $plugin) {
			if ($plugin->supportsHtmlMatching()) {
				try {
					$replacements = $plugin->getHtmlMatches($text);
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