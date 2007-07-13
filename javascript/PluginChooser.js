/**
 * @since 7/12/07
 * @package segue.plugins
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginChooser.js,v 1.1 2007/07/13 15:31:25 adamfranco Exp $
 */

PluginChooser.prototype = new CenteredPanel();
PluginChooser.prototype.constructor = PluginChooser;
PluginChooser.superclass = CenteredPanel.prototype;

/**
 * The PluginChooser provides a pop-up panel with a list of availible plugins and
 * their descriptions from which the user can choose from when creating new plugins.
 * 
 * @since 7/12/07
 * @package segue.plugins
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginChooser.js,v 1.1 2007/07/13 15:31:25 adamfranco Exp $
 */
function PluginChooser (callingElement, destUrl ) {
	if ( arguments.length > 0 ) {
		this.init(callingElement, destUrl );
	}
}

	/**
	 * Initialise the object
	 * 
	 * @param string organizerId
	 * @param object DOM_Element	callingElement 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	PluginChooser.prototype.init = function ( callingElement, destUrl ) {		
		PluginChooser.superclass.init.call(this, 
								"Choose Content Type",
								15,
								600,
								callingElement,
								'pluginchooser');
		
		this.setDefaults();
		
		this.form = this.contentElement.appendChild(document.createElement('form'));
		this.form.action = destUrl;
		this.form.method= 'post';
		this.form.chooser = this;
		this.form.onsubmit = function () {
			var titleFieldName = Harmoni.fieldName('title', this.chooser.namespace);
			if (this[titleFieldName].value == this.chooser.defaultTitle) {
				alert (this.chooser.titleError);
				return false;
			}
		}
		
		// Title
		this.addTitleInput();
		
		this.pluginContainer = this.form.appendChild(document.createElement('div'));
		this.pluginContainer.innerHTML = "<div class='loading'>loading...</div>";
		this.loadPlugins();
	}
	
	/**
	 * Set some default values
	 * 
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	PluginChooser.prototype.setDefaults = function () {
		this.preTitleText = 'Add a title and choose a type of content to add.';
		this.titleLabel = 'Title: ';
		this.defaultTitle = '(untitled)';
		this.titleError = 'Please enter a title.';
		this.namespace = 'plugin_manager';
	}
	
	/**
	 * Add the title input elements to the form
	 * 
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	PluginChooser.prototype.addTitleInput = function () {
		var container = this.form.appendChild(document.createElement('div'));
		container.appendChild(document.createTextNode(this.preTitleText));
		
		var container = this.form.appendChild(document.createElement('div'));
		container.className = 'title_form';
		container.appendChild(document.createTextNode(this.titleLabel));
		this.title = container.appendChild(document.createElement('input'));
		this.title.type = 'text';
		this.title.name = Harmoni.fieldName('title', this.namespace);
		this.title.value = this.defaultTitle;
		this.title.focus();
	}
	
	/**
	 * Initialize and run the PluginChooser
	 * 
	 * @param object DOM_Element	callingElement 
	 * @param string destUrl
	 * @return void
	 * @access public
	 * @since  7/12/07
	 */
	PluginChooser.run = function ( callingElement, destUrl ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new PluginChooser( callingElement, destUrl );
		}
	}
	
	/**
	 * Load a list of plugins and descriptions
	 * 
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	PluginChooser.prototype.loadPlugins = function () {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('plugin_manager', 'list_plugins', 
// 						{'qualifier_id': this.qualifierId, 'function_id': 'edu.middlebury.authorization.view'}, 
						'plugin_manager');
// 		var newWindow = window.open(url);

		if (req) {
			// Define a variable to point at this object that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var chooser = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						chooser.writePluginList(req.responseXML);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}	
	}
	
	/**
	 * Write the plugin list to the panel
	 * 
	 * @param xmldoc
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	PluginChooser.prototype.writePluginList = function (xmldoc) {
		var errors = xmldoc.getElementsByTagName('error');
		if (errors.length) {
			for (var i = 0; i < errors.length; i++)
				html += "\n<strong>Error: </strong>" + errors[i].text + "<br/>";
			
			this.contentElement.innerHTML = html;
			return;
		}
		
		this.pluginContainer.innerHTML = "";
		var list = this.pluginContainer.appendChild(document.createElement('dl'));
		var pluginTypes = xmldoc.getElementsByTagName('pluginType');
		for (var i = 0; i < pluginTypes.length; i++) {
			var type = pluginTypes.item(i);
			var def = list.appendChild(document.createElement('dt'));
			var desc = list.appendChild(document.createElement('dl'));
			
			var radio = def.appendChild(document.createElement('input'));
			radio.type = 'radio';
			radio.name = Harmoni.fieldName('plugin_type', this.namespace);
			radio.value = type.getAttribute('typeString');
			if (i == 0) 
				radio.checked = 'checked';
			
			def.appendChild(document.createTextNode(
				type.getElementsByTagName('keyword').item(0).firstChild.nodeValue));
				
			var icon = desc.appendChild(document.createElement('img'));
			icon.className = 'icon';
			var iconUrl = type.getElementsByTagName('icon').item(0).firstChild.data;
			icon.src = iconUrl.urlDecodeAmpersands();
			
			desc.appendChild(document.createTextNode(
				type.getElementsByTagName('description').item(0).firstChild.nodeValue));
		}
		
		var container = this.pluginContainer.appendChild(document.createElement('div'));
		container.className = 'submit_buttons';
		var submit = container.appendChild(document.createElement('input'));
		submit.type = 'submit';
		
		this.center();
	}