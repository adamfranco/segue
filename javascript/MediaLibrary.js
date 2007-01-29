/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.1 2007/01/29 21:26:04 adamfranco Exp $
 */

MediaLibrary.prototype = new CenteredPanel();
MediaLibrary.prototype.constructor = MediaLibrary;
MediaLibrary.superclass = CenteredPanel.prototype;

/**
 * <##>
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.1 2007/01/29 21:26:04 adamfranco Exp $
 */
function MediaLibrary ( assetId, callingElement ) {
	if ( arguments.length > 0 ) {
		this.init( assetId, callingElement );
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
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.init = function ( assetId, callingElement ) {
		MediaLibrary.superclass.init.call(this, 
								"Media Library",
								15,
								600,
								callingElement);
		
		this.assetId = assetId;
		
		this.createForm();
		this.createMediaList();
		this.fetchMedia();
	}
	
	/**
	 * Initialize and run the AuthZViewer
	 * 
	 * @param string assetId
	 * @param object DOM_Element	callingElement 
	 * @return void
	 * @access public
	 * @since  1/26/07
	 */
	MediaLibrary.run = function (assetId, callingElement ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new MediaLibrary(assetId, callingElement );
		}
	}
	
	/**
	 * Create the forms for media uploading
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.createForm = function () {
		this.uploadForm = document.createElement('form');
		this.uploadForm.action = Harmoni.quickUrl('media', 'upload', {'assetId': this.assetId});
		this.uploadForm.method = 'post';
		this.uploadForm.enctype = 'multipart/form-data';
		mediaLibrary = this;
		this.uploadForm.onsubmit = function () {
			return AIM.submit(this, 
				{'onStart' : function() {mediaLibrary.startUploadCallback()}, 
				'onComplete' : function(xmlDoc) {mediaLibrary.completeUploadCallback(xmlDoc)}});
		}
		
		this.uploadFormDefaults = new Array();		
		
		var table = this.uploadForm.appendChild(document.createElement('table'));
		var row1 = table.appendChild(document.createElement('tr'));
		var row2 = table.appendChild(document.createElement('tr'));
		var row3 = table.appendChild(document.createElement('tr'));
		var row4 = table.appendChild(document.createElement('tr'));
		
		this.addFieldToRow(row1, 'File', 'file', 'media_file', '', {'size': '10'});
		this.addFieldToRow(row2, 'Name/Title', 'text', 'displayName', '');
		this.addFieldToRow(row3, 'Description', 'text', 'description', '');
		this.addFieldToRow(row4, 'Author/Creator', 'text', 'creator', '');
		
		this.addFieldToRow(row1, 'Source', 'text', 'source', '');
		this.addFieldToRow(row2, 'Publisher', 'text', 'publisher', '');
		this.addFieldToRow(row3, '[Pub.] Date', 'text', 'date', '');
		this.addFieldToRow(row4, '', 'submit', 'submit', 'Submit');
		
		
// 		var subDiv = this.uploadForm.appendChild(document.createElement('div'));
// 		var submit = subDiv.appendChild(document.createElement('input'));
// 		submit.type = 'submit';
		
		this.contentElement.appendChild(this.uploadForm);
	}
	
	/**
	 * Add an input field to the given table row
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 1/29/07
	 */
	MediaLibrary.prototype.addFieldToRow = function ( row, labelText, type, name, defaultValue, properties ) {
		var heading = row.appendChild(document.createElement('th'));
		if (labelText) {
			var label = heading.appendChild(document.createElement('label'));
			label.appendChild(document.createTextNode(labelText));
			heading.appendChild(document.createTextNode(': '));
		}
		var datum = row.appendChild(document.createElement('td'));
		var input = datum.appendChild(document.createElement('input'));
		input.type = type;
		input.name = name;
		input.value = defaultValue;
		this.uploadFormDefaults[name] = defaultValue;
		if (properties) {
			for (var key in properties) {
				input[key] = properties[key];
			}
		}
	}
	
	/**
	 * Create the media list container
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.createMediaList = function () {
		this.mediaList = document.createElement('ul');		
		
		this.contentElement.appendChild(this.mediaList);
	}
	
	/**
	 * Start the upload callback process.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.startUploadCallback = function () {
		return true;
	}
	
	/**
	 * Finish the upload callback process.
	 * 
	 * @param 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.completeUploadCallback = function (xmldoc) {
		try {
			var responseElement = xmldoc.firstChild;
			
			var errors = responseElement.getElementsByTagName('error');
			if (errors.length) {
				for (var i = 0; i < errors.length; i++) {
					throw new Error( errors[i].firstChild.data );
				}
			}
		} catch (error) {
			alert (error);
			
// 			if (responseElement.xml)
// 				alert(responseElement.xml);
// 			else {
// 				var xmlSerializer = new XMLSerializer();
// 				alert(xmlSerializer.serializeToString(responseElement));
// 			}
			
			return false;
		}
			
		var fileAssets = responseElement.getElementsByTagName('asset');
		if (fileAssets.length) {
			for (var i = 0; i < fileAssets.length; i++) {
				this.addMediaAsset(new MediaAsset(fileAssets[i]));
			}
		}
		
		for (var i = 0; i < this.uploadForm.elements.length; i++) {
			if (this.uploadForm.elements[i].type != 'submit') {
				if (this.uploadFormDefaults[this.uploadForm.elements[i].name])
					this.uploadForm.elements[i].value = 
						this.uploadFormDefaults[this.uploadForm.elements[i].name];
				else
					this.uploadForm.elements[i].value = '';
			}
		}
		
		return true;
	}
	
	/**
	 * Fetch the list of existing media
	 * 
	 * @return void
	 * @access public
	 * @since 1/29/07
	 */
	MediaLibrary.prototype.fetchMedia = function () {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('media', 'list', {'assetId': this.assetId});
		if (req) {
			// Define a variable to point at this MediaLibrary that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var mediaLibrary = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						mediaLibrary.loadMedia(req.responseXML);
					} else {
						throw new Error("There was a problem retrieving the XML data: " +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
		} else {
			throw new Error("Error: Unable to execute AJAX request. Please upgrade your browser.");
		}
	}
	
	/**
	 * Load the existing media for this asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.loadMedia = function (xmldoc) {
// 		try {
			var responseElement = xmldoc.firstChild;
			
			var errors = responseElement.getElementsByTagName('error');
			if (errors.length) {
				for (var i = 0; i < errors.length; i++) {
					throw new Error( errors[i].firstChild.data );
				}
			}
// 		} catch (error) {
// 			alert (error);
			
// 			if (responseElement.xml)
// 				alert(responseElement.xml);
// 			else {
// 				var xmlSerializer = new XMLSerializer();
// 				alert(xmlSerializer.serializeToString(responseElement));
// 			}
			
// 			return false;
// 		}
		
		var fileAssets = responseElement.getElementsByTagName('asset');
		if (fileAssets.length) {
			for (var i = 0; i < fileAssets.length; i++) {
				this.addMediaAsset(new MediaAsset(fileAssets[i]));
			}
		}
		
		// Re-center the panel
		this.center();
	}
	
	/**
	 * Add a media Asset to our list
	 * 
	 * @param object MediaAsset mediaAsset
	 * @return object MediaAsset
	 * @access public
	 * @since 1/26/07
	 */
	MediaLibrary.prototype.addMediaAsset = function (mediaAsset) {
		this.mediaList.appendChild(mediaAsset.getEntryElement());
	}
	

/**
 * Media Asset represents an asset with files, that can be displayed in the 
 * Segue media library
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.1 2007/01/29 21:26:04 adamfranco Exp $
 */
function MediaAsset ( xmlElement ) {
	if ( arguments.length > 0 ) {
		this.init( xmlElement );
	}
}

	/**
	 * Initilize this object
	 * 
	 * @param object DOM_Element xmlElement
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.init = function ( xmlElement ) {
		this.displayName = xmlElement.getElementsByTagName('displayName')[0].firstChild.data;
		this.description = xmlElement.getElementsByTagName('description')[0].firstChild.data;
	}
	
	/**
	 * Answer the HTML element for this object
	 * 
	 * @return DOM_Element
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.getEntryElement = function () {
		if (!this.entryElement) {
			this.entryElement = document.createElement('li');
			
			this.displayNameElement = this.entryElement.appendChild(document.createElement('div'));
			this.displayNameElement.innerHTML = this.displayName;
			
			this.descriptionElement = this.entryElement.appendChild(document.createElement('div'));
			this.descriptionElement.innerHTML = this.description;
		}
		
		return this.entryElement;
	}



/**
 *
 *  AJAX IFRAME METHOD (AIM)
 *  http://www.webtoolkit.info/
 *
 **/

AIM = {

    frame : function(c) {

        var n = 'f' + Math.floor(Math.random() * 99999);
        var d = document.createElement('DIV');
        d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
        document.body.appendChild(d);

        var i = document.getElementById(n);
        if (c && typeof(c.onComplete) == 'function') {
            i.onComplete = c.onComplete;
        }

        return n;
    },

    form : function(f, name) {
        f.setAttribute('target', name);
    },

    submit : function(f, c) {
        AIM.form(f, AIM.frame(c));
        if (c && typeof(c.onStart) == 'function') {
            return c.onStart();
        } else {
            return true;
        }
    },

    loaded : function(id) {
        var i = document.getElementById(id);
        if (i.contentDocument) {
            var d = i.contentDocument;
        } else if (i.contentWindow) {
            var d = i.contentWindow.document;
        } else {
            var d = window.frames[id].document;
        }
        if (d.location.href == "about:blank") {
            return;
        }

        if (typeof(i.onComplete) == 'function') {
//            i.onComplete(d.body.innerHTML);
			// My action is returning an XML document, so I want to return that
			
			// rather than an HTML page
			 i.onComplete(d);
        }
    }

}