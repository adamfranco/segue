/**
 * @since 10/09/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

MigrationPanel.prototype = new Panel();
MigrationPanel.prototype.constructor = MigrationPanel;
MigrationPanel.superclass = Panel.prototype;

/**
 * A panel for creating aliases from an empty slot to another slot.
 * 
 * @since 10/09/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function MigrationPanel ( slot, status, url, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( slot, status, url, positionElement );
	}
}
	
	/**
	 * Initialize and run the MigrationPanel
	 * 
	 * @param string slot
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 10/09/08
	 */
	MigrationPanel.run = function (slot, status, url, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new MigrationPanel(slot, status, url, positionElement );
		}
	}
	
	/**
	 * Initialize the object
	 * 
	 * @param string slot
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 10/09/08
	 */
	MigrationPanel.prototype.init = function ( slot, status, url, positionElement ) {
		this.slot = slot;
		
		var helpUrl = Harmoni.quickUrl('help', 'browse_help', {topic: 'Site Aliases'});
		var helpLink = " &nbsp; &nbsp; (<a href='#' onclick=\"var helpWindow = window.open('" + helpUrl + "', 'help', 'width=700,height=600,scrollbars=yes,resizable=yes'); helpWindow.focus(); return false;\">Help</a>)";
		
		MigrationPanel.superclass.init.call(this, 
								"Migration status for '" + slot + "'",
								10,
								500,
								positionElement,
								'alias_panel');
		
		var migrationPanel = this;
		
		// Build up the form
		var form = document.createElement('form');
		this.form = form;
		form.action = Harmoni.quickUrl('dataport', 'set_migration_status');
		form.method = 'POST';
		
		var input = document.createElement('input');
		input.name = 'slot';
		input.type = 'hidden';
		input.value = slot;
		form.appendChild(input);
		
		// Incomplete
		var row = form.appendChild(document.createElement('div'));
		row.className = 'status_row';
		var input = document.createElement('input');
		input.name = 'status';
		input.type = 'radio';
		input.value = 'incomplete';
		if (status ==  'incomplete') {
			input.checked = true;
		}
		row.appendChild(input);
		
		row.appendChild(document.createTextNode(' Migration is '));
		var span = document.createElement('span');
		span.className = 'status status_incomplete';
		span.innerHTML = 'Incomplete';
		row.appendChild(span);
		row.appendChild(document.createTextNode('. I need to deal with it by either archiving it, migrating it, or deciding that I don\'t want to keep it.'));		
		
		
		// Archive
		var row = form.appendChild(document.createElement('div'));
		row.className = 'status_row';
		var input = document.createElement('input');
		input.name = 'status';
		input.type = 'radio';
		input.value = 'archived';
		if (status == 'archived') {
			input.checked = true;
		}
		row.appendChild(input);

		row.appendChild(document.createTextNode(' I have '));
		var span = document.createElement('span');
		span.className = 'status status_archived';
		span.innerHTML = 'Archived';
		row.appendChild(span);
		row.appendChild(document.createTextNode(' this site.'));
		
		
		// Migrated
		var row = form.appendChild(document.createElement('div'));
		row.className = 'status_row';
		var input = document.createElement('input');
		input.name = 'status';
		input.type = 'radio';
		input.value = 'migrated';
		if (status == 'migrated') {
			input.checked = true;
		}
		row.appendChild(input);
		
		row.appendChild(document.createTextNode(' I have '));
		var span = document.createElement('span');
		span.className = 'status status_migrated';
		span.innerHTML = 'Migrated';
		row.appendChild(span);
		row.appendChild(document.createTextNode(' this site to '));
		var urlInput = document.createElement('input');
		urlInput.name = 'url';
		if (url.length) {
			urlInput.value = url;
		}
		urlInput.onchange = function () {
			MigrationPanel.validateUrl(this);
		}
		row.appendChild(urlInput);
		row.appendChild(document.createTextNode(' (optional URL*)'));		

		
		// Unneeded
		var row = form.appendChild(document.createElement('div'));
		row.className = 'status_row';
		var input = document.createElement('input');
		input.name = 'status';
		input.type = 'radio';
		input.value = 'unneeded';
		if (status == 'unneeded') {
			input.checked = true;
		}
		row.appendChild(input);
		
		row.appendChild(document.createTextNode(' I '));
		var span = document.createElement('span');
		span.className = 'status status_unneeded';
		span.innerHTML = 'No Longer Need';
		row.appendChild(span);
		row.appendChild(document.createTextNode(' this site and don\'t want to archive or migrate it.'));
	
		// Submit		
		var submit = document.createElement('input');
		submit.type = 'button';
		submit.value = "Save";
		
		submit.onclick = function() {
			migrationPanel.submitForm(this.form);
			return false;
		}
		form.onsubmit = function () {
			migrationPanel.submitForm(this);
			return false;
		}
		
		var div = document.createElement('div');
		div.style.textAlign = 'right';
		div.appendChild(submit);
		form.appendChild(div);
		
		this.contentElement.appendChild(form);
		
		var row = this.contentElement.appendChild(document.createElement('div'));
		row.innerHTML = "* Entering an optional URL (e.g. http://blogs.middlebury.edu/mysite/) will automatically redirect all visitors trying to reach the Segue site to the new site. You do not have to enter a new URL, but without it no automatic redirecting is possible.";
	}
	
	MigrationPanel.validateUrl = function (input) {
		// validate the URL
		if (!input.value.length) {
			return true;
		}
		if (!input.value.match(/^https?:\/\/[^\/]+\.[a-z]{2,5}(:[0-9]+)?(\/[^\s]*)?$/)) {
			alert('Please enter a valid URL.');
			input.focus();
			return false;
		} else {
			return true;
		}
	}
	
	MigrationPanel.prototype.getStatusLine = function() {
		var values = this.form.serialize(true);
		var string = '<span class="status status_' + values['status'] + '">';
		switch (values['status']) {
			case 'archived':
				string += 'Archived';
				break;
			case 'migrated':
				string += 'Migrated';
				break;
			case 'unneeded':
				string += 'No Longer Needed';
				break;
			default:
				string += 'Incomplete';
		}
		string += '</span> ';
		
		if (values['status'] == 'migrated' && values['url'].length) {
			string += ' to <a href="' + values['url'] + '">' + values['url'] + '</a> ';
		}
		return string;
	}
	
	/**
	 * Submit the copy form.
	 * 
	 * @param DOMElement form
	 * @return void
	 * @access public
	 * @since 10/09/08
	 */
	MigrationPanel.prototype.submitForm = function (form) {
		// Send off an asynchronous request to do the update and monitor the
		// status in a new centered panel.
		var url = form.action;
		var params = this.getFormParams(form);
		
		var migrationPanel = this;
		
		var req = Harmoni.createRequest();
		if (req) {
			
			// Set a callback for displaying errors.
			req.onreadystatechange = function () {
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseText) {
						var statusLine = migrationPanel.positionElement.previousSibling;
						statusLine.innerHTML = migrationPanel.getStatusLine();
						migrationPanel.close();
					} else {
						alert("There was a problem retrieving the data:\n" +
							req.statusText);
					}
				}
			} 
		
			req.open('POST', url, true);
			req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
// 			req.setRequestHeader("Content-length", params.length);
// 			req.setRequestHeader("Connection", "close");
			req.send(params);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Gather the elements of the form and combine them into a post string.
	 * 
	 * @param DOMElement form
	 * @return string
	 * @access public
	 * @since 10/09/08
	 */
	MigrationPanel.prototype.getFormParams = function (form) {
		var params = '';
		for (var i = 0; i < form.elements.length; i++) {
			var elem = form.elements[i];
			if (elem.name && (elem.type != 'radio' || elem.checked)) {
				if (params.length)
					params += '&';
				
				params += elem.name + '=' + encodeURI(elem.value);
			}
		}
		return params;
	}
