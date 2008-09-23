/**
 * @since 7/31/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

Segue_Selection.prototype = new FixedPanel();
Segue_Selection.prototype.constructor = FixedPanel;
Segue_Selection.superclass = FixedPanel.prototype;

/**
 * The Segue_Selection is a panel that floats at the top of the page and displays
 * the current selection as well as allow interoperation with the server-side selection.
 * 
 * @since 7/31/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function Segue_Selection () {
	this.components = new Array();
	this.listeners = new Array();
	
	Segue_Selection.superclass.init.call(this, 
								'Your Selection',
								{top: '0px', left: '45%'},
								30,
								300,
								'segue_selection_panel');
	
	var panel = this;	// define a variable for panel that will be in the
						// scope of the onclick.
	this.cancel.onclick = function () {panel.toggleContent();}
	this.closeContent();
	this.close();
}

	/**
	 * Answer the instance of the Selection
	 * 
	 * @return object Segue_Selection
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.instance = function () {
		if (!window.segue_selection) {
			window.segue_selection = new Segue_Selection();
		}
		
		return window.segue_selection;
	}
	
	/**
	 * Add or remove a site component to the selection.
	 * 
	 * @param object siteComponent
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.toggleComponent = function (siteComponent) {
		if (this.isInSelection(siteComponent.id)) 
			this.removeComponent(siteComponent);
		else
			this.addComponent(siteComponent);
	}
	
	/**
	 * Add a site component to the selection.
	 * 
	 * @param object siteComponent
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.addComponent = function (siteComponent) {
		try {
			this.loadComponent(siteComponent);
			
			this.notifyListeners();
		
			// Fire off an AJAX request to store the addition in the session.
			var url = Harmoni.quickUrl('selection', 'add', {id: siteComponent.id});
			var req = Harmoni.createRequest();
			if (req) {
				var selection = this;
				// Set a callback for reloading the list.
				req.onreadystatechange = function () {
					
					// only if req shows 'loaded'
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseXML) {
							selection.reloadFromXML(req.responseXML);
						} else {
							alert("There was a problem retrieving the data:\n" +
								req.statusText);
						}
					}
				} 
			
				req.open('GET', url, true);
				req.send(null);
			} else {
				alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
			}
		} catch (e) {
			if (e == 'Already selected')
				return;
			else
				throw e;
		}
	}
	
	/**
	 * remove a site component to the selection.
	 * 
	 * @param object siteComponent
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.removeComponent = function (siteComponent) {
		var newComponents = new Array();
		for (var i = 0; i < this.components.length; i++) {
			if (siteComponent.id != this.components[i].id)
				newComponents.push(this.components[i]);
		}
		this.components = newComponents;
		
		// Try to mark the add-link as deselected.
		var link = document.get_element_by_id("selection_add_link-" + siteComponent.id);
		if (link) {
			link.className = "Selection_add_link_deselected";
		}
		
		this.buildDisplay();
		this.notifyListeners();
		
		// Fire off an AJAX request to store the addition in the session.
		var url = Harmoni.quickUrl('selection', 'remove', {id: siteComponent.id});
		var req = Harmoni.createRequest();
		if (req) {
			var selection = this;
			// Set a callback for reloading the list.
			req.onreadystatechange = function () {
				
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseXML) {
						selection.reloadFromXML(req.responseXML);
					} else {
						alert("There was a problem retrieving the data:\n" +
							req.statusText);
					}
				}
			} 
		
			req.open('GET', url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Reload the listing from an XML response
	 * 
	 * @param object DOMDocument
	 * @return void
	 * @access public
	 * @since 8/1/08
	 */
	Segue_Selection.prototype.reloadFromXML = function (doc) {
		var errors = doc.getElementsByTagName('error');
		if (errors.length) {
			for (var i = 0; i < errors.length; i++)
				alert(errors[i].text);
			return;
		}
		
		delete(this.components);
		this.components = new Array();
		
		var elements = doc.getElementsByTagName('siteComponent');
		for (var i = 0; i < elements.length; i++) {
			if (elements[i].hasAttribute('navType'))
				var navType = elements[i].getAttribute('navType');
			else
				var navType = null;
				
			this.loadComponent({
				id: elements[i].getAttribute('id'),
				type: elements[i].getAttribute('type'),
				navType: navType,
				displayName: elements[i].getAttribute('displayName')
			});
		}
		
		if (this.components.length == 0)
			this.close();
		
		this.buildDisplay();
		this.notifyListeners();
	}
	
	/**
	 * Load a site component to the selection, but to not record it on the server side.
	 * Used for setting initial values on page load.
	 * 
	 * @param object siteComponent
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.loadComponent = function (siteComponent) {
		if (!siteComponent.id)
			throw "SiteComponents must have an id.";
		if (!siteComponent.type)
			throw "SiteComponents must have a type.";
		if (!siteComponent.displayName)
			siteComponent.displayName = 'Untitled';
		
		if (this.isInSelection(siteComponent.id))
			throw 'Already selected';
		
		this.components.push(siteComponent);
		
		// Try to mark the add-link as selected.
		var link = document.get_element_by_id("selection_add_link-" + siteComponent.id);
		if (link) {
			link.className = "Selection_add_link_selected";
		}
		
		this.buildDisplay();
	}
	
	/**
	 * Answer true if the site component is already in the selection.
	 * 
	 * @param string siteComponentId
	 * @return boolean
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.isInSelection = function (siteComponentId) {
		for (var i = 0; i < this.components.length; i++) {
			if (siteComponentId == this.components[i].id)
				return true;
		}
		return false;
	}
	
	/**
	 * Toggle the content open or closed
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.toggleContent = function () {
		if (this.contentElement.style.display == 'none')
			this.openContent();
		else
			this.closeContent();
	}
	
	/**
	 * Close the content
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.closeContent = function () {
		this.contentElement.style.display = 'none';
		this.cancel.innerHTML = 'Open';
	}
	
	/**
	 * open the content
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.openContent = function () {
		this.contentElement.style.display = 'block';
		this.cancel.innerHTML = 'Close';
	}
	
	/**
	 * Additional action to do on close
	 * 
	 * @return void
	 * @access public
	 * @since 8/1/08
	 */
	Segue_Selection.prototype.onClose = function () {
		this.closeContent();
	}
	
	/**
	 * [Re]build the display of the selection contents
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.buildDisplay = function () {
		if (this.components.length < 1) {
			this.close();
			return;
		}
		
		this.titleElement.innerHTML = this.title + ' (' + this.components.length + ' items)';
		
		this.contentElement.innerHTML = '';
		var list = this.contentElement.appendChild(document.createElement('ol'));
		for (var i = 0; i < this.components.length; i++) {
			list.appendChild(this.getListItemForComponent(this.components[i]));
		}
		
		this.open();
		this.center();
	}
	
	/**
	 * Answer a list item for the component given
	 * 
	 * @param object siteComponent
	 * @return object DOMElement
	 * @access public
	 * @since 8/1/08
	 */
	Segue_Selection.prototype.getListItemForComponent = function (siteComponent) {
		var li = document.createElement('li');
			
		// Name
		var elem = li.appendChild(document.createElement('span'));
		elem.innerHTML = siteComponent.displayName;
		elem.className = 'name';
		li.appendChild(document.createTextNode(' '));
		
		// Type
		var elem = li.appendChild(document.createElement('span'));
		switch (siteComponent.type) {
			case 'NavBlock':
				if (siteComponent.navType == 'Page')
					var type = 'Page';
				else if (siteComponent.navType == 'Section')
					var type = 'Section';
				else
					var type = 'Nav. Item';
				break;
			case 'Block':
				var type = 'Content Block';
				break;
			default:
				throw "Unsupported component type: " + siteComponent.type;
			
		}
		elem.innerHTML = '(' + type + ')';
		elem.className = 'type';
		
		// Remove link
		var controls = li.appendChild(document.createElement('span'));
		controls.className = 'controls';
		controls.appendChild(document.createTextNode(' - '));
		var elem = controls.appendChild(document.createElement('a'));
		elem.href = '#';
		elem.innerHTML = 'remove';
		elem.onclick = function() {
			Segue_Selection.instance().removeComponent(siteComponent);
		}
		li.appendChild(document.createTextNode(' '));
		
		return li;
	}
	
	/**
	 * Add a listener to notify when the selection has been changed.
	 * Listeners must implement a update(selection) method
	 * 
	 * @param object listener
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	Segue_Selection.prototype.attachListener = function (listener) {
		this.listeners.push(listener);
	}
	
	/**
	 * Rmove a listener to notify when the selection has been changed.
	 * Listeners must implement a update() method
	 * 
	 * @param object listener
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	Segue_Selection.prototype.detachListener = function (listener) {
		var newListeners = new Array();
		for (var i = 0; i < this.listeners.length; i++) {
			if (listener !== this.listeners[i])
				newListeners.push(this.listeners[i]);
		}
		this.listeners = newListeners;
	}
	
	/**
	 * Noify listeners
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	Segue_Selection.prototype.notifyListeners = function () {
		for (var i = 0; i < this.listeners.length; i++) {
			this.listeners[i].update(this);
		}
	}
