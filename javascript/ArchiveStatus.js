/**
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */

ArchiveStatus.prototype = new CenteredPanel();
ArchiveStatus.prototype.constructor = ArchiveStatus;
ArchiveStatus.superclass = CenteredPanel.prototype;

/**
 * <##>
 * 
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
function ArchiveStatus ( callingElement, checkUrl ) {
	if ( arguments.length > 0 ) {
		this.init( callingElement, checkUrl );
	}
}
	
	/**
	 * Initialize this object
	 * 
	 * @param string assetId
	 * @param object DOM_Element	callingElement 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @return void
	 * @access public
	 */
	ArchiveStatus.prototype.init = function ( callingElement, checkUrl ) {
		ArchiveStatus.superclass.init.call(this, 
								"Exporting HTML Archive...",
								15,
								600,
								callingElement,
								'ArchiveStatus');
		
		
		this.checkUrl = checkUrl;
		this.start = new Date();
		this.outputStarted = false;
		this.statusDiv = this.contentElement.appendChild(document.createElement('div'));
		this.statusDiv.innerHTML = "Waiting";
		
		// Show the status bars for Gecko and IE 8/9
		Prototype.Browser.IE6 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 6;
		Prototype.Browser.IE7 = Prototype.Browser.IE && parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE")+5)) == 7;
		Prototype.Browser.IE8 = Prototype.Browser.IE && !Prototype.Browser.IE6 && !Prototype.Browser.IE7;
		if (Prototype.Browser.Gecko || (Prototype.Browser.IE && !Prototype.Browser.IE6 && !Prototype.Browser.IE7)) {
			this.cancel.hide();
			statusPanel = this;
			setTimeout(function(){statusPanel.update()}, 2000);
		}
		// Show a message for other browsers.
		else {
			this.contentElement.innerHTML = "Your HTML archive of the site is exporting now. This is a long process and may take several minutes. <br/><br/>Please close this panel when your download has completed.";
		}
	}
	
	ArchiveStatus.prototype.update = function () {
		statusPanel = this;
		new Ajax.Request(this.checkUrl, {
		  method: 'get',
		  onComplete: function(transport) {
		  	
			if (transport.status == 404) {
				// Archive is done, close the panel.
				if (statusPanel.outputStarted) {
					statusPanel.close();
					return;
				}
				// The archive hasn't started in a minute.
				else if (new Date - statusPanel.start > 60000) {
					statusPanel.statusDiv.innerHTML = "The archive hasn't started in over a minute. Maybe you should close this pannel and try again.";
					return;
				}
			}
			
			statusPanel.outputStarted = true;
			statusPanel.statusDiv.innerHTML = transport.responseText;
			setTimeout(function(){statusPanel.update()}, 2000);
		  }
		});
	}
	
	
