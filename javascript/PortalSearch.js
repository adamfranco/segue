/**
 * @since 8/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * This is a static class for methods relating to portal-searching of sites.
 * 
 * @since 8/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function PortalSearch () {
	
}
	
	/**
	 * Clear a result
	 * 
	 * @param string id
	 * @param DOMElement displayElement
	 * @return void
	 * @access public
	 * @since 8/11/08
	 */
	PortalSearch.clear = function (id, displayElement) {
		try {
			// Fire off an AJAX request to store the addition in the session.
			var url = Harmoni.quickUrl('portal', 'clear_search', {id: id});
			var req = Harmoni.createRequest();
			if (req) {
				// Set a callback for reloading the list.
				req.onreadystatechange = function () {
					
					// only if req shows 'loaded'
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseText) {
							
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
		
		displayElement.style.display = 'none';
	}

	/**
	 * Submit the search form
	 * 
	 * @param DOMElement form
	 * @return void
	 * @access public
	 * @since 8/11/08
	 */
	PortalSearch.submitForm = function ( form ) {
		// Send off an asynchronous request to do the update and monitor the
		// status in a new centered panel.
		var url = form.action;
		var params = PortalSearch.getFormParams(form);
		
		var statusPanel = new CenteredPanel("Searching...", 200, 200, null, 'search_status');
		statusPanel.cancel.parentNode.removeChild(statusPanel.cancel);
		statusPanel.contentElement.innerHTML = "<img src='" + Harmoni.MYPATH + "/images/loading.gif' alt='Loading...' />";
		
		var req = Harmoni.createRequest();
		if (req) {
			
			// Set a callback for displaying errors.
			req.onreadystatechange = function () {				
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					try {
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseXML) {
							var folderElems = req.responseXML.getElementsByTagName('search');
							if (!folderElems.length)
								throw 'Missing search result.';
							var id = folderElems[0].getAttribute('id');
							if (!id)
								throw 'Missing search result id.';
							window.location = Harmoni.quickUrl('portal', 'list', {folder: encodeURI(id)});
						} else {
							throw "There was a problem retrieving the data:\n" +
								req.statusText;	
						}
					} catch (e) {	
						statusPanel.contentElement.appendChild(document.createElement('br'));
						statusPanel.contentElement.appendChild(document.createTextNode('Search Failed: '));
						statusPanel.contentElement.appendChild(document.createTextNode(e));
						alert(e);
							
						var button = document.createElement('input');
						button.type = 'button';
						button.value = "Continue »";
						button.onclick = function () {
							window.location.reload();
						};
						statusPanel.contentElement.appendChild(document.createElement('br'));
						statusPanel.contentElement.appendChild(document.createElement('br'));
						statusPanel.contentElement.appendChild(button);
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
	 * @since 7/29/08
	 */
	PortalSearch.getFormParams = function (form) {
		var params = '';
		for (var i = 0; i < form.elements.length; i++) {
			var elem = form.elements[i];
			if (elem.name && (elem.type != 'checkbox' || elem.checked)) {
				if (params.length)
					params += '&';
				
				params += elem.name + '=' + encodeURI(elem.value);
			}
		}
		return params;
	}