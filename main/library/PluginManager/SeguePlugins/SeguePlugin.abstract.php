<?php
/**
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePlugin.abstract.php,v 1.4 2008/04/11 20:40:34 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SeguePluginsTemplate.abstract.php");

/**
 * All SeguePlugins SHOULD extend this class or the SegueAjaxPlugin class, and they
 * are REQUIRED to implement the SeguePluginsAPI and the SeguePluginsDriverAPI.
 * 
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePlugin.abstract.php,v 1.4 2008/04/11 20:40:34 adamfranco Exp $
 */
abstract class SeguePlugin
	extends SeguePluginsTemplate
{
		
/*********************************************************
 * Instance Methods - API
 *
 * These are the methods that plugins can and should use 
 * to interact with their environment. 
 * 		Valid additional APIs outside of the methods below:
 *			- OSID interfaces (accessed through Plugin->getManager($managerName))
 *
 * To preserve portability, plugins should not access 
 * other Harmoni APIs, constants, global variables, or
 * the super-globals $_GET, $_POST, $_REQUEST, $_COOKIE.
 *********************************************************/
	
	/**
	 * Answer an href tag string with the array values added as parameters to 
	 * an internal url.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	final public function href ( $parameters = array() ) {
		return "href='".$this->url($parameters)."'";
	}
	
	/**
	 * Answer a Javascript command to send the window to an internal url with the 
	 * parameters passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick=".$this->locationSend(array('item' => 123))
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	final public function locationSend ( $parameters = array() ) {		
		return "window.location=\"".$this->url($parameters)."\"";
	}
	
	/**
	 * Answer a url with the parameters passed, for a form. As well, specify
	 * an optional boolean second parameter, 'isMultipart' if this is a multipart
	 * form with file uploads.
	 *
	 * Use this method, e.g.:
	 *		$this->formTagWithAction(array('item' => 123), false);
	 * instead of the following:
	 * 		"<form action='".$this->url(array('item' => 123))."' method='post>";
	 *
	 * Usage of this method instead of manually writing the form start tag
	 * is optional, but will allow the plugin to more easily be ported to being
	 * an 'AjaxPlugin' later on as the AjaxPlugin redefines the behavior of
	 * this method.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @param string $method post OR get
	 * @param boolean $isMultipart
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	final public function formStartTagWithAction ( $parameters = array(), $method = 'post', 
		$isMultipart = false ) 
	{
		// If this is a multipart form, we must do a normal 'submit'
		// that includes a page refresh.
		if ($isMultipart) {
			return "<form action='".$this->url($parameters)."' method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we
		// don't ned the enctype parameter.
		else {
			if (strtolower($method) == 'get')
				$method = 'get';
			else
				$method = 'post';
			return "<form action='".$this->url($parameters)."' method='".$method."'>";
		}
	}
	
	/**
	 * Answer the markup for this plugin
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended	If true, return the extended version. Default: false.
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	final public function executeAndGetMarkup ( $showControls = false, $extended = false ) {
		return parent::executeAndGetMarkup($showControls, $extended);
	}
	
	/**
	 * Set the update module and action. This method should not be used by plugins.
	 * it is to be used only by plugin users to direct plugins to alternate updating
	 * actions.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 11/8/07
	 */
	public function setUpdateAction ($module, $action) {
		throw new UnimplementedException("setUpdateAction() is only used by AjaxPlugins.");
	}
	
}

?>