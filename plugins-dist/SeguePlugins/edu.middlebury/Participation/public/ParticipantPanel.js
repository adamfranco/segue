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
function ParticipantPanel ( name, id, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( name, id, positionElement );
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
	ParticipantPanel.run = function ( name, id, positionElement ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tmp = new ParticipantPanel(name, id, positionElement);
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
	ParticipantPanel.prototype.init = function ( name, id, positionElement ) {
		this.participantName = name;
		this.participantId = id;
		
		ParticipantPanel.superclass.init.call(this, 
								name,
								15,
								300,
								positionElement,
								'participant_panel');
		
	}
