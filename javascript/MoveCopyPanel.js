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
function MoveCopyPanel ( destId, destType, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( destId, destType, positionElement );
	}
}

	/**
	 * Initialize the panel
	 * 
	 * @param string destId
	 * @param string destType	Either MenuOrganizer or ContentOrganizer
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.init = function ( destId, destType, positionElement ) {
		if (!destId.match(/^[a-zA-Z0-9_.:-]+$/))
			throw "Invalid destination Id, '" + destId + "'.";
		this.destId = destId;
		
		if (destType != 'MenuOrganizer' && destType != 'FlowOrganizer')
			throw "Invalid destination type, '" + destType + "'. Must be 'MenuOrganizer' or 'ContentOrganizer'.";
		this.destType = destType;
				
		MoveCopyPanel.superclass.init.call(this, 
								"Move/Copy",
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
		var option = this.command.appendChild(document.createElement('option'));
		option.value = 'reference';
		option.innerHTML = 'Reference';
		this.command.value = 'copy';
		// Change the submit label on change.
		this.command.onchange = function () {
			panel.submit.value = this.value + " Checked »";
		}
		this.form.appendChild(this.command);
		this.form.appendChild(document.createTextNode(' \u00a0 \u00a0 '));
		
		// Help Link
		var help = document.createElement('a');
		help.onclick = function() {
			var string = "Copy: Insert copies of the checked blocks/pages here. The originals will not be changed.";
			string += "\n\nMove: Move the checked blocks/pages here. Links to them will now land here in their new location. They will no longer be availible in their old location.";
			string += "\n\nReference: Create references that will display the content of blocks inline or link back to pages in their original locations. The originals will not be changed.";
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
		
		// Removal from selection
		this.form.appendChild(document.createElement('br'));
		this.form.appendChild(document.createTextNode(' After usage: '));
		var select = document.createElement('select');
		select.name = 'remove_after_use';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'remove';
		option.innerHTML = 'Remove From Selection';
		var option = select.appendChild(document.createElement('option'));
		option.value = 'keep';
		option.innerHTML = 'Keep in Selection';
		this.form.appendChild(select);
		this.form.appendChild(document.createTextNode(' \u00a0 \u00a0 '));
		
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
	MoveCopyPanel.run = function ( destId, destType, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new MoveCopyPanel( destId, destType, positionElement );
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
		this.selectionList.innerHTML = '';
		var selection = Segue_Selection.instance();
		
		if (selection.components.length) {
			this.emptySelectionMessage.style.display = 'none';
		} else {
			this.emptySelectionMessage.style.display = 'block';
		}
		
		for (var i = 0; i < selection.components.length; i++) {
			this.selectionList.appendChild(
				this.getListItemForComponent(selection.components[i]));
		}
	}
	
	/**
	 * Answer a list item for the component given
	 * 
	 * @param object siteComponent
	 * @return object DOMElement
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.getListItemForComponent = function (siteComponent) {
		var li = document.createElement('li');
		
		// Checkbox
		var elem = document.createElement('input');
		elem.type = 'checkbox';
		elem.value = siteComponent.id;
		li.appendChild(elem);
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
	 * Check all of the items
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/08
	 */
	MoveCopyPanel.prototype.checkAll = function () {
		var boxes = this.selectionList.getElementsByTagName('input');
		for (var i = 0; i < boxes.length; i++) {
			if (boxes[i].type == 'checkbox')
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
