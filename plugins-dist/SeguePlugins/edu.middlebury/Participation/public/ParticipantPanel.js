/**
 * @since 2/24/09
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

ParticipantPanel.prototype = new Panel();
ParticipantPanel.prototype.constructor = ParticipantPanel;
ParticipantPanel.superclass = Panel.prototype;

/**
 * A panel for displaying participant information.
 * 
 * @since 2/24/09
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function ParticipantPanel ( name, id, nodeId, rolesUrl, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( name, id, nodeId, rolesUrl, positionElement );
	}
}
	/**
	 * Initialize and run the ParticipantPanel
	 * 
	 * @param string name
	 * @param string id
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.run = function ( name, id, nodeId, rolesUrl, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new ParticipantPanel(name, id, nodeId, rolesUrl, positionElement);
		}
		
	}
	
	/**
	 * Initialize the ParticipantPanel
	 * 
	 * @param string name
	 * @param string id
	 * @param DOMElement positionElement
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.init = function ( name, id, nodeId, rolesUrl, positionElement ) {
		this.agentName = name;
		this.agentId = id;
		this.nodeId = nodeId;
		
		ParticipantPanel.superclass.init.call(this, 
								name,
								15,
								300,
								positionElement,
								'participant_panel');
		
		
		var heading = this.contentElement.appendChild(document.createElement('h4'));
		heading.innerHTML = "Info";
		
		this.infoContainer = this.contentElement.appendChild(document.createElement('div'));
		this.infoContainer.className = 'info';
		
		
		var heading = this.contentElement.appendChild(document.createElement('h4'));
		heading.innerHTML = "Tracking";
		
		var link =  this.contentElement.appendChild(document.createElement('a'));
		link.href = Harmoni.quickUrl('participation', 'actions', {node: this.nodeId, participant: this.agentId});
		link.onclick = function () {
			var siteMapWindow = window.open(this.href, 'site_map', 'width=600,height=600,resizable=yes,scrollbars=yes');
			siteMapWindow.focus();
			return false;
		}
		link.innerHTML = "Actions on this site";
		link.className = 'tracking';
		
		this.trackingContainer = this.contentElement.appendChild(document.createElement('div'));
		this.trackingContainer.className = 'tracking';
		
		
		var heading = this.contentElement.appendChild(document.createElement('h4'));
		heading.innerHTML = "Roles";
		
		var link =  this.contentElement.appendChild(document.createElement('a'));
		link.href = rolesUrl;
		link.innerHTML = "View and modify roles &raquo;";
		link.className = 'roles';
		
		
		this.loadInfo();
// 		this.loadTrackingSummary();
	}

	/**
	 * Load the agent information and write it to our container.
	 * 
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.loadInfo = AgentInfoPanel.prototype.loadInfo;
	
	/**
	 * Write out the agent info to its container.
	 * 
	 * @param DOMDocument xmldoc
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.writeInfo = function (xmldoc) {
		var elements = xmldoc.getElementsByTagName('email');
		if (elements.length) {
			var email = elements.item(0).firstChild.nodeValue;
			if (email) {
				this.infoContainer.innerHTML = 'Email: <a href="mailto:' + email + '">' + email + '</a>';
			}
		}
		
		var elements = xmldoc.getElementsByTagName('description');
		if (elements.length) {
			var description = elements.item(0).firstChild.nodeValue;
			if (description) {
				this.infoContainer.innerHTML = this.infoContainer.innerHTML + '<br/>Description: <em>' + description + '</em>';
			}
		}
	}