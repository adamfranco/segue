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
		
		if (destType != 'MenuOrganizer' && destType != 'ContentOrganizer')
			throw "Invalid destination type, '" + destType + "'. Must be 'MenuOrganizer' or 'ContentOrganizer'.";
		this.destType = destType;
		
		MoveCopyPanel.superclass.init.call(this, 
								"Move/Copy",
								50,
								200,
								positionElement);
		
		this.reloadSelection();
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
	
	
