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
class Segue_TextPlugins_Video
	implements Segue_Wiki_TextPlugin 
{

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
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 7/14/08
	 */
	public function generate (array $paramList) {				
		$service = $this->getService($paramList['service']);
		unset($paramList['service']);
		return $service->generate($paramList);
	}
	
	/**
	 * Answer true if this content template supports HTML matching and the getHtmlMatches()
	 * method. If this method returns true, getHtmlMatches() should not throw
	 * an UnimplementedException
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/14/08
	 */
	public function supportsHtmlMatching () {
		return true;
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
				$matches = array_merge($matches, $service->getHtmlMatches($text));
			} catch (Exception $e) {
			}
		}
		return $matches;
	}
	
	/**
	 * Add a new service to those supported and return it.
	 * 
	 * @param object Segue_TextPlugins_Video_Service $service
	 * @return object Segue_TextPlugins_Video_Service 
	 * @access public
	 * @since 7/15/08
	 */
	public function addService (Segue_TextPlugins_Video_Service $service) {
		if (isset($this->services[$service->getName()]))
			throw new InvalidArgumentException("Service '".$service->getName()."' already exists.");
		
		$this->services[$service->getName()] = $service;
		return $service;
	}
	
	/**
	 * Add a new service to those supported and return it.
	 * 
	 * @param string $name
	 * @return object Segue_TextPlugins_Video_Service 
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
class Segue_TextPlugins_Video_Service {
	
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
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function addParam ($paramName, $regex, $defaultValue) {
		if (!preg_match('/^\/.+\/[a-z]*$/', $regex))
			throw new InvalidArgumentException("$regex is not a valid preg_match regular expression.");
		if (!preg_match('/^[a-z0-9_-]+$/', $paramName))
			throw new InvalidArgumentException("$paramName is not a valid param name.");
		if (isset($this->paramRegexes[$paramName]))
			throw new Exception("Param '$paramName' already exists.");
		
		$this->paramRegexes[$paramName] = $regex;
		
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
	 * Set a regular expression that will match against the output embed code
	 * and return an array of matching strings and the parameters that the string
	 * indicates.
	 * 
	 * @param string $regex
	 * @param array $matchParams This array should be a list of 'regex match num' => 'param name'
	 * @return void
	 * @access public
	 * @since 7/15/08
	 */
	public function setHtmlRegex ($regex, array $matchParams) {
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
		$this->htmlMatchRegex = $regex;
		$this->htmlMatchParams = $matchParams;
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
			$output = str_replace('###'.strtoupper($param).'###', $val, $output);
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
		if (!isset($this->htmlMatchRegex))
			throw new ConfigurationErrorException("No matching regular expression set for service, ".$this->name.".");
		
		$results = array();
		preg_match_all($this->htmlMatchRegex, $text, $matches);
		foreach ($matches[0] as $i => $match) {
			$matchParams = array();
			foreach ($this->htmlMatchParams as $ref => $paramName) {
				if ($matches[$ref][$i])
					$matchParams[$paramName] = $matches[$ref][$i];
			}
			$results[$match] = $matchParams;
		}
		return $results;
	}
	
	/*********************************************************
	 * Private methods
	 *********************************************************/
	
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