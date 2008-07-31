/**
 * @since 7/31/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

Segue_Selection.prototype = new Panel();
Segue_Selection.prototype.constructor = Panel;
Segue_Selection.superclass = Panel.prototype;

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
	
	Segue_Selection.superclass.init.call(this, 
								'Your Selection',
								'200px',
								'300px',
								document.body,
								'segue_selection_panel');
	this.cancel.style.display = 'none';
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
	 * Add a site component to the selection.
	 * 
	 * @param object siteComponent
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.addComponent = function (siteComponent) {
		this.loadComponent(siteComponent);
		
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
// 						selection.reloadFromXML(req.responseXML);
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
		
		this.components.push(siteComponent);
		
		this.buildDisplay();
	}
	
	/**
	 * [Re]build the display of the selection contents
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	Segue_Selection.prototype.buildDisplay = function () {
		if (!this.components.length) {
			this.close();
		}
		
		this.open();
		// @todo
	}
