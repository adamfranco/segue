<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AjaxPlugin.abstract.php,v 1.1 2006/01/17 20:12:24 adamfranco Exp $
 */ 

/**
 * Abstract class that all AjaxPlugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AjaxPlugin.abstract.php,v 1.1 2006/01/17 20:12:24 adamfranco Exp $
 */
class AjaxPlugin 
	extends Plugin
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
 	
/*********************************************************
 * Instance Methods - API
 *
 * Use these methods in your plugin as needed, but do not 
 * override them.
 *********************************************************/
	
	/**
	 * Answer a Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
// 		return "'".$this->_ajaxUrl($parameters)."'";
		return "'Javascript:updateAjaxPlugin(\"".$this->getId()."\", \"".$this->_ajaxUrl($parameters)."\")'";
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
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function formStartTagWithAction ( $parameters = array(), $isMultipart = false ) {		
		// If this is a multipart form, we must do a normal 'submit'
		// that includes a page refresh.
		if ($isMultipart) {
			$this->_inAjaxForm = false;
			return "<form action='".$this->_url($parameters)."' method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we can
		// override the submit with an AJAX GET submission instead. (if implemented).
		else {
			$this->_inAjaxForm = true;
			return "<form onsubmit='submitAjaxPluginForm(\"".$this->getId()."\", this, \"".$this->_ajaxUrl($parameters)."\");' action='Javascript: var nullVal = null;' method='post'>";
		}
	}
	
	/**
	 * Answer the form end tag.
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	function formEndTag () {
		$this->_inAjaxForm = false;
		return "</form>";
	}


/*********************************************************
 * Class Methods - Other
 *********************************************************/

	/**
	 * Answer the javascript functions for controlling plugins
	 * 
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function getPluginSystemJavascript () {
		ob_start();
		print<<<END
		
		<script type='text/javascript'>
			/*<![CDATA[*/
			
			function submitAjaxPluginForm( pluginId, form, destination ) {
				alert (form);
				alert (destination);
			}
			
			function updateAjaxPlugin( pluginId, destination ) {
				// branch for native XMLHttpRequest object (Mozilla, Safari, etc)
				if (window.XMLHttpRequest)
					var req = new XMLHttpRequest();
					
				// branch for IE/Windows ActiveX version
				else if (window.ActiveXObject)
					var req = new ActiveXObject("Microsoft.XMLHTTP");
				
				
				if (req) {
					req.onreadystatechange = function () {
						// For some reason IE6 fails if the 'var' is not
						// placed before working.
						var pluginElement = getElementFromDocument('plugin:' + pluginId);
						if (req.readyState > 0 && req.readyState < 4) {
							pluginElement.innerHTML = '<div>Loading...</div>';
						} else {
							pluginElement.innerHTML = '<div>Loaded</div>';
						}
								
						// only if req shows "loaded"
						if (req.readyState == 4) {
							// only if we get a good load should we continue.
							if (req.status == 200) {
								pluginElement.innerHTML = req.responseText;
							} else {
								alert("There was a problem retrieving the XML data:\n" +
									req.statusText);
							}
						}
					}
					
					req.open("GET", destination, true);
					req.send(null);
				}
			}
			
			/*]]>*/
		</script>

END;
		return ob_get_clean();
	}
	

/*********************************************************
 * Instance Methods - Not in API
 *********************************************************/
	
	/**
	 * Answer a Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access private
	 * @since 1/13/06
	 */
	function _url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
		$url =& $this->_baseUrl->deepCopy();
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		return $url->write();
	}
	
	/**
	 * Answer a url for Ajax updating
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	function _ajaxUrl ( $parameters = array() ) {
		$harmoni =& Harmoni::instance();
		$url =& $harmoni->request->mkURL('plugin_manager', 'update_ajax');
		
		$harmoni->request->startNamespace('plugin_manager');
		$url->setValue('plugin_id', $this->getId());
		$harmoni->request->endNamespace();
		
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		
		return $url->write();
	}
}

?>