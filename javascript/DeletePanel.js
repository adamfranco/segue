/**
 * @since 8/5/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

DeletePanel.prototype = new CenteredPanel();
DeletePanel.prototype.constructor = CenteredPanel;
DeletePanel.superclass = CenteredPanel.prototype;

/**
 * The DeletePanel will display a listing of the children of the node to be deleted
 * to make it clear to the user the scope of their action.
 * 
 * @since 8/5/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function DeletePanel ( siteComponent, returnNode, module, returnAction, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( siteComponent, returnNode, module, returnAction, positionElement );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param object siteComponent
	 * @param string returnNode
	 * @param string module
	 * @param string returnAction
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 8/5/08
	 */
	DeletePanel.prototype.init = function ( siteComponent, returnNode, module, returnAction, positionElement ) {
		if (!siteComponent.id)
			throw "SiteComponents must have an id.";
		if (!siteComponent.type)
			throw "SiteComponents must have a type.";
		if (!siteComponent.displayName)
			siteComponent.displayName = 'Untitled';
			
		this.siteComponent = siteComponent;
		
		DeletePanel.superclass.init.call(this, 
								"Delete <em>" + siteComponent.displayName + "</em>",
								50,
								300,
								positionElement,
								'DeletePanel');
		
		// Info Area
		this.infoArea = this.contentElement.appendChild(document.createElement('div'));
		this.infoArea.className = 'info_area';
		this.infoArea.innerHTML = "<img src='" + Harmoni.MYPATH + "/images/loading.gif' alt='Loading...' /><br/><span>Loading information...</span>";
		
		// Create our form
		this.form = document.createElement('form');
		this.form.action = Harmoni.quickUrl(module, 'deleteComponent', {
				node: this.siteComponent.id, 
				returnNode: returnNode,
				returnAction: returnAction
			});
		this.form.method = "POST";
		
		this.form.onsubmit = function() {
			if (!confirm("Are you absolutely sure that you want to delete this " + siteComponent.type + "?\n\nThis delete is permanent and cannot be undone."))
				return false;
		}
		
		this.contentElement.appendChild(this.form);
		
		// Cancel button
		var panel = this;
		var button = document.createElement('input');
		button.className = 'cancel_button';
		button.type = 'button';
		button.value = "Cancel";
		button.onclick = function () {
			panel.close();
		}
		this.form.appendChild(button);
		
		// Delete button
		var panel = this;
		this.submit = document.createElement('input');
		this.submit.type = 'submit';
		this.submit.className = 'submit_button';
		this.submit.value = "Delete";
		this.submit.disabled = true;
		this.form.appendChild(this.submit);
		
		var spacer = this.form.appendChild(document.createElement('div'));
		spacer.className = 'no_float_spacer';
		
		// Load the info
		var url = Harmoni.quickUrl('ui2', 'get_delete_info', {node: this.siteComponent.id});
		var req = Harmoni.createRequest();
		if (req) {
			// Set a callback for displaying errors.
			req.onreadystatechange = function () {
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseXML) {
						panel.loadInfo(req.responseXML);
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
	 * Initialize and run the DeletePanel
	 * 
	 * @param object siteComponent
	 * @param string returnNode
	 * @param string module
	 * @param string returnAction
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	DeletePanel.run = function ( siteComponent, returnNode, module, returnAction, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new DeletePanel( siteComponent, returnNode, module, returnAction, positionElement );
		}
	}
	
	/**
	 * Load the info about the Node from the xml response
	 * 
	 * @param DOMDocument doc
	 * @return void
	 * @access public
	 * @since 8/5/08
	 */
	DeletePanel.prototype.loadInfo = function (doc) {
		var info = '';
		var elements = doc.getElementsByTagName('siteComponent');
		if (!elements.length)
			throw "Now siteComponent element found";
		var element = elements.item(0);
		
		
		var attr = element.attributes.getNamedItem('sections');
		if (attr)
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + attr.value + ' sections';
		
		var attr = element.attributes.getNamedItem('pages');
		if (attr)
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + attr.value + ' pages';
		
		var attr = element.attributes.getNamedItem('blocks');
		if (attr)
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + attr.value + ' content blocks';
			
		var attr = element.attributes.getNamedItem('posts');
		if (attr)
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + attr.value + ' discussion posts';
		
		if (element.attributes['posts'])
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + element.attributes['posts'].value + ' discussion posts';
		
		var attr = element.attributes.getNamedItem('media');
		if (attr)
			info += '<br/>&nbsp;&nbsp;&nbsp;&nbsp;' + attr.value + ' media files';
		
		
		if (info.length) {
			info = '<em>' + this.siteComponent.displayName + '</em> is a ' + this.siteComponent.type + ' that contains the following items: <br/>' + info;
			info += '<br/><br/>All of these will be deleted if you continue.';
		} else {
			info = '<em>' + this.siteComponent.displayName + '</em> is a ' + this.siteComponent.type + ' that contains no other items. <br/><br/>It will be deleted if you continue.'
		}
		this.infoArea.innerHTML = info;
		this.submit.disabled = false;
	}
