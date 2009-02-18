/**
 * @since 2/5/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

SiteMemberPanel.prototype = new CenteredPanel();
SiteMemberPanel.prototype.constructor = SiteMemberPanel;
SiteMemberPanel.superclass = CenteredPanel.prototype;

/**
 * This is a panel for allowing users to chose site members during the site-creation
 * step. Site members will be inserted into a field as a URL-encoded get string with
 * the keys the agent ids, and with the values, the agent display names.
 *
 * 	Example: 1111342=Adam%20Franco&348572=Bob%20Jones
 * 
 * @since 2/5/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function SiteMemberPanel ( callingElement, writableField, slot ) {
	if ( arguments.length > 0 ) {
		this.init( callingElement, writableField, slot );
	}
}

	/**
	 * Initialize our panel
	 * 
	 * @param DOM_Element callingElement
	 * @param DOM_Element writableField A hidden field for writing our results.
	 * @return void
	 * @access public
	 * @since 2/5/09
	 */
	SiteMemberPanel.prototype.init = function ( callingElement, writableField, slot ) {
		SiteMemberPanel.superclass.init.call(this,
									"Add Members to this Site",
									400,
									600,
									callingElement,
									'site_members_panel');
		this.writableField = writableField;
		this.slot = slot;
		
		this.mainElement.style.minHeight = this.height + 'px';

		this.members = this.decodeValue(this.writableField.value);
		
// 		console.log(this.members);
		
	// The search and add form
		this.addForm = this.contentElement.appendChild(document.createElement('form'));
		this.addForm.action = '#';
		this.addForm.onsubmit = function () {return false;};
		
		this.newUserField = this.addForm.appendChild(document.createElement('input'));
		this.newUserField.type = 'text';
		this.newUserField.id = 'autocomplete';
		this.newUserField.name = 'new_user';
		this.newUserField.size = 50;
		
		this.addForm.appendChild(document.createTextNode(' '));
		
		var loadingIndicator = this.addForm.appendChild(document.createElement('img'));
		loadingIndicator.id = 'indicator1';
		loadingIndicator.style.display = 'none';
		loadingIndicator.src = Harmoni.MYPATH + '/images/loading.gif';
		loadingIndicator.style.height = '20px';
		loadingIndicator.style.verticalAlign = 'middle';
		
		var note = this.addForm.appendChild(document.createElement('em'));
		note.innerHTML = '<br/> Start typing a name to search, choose a result to add.';
		
		var choices = this.addForm.appendChild(document.createElement('div'));
		choices.id = "autocomplete_choices";
		choices.className = 'autocomplete';
		
		var panel = this;
		new Ajax.Autocompleter("autocomplete", "autocomplete_choices", Harmoni.quickUrl("ui2", "add_site_search_agents", {slot: this.slot}), {
		  paramName: "query", 
		  minChars: 3, 
		  updateElement: function (li) {
		  	panel.members[li.id] = li.firstChild.nodeValue;
		  	panel.newUserField.value = '';
		  	panel.printMembers();
		  }, 
		  indicator: "indicator1"
		});
		
		this.newUserField.focus();
		

	// The listing
		this.listing = this.contentElement.appendChild(document.createElement('ul'));
		
		this.printMembers();		
	}
	
	/**
	 * Write our current state to the hidden field
	 * 
	 * @return void
	 * @access public
	 * @since 2/6/09
	 */
	SiteMemberPanel.prototype.onClose = function () {
		SiteMemberPanel.superclass.onClose.call(this);
		
// 		console.log(this.encodeValue(this.members));
		this.writableField.value = this.encodeValue(this.members);
	}
	
	/**
	 * Actions to take on opening the panel
	 * 
	 * @return void
	 * @access public
	 * @since 2/17/09
	 */
	SiteMemberPanel.prototype.onOpen = function () {
		SiteMemberPanel.superclass.onOpen.call(this);
		
		this.newUserField.focus();
	}
	
	/**
	 * Initialize and run the panel
	 * 
	 * @param DOM_Element callingElement
	 * @param DOM_Element writableField A hidden field for writing our results.
	 * @return void
	 * @access public
	 * @since 2/6/09
	 */
	SiteMemberPanel.run = function ( callingElement, writableField, slot ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new SiteMemberPanel( callingElement, writableField, slot );
		}
	}
	
	/**
	 * Decode a value
	 * 
	 * @param string value
	 * @return object a hash of ids and displayNames
	 * @access public
	 * @since 2/5/09
	 */
	SiteMemberPanel.prototype.decodeValue = function (value) {
		var hash = {};
		var regex = /(?:&|&amp;)?([^=]+)=([^&]+)/g;
		var match;
		while (match = regex.exec(value)) {
			hash[unescape(match[1])] = unescape(match[2]);
		}
		return hash;
	}
	
	/**
	 * Encode a value
	 * 
	 * @param object hash
	 * @return string
	 * @access public
	 * @since 2/6/09
	 */
	SiteMemberPanel.prototype.encodeValue = function (hash) {
		var pairs = new Array;
		for (var key in hash) {
			pairs.push(escape(key) + "=" + escape(hash[key]));
		}
		
		return pairs.join('&');
	}
	
	/**
	 * Print out a listing of the current members
	 * 
	 * @return void
	 * @access public
	 * @since 2/16/09
	 */
	SiteMemberPanel.prototype.printMembers = function () {
		this.listing.innerHTML = "";
		for (var id in this.members) {
			var entry = this.listing.appendChild(document.createElement('li'));
			entry.innerHTML = this.members[id] + " &nbsp; ";
			var remove = entry.appendChild(document.createElement('a'));
			remove.href = '#';
			remove.innerHTML = 'remove';
			remove.title = 'Remove ' + this.members[id] + ' from the Site-Members group.';
			remove.user_id = id;
			remove.panel = this;
			remove.onclick = function() {
				delete this.panel.members[this.user_id];
				this.panel.printMembers();
				return false;
			}
		}
	}
