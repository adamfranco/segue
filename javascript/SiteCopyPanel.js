/**
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

SiteCopyPanel.prototype = new Panel();
SiteCopyPanel.prototype.constructor = SiteCopyPanel;
SiteCopyPanel.superclass = Panel.prototype;

/**
 * A panel for setting options and copying a site.
 * 
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function SiteCopyPanel ( destSlot, srcSiteId, srcTitle, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( destSlot, srcSiteId, srcTitle, positionElement );
	}
}
	
	/**
	 * Initialize and run the SiteCopyPanel
	 * 
	 * @param string destSlot
	 * @param string srcSiteId
	 * @param string srcTitle
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	SiteCopyPanel.run = function (destSlot, srcSiteId, srcTitle, positionElement ) {
		if (positionElement.panel && positionElement.panel.srcSiteId == srcSiteId) {
			positionElement.panel.open();
		} else {
			var tmp = new SiteCopyPanel(destSlot, srcSiteId, srcTitle, positionElement );
		}
	}
	
	/**
	 * Initialize the object
	 * 
	 * @param string destSlot
	 * @param string srcSiteId
	 * @param string srcTitle
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	SiteCopyPanel.prototype.init = function ( destSlot, srcSiteId, srcTitle, positionElement ) {
		this.destSlot = destSlot;
		this.srcSiteId = srcSiteId;
		this.srcTitle = srcTitle;
		
		SiteCopyPanel.superclass.init.call(this, 
								"Copy <br/>&nbsp;&nbsp;&nbsp;&nbsp;'" + srcTitle + "' <br/>to <br/>&nbsp;&nbsp;&nbsp;&nbsp;'" + destSlot + "'",
								15,
								200,
								positionElement);
		
		// Build up the form
		var form = document.createElement('form');
		form.action = Harmoni.quickUrl('portal', 'copy_site');
		form.method = 'POST';
		
		var input = document.createElement('input');
		input.name = 'destSlot';
		input.type = 'hidden';
		input.value = destSlot;
		form.appendChild(input);
		
		var input = document.createElement('input');
		input.name = 'srcSiteId';
		input.type = 'hidden';
		input.value = srcSiteId;
		form.appendChild(input);
		
		var input = document.createElement('input');
		input.name = 'copyPermissions';
		input.type = 'checkbox';
		input.value = 'true';
		input.checked = 'checked';
		form.appendChild(input);
		form.appendChild(document.createTextNode(' Copy Permissions?'));
		form.appendChild(document.createElement('br'));
		
		var input = document.createElement('input');
		input.name = 'copyDiscussions';
		input.type = 'checkbox';
		input.value = 'true';
		input.checked = 'checked';
		form.appendChild(input);
		form.appendChild(document.createTextNode(' Copy Discussion Posts?'));
		form.appendChild(document.createElement('br'));
		form.appendChild(document.createElement('br'));
		
		var submit = document.createElement('input');
		submit.type = 'button';
		submit.value = "Copy »";
		
		var siteCopyPanel = this;
		submit.onclick = function() {
			siteCopyPanel.submitForm(this.form);
			return false;
		}
		
		var div = document.createElement('div');
		div.style.textAlign = 'right';
		div.appendChild(submit);
		form.appendChild(div);
		
		this.contentElement.appendChild(form);
	}
	
	/**
	 * Submit the copy form.
	 * 
	 * @param DOMElement form
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	SiteCopyPanel.prototype.submitForm = function (form) {
		// Send off an asynchronous request to do the update and monitor the
		// status in a new centered panel.
		var url = form.action;
		var params = this.getFormParams(form);
		
		var statusPanel = new CenteredPanel("Copy Status", 400, 800, this.positionElement);
		statusPanel.cancel.parentNode.removeChild(statusPanel.cancel);
		statusPanel.contentElement.innerHTML = "<img src='" + Harmoni.MYPATH + "/images/loading.gif' alt='Loading...' /><br/><span>Copying Site...</span>";
		
		var req = Harmoni.createRequest();
		if (req) {
			
			// Set a callback for displaying errors.
			req.onreadystatechange = function () {
				// Update the status area.
				// IE will throw an error if we try to access responseText
				try {
					if (req.responseText && req.responseText.length) {
						statusPanel.contentElement.innerHTML = req.responseText;
					} else {
//	 					statusPanel.contentElement.innerHTML = "<span style='blink'>Copying Site...</span> Readystate: "  + req.readyState + " Status: " + requ.status + " " + req.statusText;
					}
				} catch (e) {
				}
				
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseText) {
						
					} else {
						alert("There was a problem retrieving the data:\n" +
							req.statusText);
						statusPanel.contentElement.appendChild(document.createElement('br'));
						statusPanel.contentElement.appendChild(document.createTextNode('Copy Failed'));
					}
					
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
	SiteCopyPanel.prototype.getFormParams = function (form) {
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
