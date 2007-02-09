/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
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
 * @version $Id: MediaLibrary.js,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
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
								callingElement,
								'medialibrary');
		
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
		this.mediaList = document.createElement('table');	
		this.mediaList.className = 'medialist';
		this.mediaListHead = this.mediaList.appendChild(document.createElement('thead'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('thumb'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('info'));
		
// 		var element = this.mediaListHead.appendChild(document.createElement('th'));
// 		element.appendChild(document.createTextNode('size'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('date'));
		
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
		
		var xmlSerializer = new XMLSerializer();
		alert(xmlSerializer.serializeToString(responseElement));
			
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
// 						alert(req.responseText);
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
 * @version $Id: MediaLibrary.js,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
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
		this.modificationDate = xmlElement.getElementsByTagName('modificationDate')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('title').length)
			this.title = xmlElement.getElementsByTagName('title')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('source').length)
			this.source = xmlElement.getElementsByTagName('source')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('creator').length)
			this.creator = xmlElement.getElementsByTagName('creator')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('publisher').length)
			this.publisher = xmlElement.getElementsByTagName('publisher')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('date').length)
			this.date = xmlElement.getElementsByTagName('date')[0].firstChild.data;
		
		this.files = new Array();
		var mediaElements = xmlElement.getElementsByTagName('file');
		for (var i = 0; i < mediaElements.length; i++) {
			this.files.push(new MediaFile(mediaElements[i]));
		}
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
			this.entryElement = document.createElement('tbody');
			
			var rows = new Array();
			rows.push(this.entryElement.appendChild(document.createElement('tr')));
			
			this.filesElement = rows[rows.length - 1].appendChild(document.createElement('td'));
			if (this.files.length) {
				for (var i = 0; i < this.files.length; i++) {
					this.files[i].render(this.filesElement);
				}
			}
			
			this.displayNameElement = rows[rows.length - 1].appendChild(document.createElement('td'));
			this.displayNameElement.innerHTML = this.displayName;
			this.displayNameElement.className = 'displayName';
			
			if (this.description) {
				rows.push(this.entryElement.appendChild(document.createElement('tr')));
				this.descriptionElement = rows[rows.length - 1].appendChild(document.createElement('td'));
				this.descriptionElement.innerHTML = this.description;
				this.descriptionElement.className = 'description';
			}
			
			rows.push(this.entryElement.appendChild(document.createElement('tr')));
			this.citationElement = rows[rows.length - 1].appendChild(document.createElement('td'));
			this.citationElement.className = 'citation';
			this.writeCitation();
			
			this.dateElement = rows[0].appendChild(document.createElement('td'));
			this.dateElement.className = 'modification_date';
			if (this.modificationDate)
				this.dateElement.innerHTML = this.modificationDate;
			
			this.filesElement.rowSpan = rows.length;
			this.dateElement.rowSpan = rows.length;
		}
		
		return this.entryElement;
	}
	
	/**
	 * Write a citation into the citation element
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/07
	 */
	MediaAsset.prototype.writeCitation = function () {		
		if (this.author) {
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.author;
			
			this.citationElement.appendChild(document.createTextNode('. '));
		}
		
		if (this.title) {
			this.citationElement.appendChild(document.createTextNode('"'));
			
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.title;
			
			this.citationElement.appendChild(document.createTextNode('" '));
		}
		
		if (this.source) {
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.source;
			element.style.fontStyle = 'italic';
			
			this.citationElement.appendChild(document.createTextNode('. '));
		}
		
		if (this.publisher) {
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.publisher;
			
			this.citationElement.appendChild(document.createTextNode(', '));
		}
		
		if (this.date) {
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.date;
			
			this.citationElement.appendChild(document.createTextNode(' '));
		}
		
		if (this.pages) {
			this.citationElement.appendChild(document.createTextNode('('));
			
			var element = this.citationElement.appendChild(document.createElement('span'));
			element.innerHTML = this.pages;
			
			this.citationElement.appendChild(document.createTextNode(') '));
		}
	}

/**
 * This class represents a file record attached to an asset
 * 
 * @since 1/31/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.2 2007/02/09 21:35:31 adamfranco Exp $
 */
function MediaFile ( xmlElement ) {
	if ( arguments.length > 0 ) {
		this.init( xmlElement );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param <##>
	 * @return void
	 * @access public
	 * @since 1/31/07
	 */
	MediaFile.prototype.init = function ( xmlElement ) {
		this.id = xmlElement.getAttribute('id');
		this.name = xmlElement.getElementsByTagName('name')[0].firstChild.data;
		this.size = xmlElement.getElementsByTagName('size')[0].firstChild.data;
		this.url = xmlElement.getElementsByTagName('url')[0].firstChild.data;
		this.url = decodeURI(this.url);
		this.url = this.url.urlDecodeAmpersands();
		this.thumbnailUrl = xmlElement.getElementsByTagName('thumbnailUrl')[0].firstChild.data;
		this.thumbnailUrl = decodeURI(this.thumbnailUrl);
		this.thumbnailUrl = this.thumbnailUrl.urlDecodeAmpersands();
	}
	
	/**
	 * Render the object into a container
	 * 
	 * @param object DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/31/07
	 */
	MediaFile.prototype.render = function ( container ) {
		this.imageLink = container.appendChild(document.createElement('a'));
		this.imageLink.href = this.url;
		
		this.img = this.imageLink.appendChild(document.createElement('img'));
		this.img.src = this.thumbnailUrl;
		
// 		var url = this.url;
// 		this.img.onclick = function () {
// 			window.open (url, this.id, "height=400,width=600,resizable=yes,scrollbars=yes");
// 		}
		this.img.className = 'thumbnail link';
		
// 		container.appendChild(this.img);
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