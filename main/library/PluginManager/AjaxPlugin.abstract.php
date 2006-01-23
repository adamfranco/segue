<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AjaxPlugin.abstract.php,v 1.3 2006/01/23 15:58:59 adamfranco Exp $
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
 * @version $Id: AjaxPlugin.abstract.php,v 1.3 2006/01/23 15:58:59 adamfranco Exp $
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
		
		return "'Javascript:updateAjaxPlugin(\"".$this->getId()."\", \"".$this->_ajaxUrl($parameters)."\")'";
	}
	
	/**
	 * Answer a Javascript command to send the window to a url with the parameters
	 * passed.
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
	function locationSend ( $parameters = array() ) {		
		return $this->url($parameters);
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
			return "<form action='".$this->_url($parameters)."' method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we can
		// override the submit with an AJAX GET submission instead. (if implemented).
		else {
			return "<form onsubmit='submitAjaxPluginForm(\"".$this->getId()."\", this, \"".$this->_ajaxUrl($parameters)."\");' action='Javascript: var nullVal = null;' method='post'>";
		}
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
				for (var i = 0; i < form.elements.length; i++) {
					destination += '&amp;' + escape(form.elements[i].name) + '=' + escape(form.elements[i].value);
				}
				var regex = /&amp;/gi;
				destination = destination.replace(regex, '&');
				
				updateAjaxPlugin(pluginId, destination);
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
						var pluginElement = getElementFromDocument('plugin:' + pluginId);
						
						if (req.readyState > 0 && req.readyState < 4) {
							pluginElement.innerHTML = '<div>Loading...</div>';
						} else {
							pluginElement.innerHTML = '<div>Loaded</div>';
									
							// only if req shows "loaded"
							if (req.readyState == 4) {
								// only if we get a good load should we continue.
								if (req.status == 200) {
									//get the plugin element
									var pluginResponseElement = req.responseXML.firstChild;
									
									// Title
									var elements = pluginResponseElement.getElementsByTagName("title");								
									if (elements.length > 0 && elements[0].textContent)
										var title = elements[0].textContent;
									else
										var title = '';
										
									var elements = pluginResponseElement.getElementsByTagName("markup");
									if (elements.length > 0 && elements[0].textContent)
										var markup = elements[0].textContent;
									else
										var markup = '';
									
									var pluginTitleElement = getElementFromDocument('plugin-title:' + pluginId);
									pluginTitleElement.innerHTML = title;
									pluginElement.innerHTML = markup;
								} else {
									alert("There was a problem retrieving the XML data:\\n" +
										req.statusText);
								}
							}
						}
					}
					
					req.open("GET", destination, true);
					req.send(null);
				}
			}
			
			/**
			 * Answer the element of the document by id.
			 * 
			 * @param string id
			 * @return object The html element
			 * @access public
			 * @since 8/25/05
			 */
			function getElementFromDocument(id) {
				// Gecko, KHTML, Opera, IE6+
				if (document.getElementById) {
					return document.getElementById(id);
				}
				// IE 4-5
				if (document.all) {
					return document.all[id];
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
	 * Answer the markup for this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginMarkup () {
		$markup = "\n<div id='plugin:".$this->getId()."'>\n";
		$markup .= $this->getMarkup();
		$markup .= "\n</div>"; 
		return $markup;
	}
 	
 	/**
	 * Answer the markup for the pluginTitle
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginTitleMarkup () {
		$markup = "\n<div id='plugin-title:".$this->getId()."'>";
		$markup .= $this->getTitle();
		$markup .= "</div>"; 
		return $markup;
	}
	
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