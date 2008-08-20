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

/**
 * This inteface defines methods needed for 'content templates', Segue's way of 
 * supporting pluggable strings in HTML.
 * 
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_video
	implements Segue_Wiki_TextTemplate 
{
	
	/**
	 * Answer true if this text template is safe for inclusion and editing with
	 * the WYSIWYG HTML editor. If true, users will not see the text template 
	 * markup in the editor, but rather the generated code. This should really
	 * only be used for templates that work with HTML generated directly by the editor.
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/20/08
	 */
	public function isEditorSafe () {
		return false;
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function __construct () {
		$this->services = array();
	}
	
	/**
	 * Answer a block of HTML text that describes this template and its parameters.
	 * Use an h4 tag for the template name.
	 * Use a definition list for parameters. 
	 *
	 * Example:
	 *
	 * <div>
	 *		<h4>video</h4>
	 *		<p>This template will allow the embedding of Flash Video from a variety
	 *		of sources. These sources must be configured by the Segue administrator
	 *		if they are not included in the default configuration.</p>
	 * 		<h4>Parameters:</h4>
	 *		<dl>
	 *			<dt>service<dt>
	 *			<dd>The service at which this video is hosted -- all lowercase.
	 *			Examples: youtube, youtube_playlist, google, vimeo, hulu</dd>
	 *			<dt>id</dt>
	 *			<dd>The Id of the video, the specific form of this dependent on the
	 *			service, but generally this can be found in the URL for flash-video
	 *			file. Example for YouTube: s13dLaTIHSg</dd>
	 *			<dt>width</dt>
	 *			<dd>The integer width of the player in pixels. Example: 325</dd>
	 *			<dt>height</dt>
	 *			<dd>The integer height of the player in pixels. Example: 250</dd>
	 *		</dl>
	 *		<h4>Example Usage:</h4>
	 *		<p>{{video|service=youtube|id=s13dLaTIHSg|width=425|height=344}}</p>
	 *		<p>Note: If you paste the embed code from a supported service into
	 *		a text block, it will automatically be converted into the template markup
	 *		when saved</p>
	 * </div>
	 * 
	 * 
	 * @return string
	 * @access public
	 * @since 7/16/08
	 */
	public function getDescriptionHtml () {
		return _(
"<div>
	<h4>video</h4>
	<p>This template will allow the embedding of Flash Video from a variety of sources. These sources must be configured by the Segue administrator if they are not included in the default configuration.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt>service<dt>
		<dd>The service at which this video is hosted -- all lowercase. Examples: youtube, youtube_playlist, google, vimeo, hulu</dd>
		<dt>id</dt>
		<dd>The Id of the video, the specific form of this dependent on the service, but generally this can be found in the URL for flash-video file. Example for YouTube: s13dLaTIHSg</dd>
		<dt>width</dt>
		<dd>The integer width of the player in pixels. Example: 325</dd>
		<dt>height</dt>
		<dd>The integer height of the player in pixels. Example: 250</dd>
	</dl>
	<h4>Example Usage:</h4>
	<p>{{video|service=youtube|id=s13dLaTIHSg|width=425|height=344}}</p>
	<p>Note: If you paste the embed code from a supported service into a text block, it will automatically be converted into the template markup when saved</p>
</div>");
	}
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 7/14/08
	 */
	public function generate (array $paramList) {
		if (!isset($paramList['service']))
			throw new OperationFailedException('No service specified in param list.');
		
		$service = $this->getService($paramList['service']);
		unset($paramList['service']);
		
// 		ob_start();
// 		print $service->generate($paramList);
// 		print "<hr/>";
// 		foreach ($this->getHtmlMatches($service->generate($paramList)) as $string => $params) {
// 			printpre(htmlentities($string)); printpre($params); printpre("\n\n");
// 		}
// 		return ob_get_clean();
		
		return $service->generate($paramList);
	}
	
	/**
	 * Answer an array of strings in the HTML that look like this template's output
	 * and list of parameters that the HTML corresponds to. e.g:
	 * 	array(
	 *		"<img src='http://www.example.net/test.jpg' width='350px'/>" 
	 *				=> array (	'server'	=> 'www.example.net',
	 *							'file'		=> 'test.jp',
	 * 							'width'		=> '350px'))
	 * 
	 * This method may throw an UnimplementedException if this is not supported.
	 *
	 * @param string $text
	 * @return array
	 * @access public
	 * @since 7/14/08
	 */
	public function getHtmlMatches ($text) {
		$matches = array();
		foreach ($this->services as $service) {
			try {
				$serviceMatches = $service->getHtmlMatches($text);
				foreach ($serviceMatches as $string => $params) {
					if (!isset($matches[$string])) {
						$newParams = array('service' => $service->getName());
						$matches[$string] = array_merge($newParams, $params);
					}
				}
			} catch (Exception $e) {
			}
		}
		return $matches;
	}
	
	/**
	 * Add a new service to those supported and return it.
	 * 
	 * @param object Segue_TextTemplates_Video_Service $service
	 * @return object Segue_TextTemplates_Video_Service 
	 * @access public
	 * @since 7/15/08
	 */
	public function addService (Segue_TextTemplates_Video_Service $service) {
		if (isset($this->services[$service->getName()]))
			throw new InvalidArgumentException("Service '".$service->getName()."' already exists.");
		
		$this->services[$service->getName()] = $service;
		return $service;
	}
	
	/**
	 * Add a new service to those supported and return it.
	 * 
	 * @param string $name
	 * @return object Segue_TextTemplates_Video_Service 
	 * @access public
	 * @since 7/15/08
	 */
	public function getService ($name) {
		if (!isset($this->services[$name]))
			throw new InvalidArgumentException("Service '".$name."' does not exist.");
		
		return $this->services[$name];
	}
}

/**
 * Service objects contain a configuration of a service and its output.
 * 
 * @since 7/15/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_Video_Service {
	
	/**
	 * @var string $name;  
	 * @access private
	 * @since 7/15/08
	 */
	private $name;
	
	/**
	 * @var array $defaults;  
	 * @access private
	 * @since 7/15/08
	 */
	private $defaults;
	
	/**
	 * @var array $paramRegexes;  
	 * @access private
	 * @since 7/15/08
	 */
	private $paramRegexes;
	
	/**
	 * @var string $targetHtml;  
	 * @access private
	 * @since 7/15/08
	 */
	private $targetHtml;
	
	/**
	 * Constuctor
	 * 
	 * @param string $name The name of the service. All lowercase, letters, numbers, and underscores.
	 * @return voi
	 * @access public
	 * @since 7/15/08
	 */
	public function __construct ($name, $targetHtml) {
		if (!preg_match('/^[a-z0-9_-]+$/', $name))
			throw new InvalidArgumentException("$name is not a valid service name.");
		$this->name = $name;
		
		$this->defaults = array(
				'width' => '300',
				'height' => '300',
			);
		
		$this->paramRegexes = array(
				'id'		=> '/^[a-z0-9_-]+$/i',
				'width'		=> '/^[0-9]+$/',
				'height'	=> '/^[0-9]+$/'
			);
		
		if (!preg_match('/###ID###/', $targetHtml))
			throw new InvalidArgumentException('targetHtml is missing the required ###ID### placeholder');
		$this->targetHtml = $targetHtml;
		
		$this->preText = array();
		$this->postText = array();
	}
	
	/**
	 * Answer the service name
	 * 
	 * @return string
	 * @access public
	 * @since 7/15/08
	 */
	public function getName () {
		return $this->name;
	}
	
	/**
	 * Add a new parameter that can be replaced in the target Html
	 * Uses preg_match syntax
	 * 
	 * @param string $paramName
	 * @param string $regex
	 * @param string $defaultValue
	 * @param optional string $pre Text to add before the parameter, only if it has a value.
	 * @param optional string $post Text to add after the parameter, only if it has a value.
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function addParam ($paramName, $regex, $defaultValue, $pre='', $post='') {
		if (!preg_match('/^\/.+\/[a-z]*$/', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		if (!preg_match('/^[a-z0-9_-]+$/', $paramName))
			throw new InvalidArgumentException("$paramName is not a valid param name.");
		if (isset($this->paramRegexes[$paramName]))
			throw new Exception("Param '$paramName' already exists.");
		
		$this->paramRegexes[$paramName] = $regex;
		
		$this->preText[$paramName] = $pre;
		$this->postText[$paramName] = $post;
		
		
		$this->setDefaultValue($paramName, $defaultValue);
	}
	
	/**
	 * Set the regular expression to use when validating parameters.
	 * Uses preg_match syntax
	 * 
	 * @param string $paramName
	 * @param string $regex
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setParamRegex ($paramName, $regex) {
		if (!preg_match('/^\/.+\/[a-z]*$/', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		if (!preg_match('/^[a-z0-9_-]+$/', $paramName))
			throw new InvalidArgumentException("$paramName is not a valid param name.");
		
		if (!isset($this->paramRegexes[$paramName]))
			throw new Exception("Unknown param name '$paramName'.");
		
		$this->paramRegexes[$paramName] = $regex;
	}
	
	/**
	 * Set the default value for a param
	 * 
	 * @param string $paramName
	 * @param string $defaultValue
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setDefaultValue ($paramName, $defaultValue) {
		if (!preg_match('/^[a-z0-9_-]+$/', $paramName))
			throw new InvalidArgumentException("$paramName is not a valid param name.");
		
		if (!isset($this->paramRegexes[$paramName]))
			throw new Exception("Unknown param name '$paramName'.");
		
		$this->validateParam($paramName, $defaultValue);
		
		$this->defaults[$paramName] = $defaultValue;
	}
	
	/**
	 * Set a regular expression that will match against a url in the embed code
	 * and to validate the flash video player application being accessed
	 *
	 * This expression will only be run against <object></object>, <embed></embed>, and/or
	 * <object><embed></embed></object> blocks where the type is
	 * application/x-shockwave-flash, so there is no need to match the surrounding tags.
	 *
	 * 
	 * @param string $regex
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setHtmlPlayerRegex ($regex) {
		if (!preg_match('/^\/.+\/[a-z]*$/sm', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		
		$this->htmlPlayerRegex = $regex;
	}
	
	/**
	 * Set a regular expression that will match against a url in the embed code
	 * and select the id of the video as its first subpattern. This regex will only be
	 * run on embed blocks that match the player regular expression set with 
	 * setHtmlPlayerRegex.
	 *
	 * This expression will only be run against <object></object>, <embed></embed>, and/or
	 * <object><embed></embed></object> blocks where the type is
	 * application/x-shockwave-flash, so there is no need to match the surrounding tags.
	 *
	 * 
	 * @param string $regex
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setHtmlIdRegex ($regex) {
		if (!preg_match('/^\/.+\/[a-z]*$/sm', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		
		$this->htmlIdRegex = $regex;
	}
	
	/**
	 * Set a regular expression that will match against the output embed code
	 * and return an array of matching strings and the parameters that the string
	 * indicates.
	 *
	 * This expression will only be run against <object></object>, <embed></embed>, and/or
	 * <object><embed></embed></object> blocks where the type is
	 * application/x-shockwave-flash, so there is no need to match the surrounding tags.
	 *
	 * These parameters are to be in addition to the URL matching -- specified with the
	 * setHtmlIdRegex() method -- and the width and height parameters which are
	 * automatically searched for.
	 * 
	 * @param string $regex
	 * @param array $matchParams This array should be a list of 'regex match num' => 'param name'
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setHtmlParamsRegex ($regex, array $matchParams) {
		if (!preg_match('/^\/.+\/[a-z]*$/sm', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		foreach ($matchParams as $key => $name) {
			if (!is_int($key))
				throw new InvalidArgumentException("$key must be an integer.");
			if (!preg_match('/^[a-z0-9_-]+$/', $name))
				throw new InvalidArgumentException("$name is not a valid param name.");
		}
		
		if (!count($matchParams))
			throw new InvalidArgumentException("At least one match parameter must be specified.");
		$this->htmlParamsRegex = $regex;
		$this->htmlParamsParams = $matchParams;
	}
	
	/*********************************************************
	 * Output methods
	 *********************************************************/
	
	/**
	 * Generate the target HTML with a given set of parameters
	 * 
	 * @param array $params
	 * @return string
	 * @access public
	 * @since 7/15/08
	 */
	public function generate (array $params) {		
		// Strip out any invalid parameters
		foreach ($params as $name => $val) {
			try {
				$this->validateParam($name, $val);
			} catch (InvalidArgumentException $e) {
				unset($params[$name]);
			}
		}
		
		// Add in any missing defaults
		foreach ($this->defaults as $name => $val) {
			if (!isset($params[$name]))
				$params[$name] = $val;
		}
		
		// Validate the whole array, should validate always.
		$this->validateParams($params);
		
		// Replace placeholders with our params
		$output = $this->targetHtml;
		foreach ($params as $param => $val) {
			$text = $val;
			if (isset($this->preText[$param]) && $val)
				$text = $this->preText[$param].$text;
			if (isset($this->postText[$param]) && $val)
				$text = $text.$this->postText[$param];
			
			$output = str_replace('###'.strtoupper($param).'###', $text, $output);
		}
		
		return $output;
	}
	
	/**
	 * Answer an array of strings in the HTML that look like this template's output
	 * and list of parameters that the HTML corresponds to. e.g:
	 * 	array(
	 *		"<img src='http://www.example.net/test.jpg' width='350px'/>" 
	 *				=> array (	'server'	=> 'www.example.net',
	 *							'file'		=> 'test.jp',
	 * 							'width'		=> '350px'))
	 * 
	 * This method may throw an UnimplementedException if this is not supported.
	 *
	 * @param string $text
	 * @return array
	 * @access public
	 * @since 7/14/08
	 */
	public function getHtmlMatches ($text) {
		if (!isset($this->htmlPlayerRegex))
			throw new ConfigurationErrorException("No player-matching regular expression set for service, ".$this->name.". Set a player-matching regular expression with setHtmlPlayerRegex(\$regex)");
		
		if (!isset($this->htmlIdRegex))
			throw new ConfigurationErrorException("No Id-matching regular expression set for service, ".$this->name.". Set an Id-matching regular expression with setHtmlIdRegex(\$regex)");
		
		$regex = '/

<object .* type=[\'"]application\/x-shockwave-flash[\'"] .* <\/object>
| <embed .* type=[\'"]application\/x-shockwave-flash[\'"] .* <\/embed>
		
		/ix';
		
		// Go through each flash object or embed tag and try to match the url
		// against ours.
		$results = array();
		preg_match_all($regex, $text, $matches);
		foreach ($matches[0] as $i => $match) {
// 			printpre("working on: ".htmlentities($match));
			try {
				$matchParams = array();
				$matchParams['id'] = $this->getIdFromHtml($match);

				// If the url/id is valid, try searching for the rest of our params
				try {
					$matchParams['width'] = $this->getWidthFromHtml($match);
				} catch (Exception $e) {}
				try {
					$matchParams['height'] = $this->getHeightFromHtml($match);
				} catch (Exception $e) {}
				
				try {
					$additionalParams = $this->getAdditionalParamsFromHtml($match);
					if (count($additionalParams))
						$matchParams = array_merge($matchParams, $additionalParams);
				} catch (Exception $e) {}
				
				$results[$match] = $matchParams;
			} catch (Exception $e) {
			}
		}
		return $results;
	}
	
	/*********************************************************
	 * Private methods
	 *********************************************************/
	
	/**
	 * Answer a video id from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 7/15/08
	 */
	protected function getIdFromHtml ($embedHtml) {
		if (!isset($this->htmlPlayerRegex))
			throw new ConfigurationErrorException("No player-matching regular expression set for service, ".$this->name.". Set a player-matching regular expression with setHtmlPlayerRegex(\$regex)");
		
		if (!isset($this->htmlIdRegex))
			throw new ConfigurationErrorException("No Id-matching regular expression set for service, ".$this->name.". Set an Id-matching regular expression with setHtmlIdRegex(\$regex)");
		
		if (!preg_match($this->htmlPlayerRegex, $embedHtml))
			throw new OperationFailedException("Did not match embed code against ".$this->htmlPlayerRegex." for service ".$this->name.".");
			
		if (!preg_match($this->htmlIdRegex, $embedHtml, $matches))
			throw new OperationFailedException("Did not match the embed code against ".$this->htmlIdRegex." for service ".$this->name.".");
		
		if (!isset($matches[1]))
			throw new ConfigurationErrorException("Url-matching regular expression for service, ".$this->name." does not contain any subpatterns. The ID match must be in the first subpattern contained in parentheses.");
		
		return $matches[1];
	}
	
	/**
	 * Answer a width from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 7/15/08
	 */
	protected function getWidthFromHtml ($embedHtml) {
		$regex = '/

# Width Attributes
width=[\'"]([0-9]+)(?: px)?[\'"]

# style-based width
| 
style=[\'"]		# Attribute start
	[^\'"]*		# other style properties
	width:\s?([0-9]+)(?: px)?
	[^\'"]*		# other style properties
[\'"]			# Attribute end
		
		/ix';
		
		if (!preg_match($regex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match width against ".$regex." for service ".$this->name.".");
		
		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match width against ".$regex." for service ".$this->name.".");
	}
	
	/**
	 * Answer a height from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 7/15/08
	 */
	protected function getHeightFromHtml ($embedHtml) {
		$regex = '/

# Height Attributes
height=[\'"]([0-9]+)(?: px)?[\'"]

# style-based height
| 
style=[\'"]		# Attribute start
	[^\'"]*		# other style properties
	height:\s?([0-9]+)(?: px)?
	[^\'"]*		# other style properties
[\'"]			# Attribute end
		
		/ix';
		
		if (!preg_match($regex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match height against ".$regex." for service ".$this->name.".");
		
		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match height against ".$regex." for service ".$this->name.".");
	}
	
	/**
	 * Answer an array of additional params found in the HTML
	 * 
	 * @param string $embedHtml
	 * @return array
	 * @access protected
	 * @since 7/17/08
	 */
	protected function getAdditionalParamsFromHtml ($embedHtml) {
		if (!isset($this->htmlParamsRegex))
			throw new OperationFailedException('No additional params regex supplied');
		
		if (!preg_match($this->htmlParamsRegex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match additional params against ".$this->htmlParamsRegex." for service ".$this->name.".");
		
		$params = array();
		foreach ($this->htmlParamsParams as $i => $name) {
			if (strlen($matches[$i]))
				$params[$name] = $matches[$i];
		}
		return $params;
	}
	
	/**
	 * Validate an array of parameters
	 * 
	 * @param array $params
	 * @return void
	 * @access protected
	 * @since 7/15/08
	 */
	protected function validateParams (array $params) {
		foreach ($params as $name => $val)
			$this->validateParam($name, $val);
		foreach ($this->paramRegexes as $name => $val) {
			if (!isset($params[$name]))
				throw new InvalidArgumentException("Missing param name '$name'.");
		}
		return true;
	}
	
	/**
	 * Validiate a parameter
	 * 
	 * @param string $paramName
	 * @param string $value
	 * @return void
	 * @access protected
	 * @since 7/15/08
	 */
	protected function validateParam ($paramName, $value) {
		if (!isset($this->paramRegexes[$paramName]))
			throw new InvalidArgumentException("Unknown param name '$paramName'.");
		if (!preg_match($this->paramRegexes[$paramName], $value))
			throw new InvalidArgumentException("Param value '$value' doesn't match regex ".$this->paramRegexes[$paramName]);
		
		return true;
	}
}

?>