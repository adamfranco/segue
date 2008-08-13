/**
 * @since 8/4/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

MoveCopyPanel.prototype = new Panel();
MoveCopyPanel.prototype.constructor = Panel;
MoveCopyPanel.superclass = Panel.prototype;

/**
 * This panel provides a UI for moving/copying from the selection to a new location.
 * 
 * @since 8/4/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function MoveCopyPanel ( destId, destType, ancestors, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( destId, destType, ancestors, positionElement );
	}
}

	/**
	 * Initialize the panel
	 * 
	 * @param string destId
	 * @param string destType	Either MenuOrganizer or ContentOrganizer
	 * @param array ancestors	Id-strings of the ancestors of the destination element.
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.init = function ( destId, destType, ancestors, positionElement ) {
		if (!destId.match(/^[a-zA-Z0-9_.:-]+$/))
			throw "Invalid destination Id, '" + destId + "'.";
		this.destId = destId;
		
		if (destType != 'MenuOrganizer' && destType != 'FlowOrganizer')
			throw "Invalid destination type, '" + destType + "'. Must be 'MenuOrganizer' or 'ContentOrganizer'.";
		this.destType = destType;
		
		for (var i = 0; i < ancestors.length; i++) {
			var type = typeof(ancestors[i]);
			if (type != 'string' && type != 'number')
				throw "Ancestor ids must be strings. Found " + type;
		}
		this.ancestors = ancestors;
		
		var helpUrl = Harmoni.quickUrl('help', 'browse_help', {topic: 'Move-Copy'});
		var helpLink = " &nbsp; &nbsp; (<a href='#' onclick=\"var helpWindow = window.open('" + helpUrl + "', 'help', 'width=700,height=600,scrollbars=yes,resizable=yes'); helpWindow.focus(); return false;\">Help</a>)";
				
		MoveCopyPanel.superclass.init.call(this, 
								"Move/Copy" + helpLink,
								50,
								300,
								positionElement,
								'Selection_MoveCopy_Panel');
		
		
		/*********************************************************
		 * build our form
		 *********************************************************/
		var panel = this;
		
		this.form = document.createElement('form');
		this.form.action = Harmoni.quickUrl('selection', 'move_copy', {destId: this.destId});
		this.form.method = "POST";
		// Submit checking.
		this.form.onsubmit = function() {
			try {
				panel.validateForm();
				panel.submitForm();
				return false;
			} catch (e) {
				alert(e);
				return false;
			}
		}
		
		// Command Switching
		this.command = document.createElement('select');
		this.command.name = 'command';
		var option = this.command.appendChild(document.createElement('option'));
		option.value = 'copy';
		option.innerHTML = 'Copy';
		var option = this.command.appendChild(document.createElement('option'));
		option.value = 'move';
		option.innerHTML = 'Move';
// 		var option = this.command.appendChild(document.createElement('option'));
// 		option.value = 'reference';
// 		option.innerHTML = 'Reference';
		this.command.value = 'copy';
		// Change the submit label on change.
		this.command.onchange = function () {
			panel.submit.value = this.options.item(this.selectedIndex).innerHTML + " Checked »";
			
			if (this.value == 'copy') {
				panel.copyPermsDiv.style.display = 'block';
			} else {
				panel.copyPermsDiv.style.display = 'none';
			}
			
			panel.reloadFromSelection();
			
		}
		this.form.appendChild(this.command);
		this.form.appendChild(document.createTextNode(' \u00a0 \u00a0 '));
		
		// Help Link
		var help = document.createElement('a');
		help.onclick = function() {
			var string = "Copy: Insert copies of the checked blocks/pages here. The originals will not be changed.";
			string += "\n\nMove: Move the checked blocks/pages here. Links to them will now land here in their new location. They will no longer be availible in their old location.";
// 			string += "\n\nReference: Create references that will display the content of blocks inline or link back to pages in their original locations. The originals will not be changed.";
			alert(string);
			return false;
		}
		help.innerHTML = '?';
		this.form.appendChild(help);
		this.form.appendChild(document.createTextNode(' \u00a0 \u00a0 '));
		
		// Submit button
		this.submit = document.createElement('input');
		this.submit.type = 'submit';
		this.submit.value = 'Copy Checked »';
		this.form.appendChild(this.submit);
		
		// Copy Permissions/Discussions
		this.copyPermsDiv = this.form.appendChild(document.createElement('div'));
		this.copyPermsDiv.appendChild(document.createTextNode(' Copy Options: '));
		this.copyPermsDiv.appendChild(document.createElement('br'));
		this.copyPermsDiv.appendChild(document.createTextNode(' \u00a0 \u00a0 \u00a0 \u00a0 '));
		this.copyPermsDiv.className = 'copy_options_div';
		
		var select = document.createElement('select');
		select.name = 'copy_permissions';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'true';
		option.innerHTML = 'Copy Permissions';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'false';
		option.innerHTML = 'Remove Permissions';
		select.value = 'false';
		this.copyPermsDiv.appendChild(select);
		
		this.copyPermsDiv.appendChild(document.createElement('br'));
		this.copyPermsDiv.appendChild(document.createTextNode(' \u00a0 \u00a0 \u00a0 \u00a0 '));
		var select = document.createElement('select');
		select.name = 'copy_discussions';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'true';
		option.innerHTML = 'Copy Discussion Posts';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'false';
		option.innerHTML = 'Remove Discussion Posts';
		select.value = 'false';
		this.copyPermsDiv.appendChild(select);
		
		
		// Removal from selection
		var div = this.form.appendChild(document.createElement('div'));
		div.className = 'selection_removal_div';
		div.appendChild(document.createTextNode(' After usage: '));
		div.appendChild(document.createElement('br'));
		div.appendChild(document.createTextNode(' \u00a0 \u00a0 \u00a0 \u00a0 '));
		var select = document.createElement('select');
		select.name = 'remove_after_use';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'remove';
		option.innerHTML = 'Remove From Selection';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'keep';
		option.innerHTML = 'Keep in Selection';
		div.appendChild(select);
