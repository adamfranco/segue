<?php
/**
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * Abstract class for setting passing through data from the MiddMedia server to 
 * our Javascript media library client
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
abstract class MiddMediaAction
	extends XmlAction
{
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/13/09
	 */
	public function isAuthorizedToExecute () {
		return true; // Authorization will be done in the SOAP calls themselves.
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	public function __construct () {
		if (!defined('MIDDMEDIA_SERVICE_ID'))
			throw new ConfigurationErrorException('MIDDMEDIA_SERVICE_ID is not defined.');
		if (!defined('MIDDMEDIA_SERVICE_KEY'))
			throw new ConfigurationErrorException('MIDDMEDIA_SERVICE_KEY is not defined.');
		if (!defined('MIDDMEDIA_WSDL_URL'))
			throw new ConfigurationErrorException('MIDDMEDIA_WSDL_URL is not defined.');
		
		if (!class_exists('SoapClient'))  
			throw new ConfigurationErrorException('SOAP not available');
		
		$this->_client = new SoapClient(MIDDMEDIA_WSDL_URL);
		$this->_soapFunctions = array();
		foreach ($this->_client->__getfunctions() as $funcDesc) {
			if (preg_match('/^\w+ (service([a-zA-Z0-9]+))\(.+/', $funcDesc, $matches)) {
				$this->_soapFunctions[lcfirst($matches[2])] = $matches[1];
			}
		}
	}
	
	/**
	 * Pass through calls to the SOAP client and return the result.
	 * 
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @access public
	 * @since 1/13/09
	 */
	public function __call ($method, $args) {
		if (!isset($this->_soapFunctions[$method]))
			throw new Exception('Unknown method '.$method);
		
		// Prepend the arguments with our connection info
		array_unshift($args, $this->getUserId(), MIDDMEDIA_SERVICE_ID, MIDDMEDIA_SERVICE_KEY);
		
		return call_user_func_array(
			array($this->_client, $this->_soapFunctions[$method]),
			$args);
	}
	
	/**
	 * Answer the id of the user to send to MiddTube
	 * 
	 * @return string
	 * @access protected
	 * @since 1/13/09
	 */
	protected function getUserId () {
		if (!isset($this->_userId)) {
			$authN = Services::getService("AuthN");
			$idManager = Services::getService("Id");
			$agentManager = Services::getService("Agent");
			
			$userId = $authN->getFirstUserId();
			
			if ($userId->isEqual($idManager->getId("edu.middlebury.agents.anonymous")))
				throw new PermissionDeniedException('You are not authorized to access MiddMedia');
			
			$agent = $agentManager->getAgent($userId);
			$properties = $agent->getProperties();		
			$username = null;
			while ($properties->hasNext() && !$username) {
				$propertySet = $properties->next();
				$username = $propertySet->getProperty("username");
				if (!$username)
					$username = $propertySet->getProperty("Login");
				if (!$username)
					$username = $propertySet->getProperty("login");
			}
		}
		if (!$username)
			throw new PermissionDeniedException('You are not authorized to access MiddMedia');
		
		return $username;
	}
	
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	final public function execute () {
		$this->start();
		try {
			$this->buildXml();
		} catch (PermissionDeniedException $e) {
			$this->error($e->getMessage());
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e);
			$this->error(get_class($e).": ".$e->getMessage());
		}
		$this->end();
	}
	
	/**
	 * Build the XML content for this action
	 * 
	 * @return void
	 * @access protected
	 * @since 1/14/09
	 */
	abstract protected function buildXml ();
	
}

if ( false === function_exists('lcfirst') ):
    function lcfirst( $str )
    { return (string)(strtolower(substr($str,0,1)).substr($str,1));}
endif; 

?>