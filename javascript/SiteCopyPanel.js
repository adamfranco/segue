/**
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

SiteCopyPanel.prototype = new Panel();
SiteCopyPanel.prototype.constructor = SiteCopyPanel;
SiteCopyPanel.superclass = Panel.prototype;

/**
 * A panel for setting options and copying a site.
 * 
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function SiteCopyPanel ( destSlot, srcSiteId, srcTitle, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( destSlot, srcSiteId, srcTitle, positionElement );
	}
}

	/**
	 * Initialize the object
	 * 
	 * @param string destSlot
	 * @param string srcSiteId
	 * @param string srcTitle
	 * @param string positionElement
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	SiteCopyPanel.prototype.init = function ( destSlot, srcSiteId, srcTitle, positionElement ) {
		this.destSlot = destSlot;
		this.srcSiteId = srcSiteId;
		this.srcTitle = srcTitle;
		
		SiteCopyPanel.superclass.init.call(this, 
								"Copy <br/>&nbsp;&nbsp;&nbsp;&nbsp;'" + srcTitle + "' <br/>to <br/>&nbsp;&nbsp;&nbsp;&nbsp;'" + destSlot + "'",
								15,
								200,
								positionElement);
		
		// Build up the form
		var form = document.createElement('form');
		form.action = Harmoni.quickUrl('portal', 'copy_site');
		form.method = 'post';
		
		var siteCopyPanel = this;
		form.onsubmit = function() {
			siteCopyPanel.submitForm(this);
			return false;
		}
		
		var input = document.createElement('input');
		input.name = 'destSlot';
		input.type = 'hidden';
		input.value = destSlot;
		form.appendChild(input);
		
		var input = document.createElement('input');
		input.name = 'srcSiteId';
		input.type = 'hidden';
		input.value = srcSiteId;
		form.appendChild(input);
		
		var input = document.createElement('input');
		input.name = 'copyPermissions';
		input.type = 'checkbox';
		input.value = 'true';
		input.checked = 'checked';
		form.appendChild(input);
		form.appendChild(document.createTextNode(' Copy Permissions?'));
		form.appendChild(document.createElement('br'));
		
		var input = document.createElement('input');
		input.name = 'copyDiscussions';
		input.type = 'checkbox';
		input.value = 'true';
		input.checked = 'checked';
		form.appendChild(input);
		form.appendChild(document.createTextNode(' Copy Discussion Posts?'));
		form.appendChild(document.createElement('br'));
		form.appendChild(document.createElement('br'));
		
		var submit = document.createElement('input');
		submit.type = 'submit';
		submit.value = "Copy »";
		var div = document.createElement('div');
		div.style.textAlign = 'right';
		div.appendChild(submit);
		form.appendChild(div);
		
		this.contentElement.appendChild(form);
	}
	
	/**
	 * Initialize and run the SiteCopyPanel
	 * 
	 * @param string destSlot
	 * @param string srcSiteId
	 * @param string srcTitle
	 * @param string positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	SiteCopyPanel.run = function (destSlot, srcSiteId, srcTitle, positionElement ) {
		if (positionElement.panel && positionElement.panel.srcSiteId == srcSiteId) {
			positionElement.panel.open();
		} else {
			var tmp = new SiteCopyPanel(destSlot, srcSiteId, srcTitle, positionElement );
		}
	}
