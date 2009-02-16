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
function SiteMemberPanel ( callingElement, writableField ) {
	if ( arguments.length > 0 ) {
		this.init( callingElement, writableField );
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
	SiteMemberPanel.prototype.init = function ( callingElement, writableField ) {
		SiteMemberPanel.superclass.init.call(this,
									"Add Members to this Site",
									15,
									600,
									callingElement,
									'site_members_panel');
		this.writableField = writableField;
		
		this.members = this.decodeValue(this.writableField.value);
		
// 		console.log(this.members);
		
		// @todo create a form for searching and adding members.

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
	 * Initialize and run the panel
	 * 
	 * @param DOM_Element callingElement
	 * @param DOM_Element writableField A hidden field for writing our results.
	 * @return void
	 * @access public
	 * @since 2/6/09
	 */
	SiteMemberPanel.run = function ( callingElement, writableField ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new SiteMemberPanel( callingElement, writableField );
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