// 		this.form.appendChild(document.createTextNode(' \u00a0 \u00a0 '));
		
		// Check All/None
		var div = document.createElement('div');
		div.className = 'All_None';
		var link = document.createElement('a');
		link.innerHTML = "Check All";
		link.onclick = function() {
			panel.checkAll();
			return false;
		}
		div.appendChild(link);
		
		div.appendChild(document.createTextNode(' \u00a0 / \u00a0 '));
		
		var link = document.createElement('a');
		link.innerHTML = "Check None";
		link.onclick = function() {
			panel.checkNone();
			return false;
		}
		div.appendChild(link);
		this.form.appendChild(div);
		
		
		// Item list.
		this.selectionList = document.createElement('ol');
		this.form.appendChild(this.selectionList);
		
		
		// No Selection message.
		this.emptySelectionMessage = document.createElement('p');
		this.emptySelectionMessage.className = 'emptySelectionMessage';
		
		if (this.destType == 'MenuOrganizer')
			this.emptySelectionMessage.innerHTML = "No pages or content blocks are selected. Use the <strong>+ Selection</strong> links to select pages or content blocks so that they can be moved or copied here.";
		else
			this.emptySelectionMessage.innerHTML = "No content blocks are selected. Use the <strong>+ Selection</strong> links to select content blocks so that they can be moved or copied here.";
		this.form.appendChild(this.emptySelectionMessage);
		
		
		this.contentElement.appendChild(this.form);
		
		// Register for updates with the Selection.
		Segue_Selection.instance().attachListener(this);
		
		// Add items from the selection
		this.reloadFromSelection();
		
		// Check All by default
		this.checkAll();
	}
	
	/**
	 * Initialize and run the SiteCopyPanel
	 * 
	 * @param string destSlot
	 * @param string destType	Either MenuOrganizer or ContentOrganizer
	 * @param array ancestors	Id-strings of the ancestors of the destination element.
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	MoveCopyPanel.run = function ( destId, destType, ancestors, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new MoveCopyPanel( destId, destType, ancestors, positionElement );
		}
	}
	
	/**
	 * Methods to call on panel open.
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.onOpen = function () {
		// Register for updates with the Selection.
		Segue_Selection.instance().attachListener(this);
		
		this.reloadFromSelection();
	}
	
	/**
	 * Methods to call on panel close.
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.onClose = function () {
		// unregister for updates with the Selection.
		Segue_Selection.instance().detachListener(this);
	}
	
	/**
	 * Reload our listing from the Selection.
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.reloadFromSelection = function () {
		var checkedIds = this.getCheckedIds();
		this.selectionList.innerHTML = '';
		var selection = Segue_Selection.instance();
		
		if (selection.components.length) {
			this.emptySelectionMessage.style.display = 'none';
		} else {
			this.emptySelectionMessage.style.display = 'block';
		}
		
		for (var i = 0; i < selection.components.length; i++) {
			this.selectionList.appendChild(
				this.getListItemForComponent(selection.components[i], checkedIds));
		}
	}
	
	/**
	 * Answer a list item for the component given
	 * 
	 * @param object siteComponent
	 * @param array checkedIds
	 * @return object DOMElement
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.getListItemForComponent = function (siteComponent, checkedIds) {
		var li = document.createElement('li');
		
		// Checkbox
		var elem = document.createElement('input');
		elem.name = 'sourceIds[]';
		elem.type = 'checkbox';
		elem.value = siteComponent.id;
		li.appendChild(elem);
		
		if (this.destId == siteComponent.id
			|| (this.command.value == 'move' && this.isAncestor(siteComponent))
			|| (this.destType == 'FlowOrganizer' && siteComponent.type != 'Block'))
		{
			elem.disabled = true;
			li.className = 'disabled';
		} else if (checkedIds.elementExists(siteComponent.id)) {
			elem.checked = true;
		}
		
		li.appendChild(document.createTextNode(' '));
			
		// Name
		var elem = li.appendChild(document.createElement('span'));
		elem.innerHTML = siteComponent.displayName;
		elem.className = 'name';
		li.appendChild(document.createTextNode(' '));
		
		// Type
		var elem = li.appendChild(document.createElement('span'));
		switch (siteComponent.type) {
			case 'NavBlock':
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
		
		return li;
	}
	
	/**
	 * Answer true if the siteComponent passed is an ancestor of ours an hence
	 * we cannot move it below as that would create a loop in the hierarchy.
	 * 
	 * @param object siteComponent
	 * @return boolean
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.isAncestor = function (siteComponent) {
		for (var i = 0; i < this.ancestors.length; i++) {
			if (siteComponent.id == this.ancestors[i])
				return true;
		}
		return false;
	}
	
	/**
	 * Check all of the items
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.checkAll = function () {
		var boxes = this.selectionList.getElementsByTagName('input');
		for (var i = 0; i < boxes.length; i++) {
			if (boxes[i].type == 'checkbox' && !boxes[i].disabled)
				boxes[i].checked = 'checked';
		}
	}
	
	/**
	 * Check none of the items
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.checkNone = function () {
		var boxes = this.selectionList.getElementsByTagName('input');
		for (var i = 0; i < boxes.length; i++) {
			if (boxes[i].type == 'checkbox')
				boxes[i].checked = false;
		}
	}
	
	/**
	 * Answer an array of currently checked Ids.
	 * 
	 * @return Array
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.getCheckedIds = function () {
		var checked = new Array();
		var boxes = this.selectionList.getElementsByTagName('input');
		for (var i = 0; i < boxes.length; i++) {
			if (boxes[i].type.toLowerCase() == 'checkbox' && boxes[i].checked)
			{
				checked.push(boxes[i].value);
			}
		}
		
		return checked;
	}
	
	/**
	 * Update the pannel when the Selection has changed
	 * 
	 * @param object Selection
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.update = function (selection) {
		this.reloadFromSelection();
	}
	
	/**
	 * Validate that at least one item is checked before submitting.
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.validateForm = function () {
		if (!this.getCheckedIds().length)
			throw "At least one item must be checked.";
	}
	
	/**
	 * Submit the form and show a status panel.
	 * 
	 * @return void
	 * @access public
	 * @since 8/5/08
	 */
	MoveCopyPanel.prototype.submitForm = function () {
		// Send off an asynchronous request to do the update and monitor the
		// status in a new centered panel.
		var url = this.form.action;
		var params = this.getFormParams(this.form);
		
		var statusPanel = new CenteredPanel("Move/Copy Status", 400, 800, this.positionElement);
		statusPanel.cancel.parentNode.removeChild(statusPanel.cancel);
		switch (this.command.value) {
			case 'move':
				var actionText = 'Moving';
				break;
			case 'copy':
				var actionText = 'Copying';
				break;
			case 'reference':
				var actionText = 'Referencing';
				break;
			default:
				throw "Unknown command, '" + this.command.value + "'.";
		}
		statusPanel.contentElement.innerHTML = "<img src='" + Harmoni.MYPATH + "/images/loading.gif' alt='Loading...' /><br/><span>" + actionText + " selected items...</span>";
		
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
						statusPanel.contentElement.appendChild(document.createTextNode('Move/Copy Failed'));
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
	MoveCopyPanel.prototype.getFormParams = function (form) {
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
