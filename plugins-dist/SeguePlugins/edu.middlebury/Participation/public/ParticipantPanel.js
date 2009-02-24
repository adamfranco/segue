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
		this.participantName = name;
		this.participantId = id;
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
		link.href = Harmoni.quickUrl('participation', 'actions', {node: this.nodeId, participant: this.participantId});
		link.onclick = function () {
			var siteMapWindow = window.open(this.href, 'site_map', 'width=600,height=600,resizable=yes,scrollbars=yes');
			siteMapWindow.focus();
			return false;
		}
		link.innerHTML = "Actions on this site";
		
		this.trackingContainer = this.contentElement.appendChild(document.createElement('div'));
		this.trackingContainer.className = 'tracking';
		
		
		var heading = this.contentElement.appendChild(document.createElement('h4'));
		heading.innerHTML = "Roles";
		
		var link =  this.contentElement.appendChild(document.createElement('a'));
		link.href = rolesUrl;
		link.innerHTML = "View and modify roles for " + name + " &raquo;";
		
		
// 		this.loadInfo();
// 		this.loadTrackingSummary();
	}
