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
		link.innerHTML = "All contributions on this site";
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
		this.loadTrackingSummary();
	}

	/**
	 * Load information about the agent.
	 * 
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.loadInfo = function () {
		// Get the new tags and re-call this method with the tags
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('agents', 'agent_info', {'agent_id': this.agentId});
// 		var newWindow = window.open(url);

		if (req) {
			// Define a variable to point at this object that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var authZViewer = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						authZViewer.writeInfo(req.responseXML);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}	
	}
	
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
	
	/**
	 * Load tracking information about the agent.
	 * 
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.loadTrackingSummary = function () {
		// Get the new tags and re-call this method with the tags
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('participation', 'ParticipationSummaryXml', {participant: this.agentId, node: this.nodeId});

		if (req) {
			// Define a variable to point at this object that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var panel = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						panel.writeTrackingInfo(req.responseXML);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}	
	}
	
	/**
	 * Write out the tracking info for the user
	 * 
	 * @param DOMDocument xmldoc
	 * @return void
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.writeTrackingInfo = function (xmldoc) {
		var roles = xmldoc.getElementsByTagName('role');
		for (var i = 0; i < roles.length; i++) {
			switch (roles[i].getAttribute('id')) {
				case 'comments':
					var numComments = new Number(roles[i].getAttribute('number'));
					break;
				case 'author':
					var numAuthor = new Number(roles[i].getAttribute('number'));
					break;
				case 'editor':
					var numEditor = new Number(roles[i].getAttribute('number'));
					break;
			}
		}
		
		this.trackingContainer.appendChild(document.createTextNode('Commenter: '));
		this.trackingContainer.appendChild(this.getActionsLink('commenter', numComments));
		
		this.trackingContainer.appendChild(document.createElement('br'));
		this.trackingContainer.appendChild(document.createTextNode('Author: '));
		this.trackingContainer.appendChild(this.getActionsLink('author', numAuthor));
		
		this.trackingContainer.appendChild(document.createElement('br'));
		this.trackingContainer.appendChild(document.createTextNode('Editor: '));
		this.trackingContainer.appendChild(this.getActionsLink('editor', numEditor));
	}
	
	/**
	 * Answer a html link to the participation window
	 * 
	 * @param string role
	 * @param int num
	 * @return string
	 * @access public
	 * @since 2/24/09
	 */
	ParticipantPanel.prototype.getActionsLink = function (role, num) {
		var link = document.createElement('a');
		link.href = Harmoni.quickUrl('participation', 'actions', {participant: this.agentId, node: this.nodeId, role:role});
		link.innerHTML = num;
		link.onclick = function () {
			var siteMapWindow = window.open(this.href, 'site_map', 'width=600,height=600,resizable=yes,scrollbars=yes');
			siteMapWindow.focus();
			return false;
		}
		return link;
	}