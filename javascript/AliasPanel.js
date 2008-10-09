/**
 * @since 10/09/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

AliasPanel.prototype = new Panel();
AliasPanel.prototype.constructor = AliasPanel;
AliasPanel.superclass = Panel.prototype;

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
function AliasPanel ( slot, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( slot, positionElement );
	}
}
	
	/**
	 * Initialize and run the AliasPanel
	 * 
	 * @param string slot
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 10/09/08
	 */
	AliasPanel.run = function (slot, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new AliasPanel(slot, positionElement );
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
	AliasPanel.prototype.init = function ( slot, positionElement ) {
		this.slot = slot;
		
		AliasPanel.superclass.init.call(this, 
								"Make '" + slot + "' an alias of...",
								15,
								300,
								positionElement,
								'alias_panel');
		
		// Build up the form
		var form = document.createElement('form');
		form.action = Harmoni.quickUrl('slots', 'make_alias');
		form.method = 'POST';
		
		var input = document.createElement('input');
		input.name = 'slot';
		input.type = 'hidden';
		input.value = slot;
		form.appendChild(input);
		
		this.targetSlot = document.createElement('input');
		this.targetSlot.name = 'target_slot';
		this.targetSlot.size = '40';
		this.targetSlot.id = this.slot +'_alias_target';
		this.targetSlot.type = 'text';
		form.appendChild(this.targetSlot);
		
		var choices = document.createElement('div');
		choices.id = this.slot +'_alias_target_choices';
		choices.className = 'autocomplete';
		form.appendChild(choices);
		
		form.appendChild(document.createElement('br'));
		form.appendChild(document.createElement('br'));
		
		var submit = document.createElement('input');
		submit.type = 'button';
		submit.value = "Make Alias »";
		
		var aliasPanel = this;
		submit.onclick = function() {
			aliasPanel.submitForm(this.form);
			return false;
		}
		
		var div = document.createElement('div');
		div.style.textAlign = 'right';
		div.appendChild(submit);
		form.appendChild(div);
		
		this.contentElement.appendChild(form);
		
		// Add the autocompleter 
		new Ajax.Autocompleter(
			this.slot +'_alias_target', 
			this.slot +'_alias_target_choices',
			Harmoni.quickUrl('slots', 'alias_targets', {slot: slot}),
			{	paramName: 'search',
				minChars: 2
				
			}
		);
		
	}
	
	/**
	 * Submit the copy form.
	 * 
	 * @param DOMElement form
	 * @return void
	 * @access public
	 * @since 10/09/08
	 */
	AliasPanel.prototype.submitForm = function (form) {
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
	 * @since 10/09/08
	 */
	AliasPanel.prototype.getFormParams = function (form) {
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
