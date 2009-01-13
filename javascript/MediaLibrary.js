/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
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
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function MediaLibrary ( assetId, callingElement, allowedMimeTypes ) {
	if ( arguments.length > 0 ) {
		this.init( assetId, callingElement, allowedMimeTypes );
	}
}

	MediaLibrary.externalLibraries = [];

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
	MediaLibrary.prototype.init = function ( assetId, callingElement, allowedMimeTypes ) {
		if (!assetId || !assetId.length) {
			var message = "Required parameter, assetId, does not have a value";
// 			alert(message);
			throw message;
		}
		
		MediaLibrary.superclass.init.call(this, 
								"Media Library",
								15,
								600,
								callingElement,
								'medialibrary');
		
		this.caller = callingElement;
		this.assetId = assetId;
		this.allowedMimeTypes = allowedMimeTypes;
		
		this.tabs = new TabbedContent();
		this.tabs.appendToContainer(this.contentElement);
		
	// Files attached to this asset
		var tab = this.tabs.addTab('asset_media', "Files Here");
		this.assetLibrary = new AssetLibrary(this, this.assetId, this.caller, tab.wrapperElement);
		
		tab.library = this.assetLibrary;
		tab.onOpen = function () { this.library.onOpen() };
		
		this.tabs.selectTab('asset_media');
		
	// All Files in Site
		var tab = this.tabs.addTab('site_media', "Other Files In Site");
		this.siteLibrary = new SiteLibrary(this, this.assetId, this.caller, tab.wrapperElement);

		tab.library = this.siteLibrary;
		tab.onOpen = function () { this.library.onOpen() };

	// Addtional external libraries
		for ( var i = 0; i < MediaLibrary.externalLibraries.length; i++) {
			var config = MediaLibrary.externalLibraries[i];
			
			// Include any needed class files.
			if (!typeof config.class !== 'function') {
				if (!config.jsSourceUrl)
					throw "External library class " + config.class + " is not defined and a source URL is not specified.";
				
				Script.include(config.jsSourceUrl);
			}
			
			var tab = this.tabs.addTab('ext_' + i, config.title);
			eval('tab.library = new ' + config.class + '(this, config, this.caller, tab.wrapperElement);');
			
			tab.onOpen = function () { 
				if (typeof this.library.onOpen == 'function')
					this.library.onOpen();
			};
		}
		
	}
	
	/**
	 * Force-reload the whole media library. needed for IE crap.
	 * 
	 * @return void
	 * @access public
	 * @since 2/20/08
	 */
	MediaLibrary.prototype.forceReload = function () {
		this.mainElement.innerHTML = '';
		this.mainElement.parentNode.removeChild(this.mainElement);
		this.mainElement = null;
		this.screen.innerHTML = '';
		this.screen.parentNode.removeChild(this.screen);
		this.init(this.assetId, this.caller);
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
	MediaLibrary.run = function (assetId, callingElement, allowedMimeTypes ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new MediaLibrary(assetId, callingElement, allowedMimeTypes );
		}
	}
	
	/**
	 * Update the panel when the content changes
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	MediaLibrary.prototype.onContentChange = function () {
		this.center();
	}

/**
 * <##>
 * 
 * @since 2/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function FileLibrary ( owner, assetId, caller, container ) {
	if ( arguments.length > 0 ) {
		this.init( owner, assetId, caller, container );
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
	 * @param object DOM_Element	container the container to render our content in.
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	FileLibrary.prototype.init = function ( owner, assetId, caller, container ) {
		this.owner = owner;
		this.assetId = assetId;
		this.caller = caller;
		this.container = container;
		this.media = new Array();
	}

	/**
	 * Force-reload the whole media library. needed for IE crap.
	 * 
	 * @return void
	 * @access public
	 * @since 2/20/08
	 */
	FileLibrary.prototype.forceReload = function () {
		this.owner.forceReload();
	}
	
	/**
	 * Choose a media file and close
	 * 
	 * @param object MediaFile mediaFile
	 * @return void
	 * @access public
	 * @since 2/21/07
	 */
	FileLibrary.prototype.onUse = function (mediaFile) {
		this.owner.close();
		this.caller.onUse(mediaFile);
	}
	
	/**
	 * Recenter the panel
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	FileLibrary.prototype.onContentChange = function () {
		this.owner.onContentChange();
	}
	
	/**
	 * Create the media list container
	 * 
	 * @param object DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	FileLibrary.prototype.createMediaList = function (container) {
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
		element.appendChild(document.createTextNode('modification date'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('permissions'));
		element.className = 'perms_col';
		
		container.appendChild(this.mediaList);
	}
	
	/**
	 * Answer a list of allowed mime types or null if any are allowed.
	 * 
	 * @return mixed Array or null
	 * @access public
	 * @since 8/26/08
	 */
	FileLibrary.prototype.getAllowedMimeTypes = function () {
		if (this.owner.allowedMimeTypes && this.owner.allowedMimeTypes.length)
			return this.owner.allowedMimeTypes;
		else
			return null;
	}
	
	/**
	 * Fetch the list of existing media
	 * 
	 * @return void
	 * @access public
	 * @since 1/29/07
	 */
	FileLibrary.prototype.fetchMedia = function () {
		var req = Harmoni.createRequest();
		var url = this.getMediaListUrl();
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
	FileLibrary.prototype.loadMedia = function (xmldoc) {
// 		try {
			var responseElement = xmldoc.firstChild;
			var errors = xmldoc.getElementsByTagName('error');
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
		
		var fileAssets = xmldoc.getElementsByTagName('asset');
		if (fileAssets.length) {
			for (var i = 0; i < fileAssets.length; i++) {
				this.addMediaAsset(new MediaAsset(this.assetId, fileAssets[i], this, this.canEdit));
			}
		}
		
		// Re-center the panel
		this.onContentChange();
	}
	
	/**
	 * Add a media Asset to our list
	 * 
	 * @param object MediaAsset mediaAsset
	 * @return object MediaAsset
	 * @access public
	 * @since 1/26/07
	 */
	FileLibrary.prototype.addMediaAsset = function (mediaAsset) {
		this.mediaList.appendChild(mediaAsset.getEntryElement());
		this.owner.center();
		
		this.media.push(mediaAsset);
	}
	
	/**
	 * Add an input field to the given table row
	 * 
	 * @param object DOM_Element row
	 * @param string labelText
	 * @param string type <input type='xxxx'/> I.E. File, text, submit
	 * @param string name
	 * @param string defaultValue
	 * @param object properties An associative list of other properties to give to
	 *							the input
	 * @return DOM_Element the input element
	 * @access public
	 * @since 1/29/07
	 */
	FileLibrary.prototype.addFieldToRow = function ( row, labelText, type, name, defaultValue, properties ) {
		var heading = row.appendChild(document.createElement('th'));
		if (labelText) {
			var label = heading.appendChild(document.createElement('label'));
			label.appendChild(document.createTextNode(labelText));
			heading.appendChild(document.createTextNode(': '));
		}
		var datum = row.appendChild(document.createElement('td'));
		
		// IE does not allow setting of the input name after creation
		if (getBrowser()[2] == 'msie') {
			var input = document.createElement('<input name="'+name+'">');
		} else {
			var input = document.createElement('input');
			input.name = name;
		}
		input.type = type;
		input.value = defaultValue;
		
		this.uploadFormDefaults[name] = defaultValue;
		if (properties) {
			for (var key in properties) {
				input[key] = properties[key];
			}
		}
		
		input.tabIndex = this.tabIndex;
		this.tabIndex++;
		
		datum.appendChild(input);
		
		return input;
	}
	
	/**
	 * Start the upload callback process.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	FileLibrary.prototype.startUploadCallback = function () {
		// IE 6 will not load properly, so set a timeout and reload soon after.
		if (getBrowser()[2] == 'msie' && getMajorVersion(getBrowser()[3]) < 7) {
			var currentObj = this;
			window.setTimeout(function() {currentObj.forceReload();}, 15000);
			alert("IE6 does not refresh properly. \nMediaLibrary will reload in 15 seconds. \nIf you file does not appear after the media library reloads, refresh the page and open the media library again.");
		}
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
	FileLibrary.prototype.completeUploadCallback = function (xmldoc) {
// 		alert('xmldoc = ' + xmldoc);
// 		alert('xmldoc.firstChild = ' + xmldoc.firstChild);
// 		alert('xmldoc.documentElement = ' + xmldoc.documentElement);
// 		alert('xmldoc.documentElement.firstChild = ' + xmldoc.documentElement.firstChild);
		try {
			var responseElement = xmldoc.firstChild;
			
			var errors = responseElement.getElementsByTagName('error');
			if (errors.length) {
				for (var i = 0; i < errors.length; i++) {
					if (errors[i].hasAttribute('type') && errors[i].getAttribute('type') == 'ImageProcessingFailedException') {
						// do not notify on convert-failed errors
					} else {
						if (errors[i].hasAttribute('type'))
							var type = errors[i].getAttribute('type') + ": ";
						else
							var type = ''
						alert(type + errors[i].firstChild.data);
// 						throw new Error( errors[i].firstChild.data );
					}
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
				
// 		var xmlSerializer = new XMLSerializer();
// 		alert(xmlSerializer.serializeToString(responseElement));
			
		var fileAssets = responseElement.getElementsByTagName('asset');
		if (fileAssets.length) {
			for (var i = 0; i < fileAssets.length; i++) {
				this.addMediaAsset(new MediaAsset(this.assetId, fileAssets[i], this));
			}
		} else if (getBrowser()[2] == 'msie') {
			// IE Renders the iframe document and barfs up the xml. Just reinitialize
			// the entire media library as that is easier
			this.forceReload();
// 			alert('Error in file upload response: no files listed. ');
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
		
		this.owner.center();
		
		return true;
	}



AssetLibrary.prototype = new FileLibrary();
AssetLibrary.prototype.constructor = AssetLibrary;
AssetLibrary.superclass = FileLibrary.prototype;

/**
 * <##>
 * 
 * @since 2/26/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function AssetLibrary ( owner, assetId, caller, container ) {
	if ( arguments.length > 0 ) {
		this.init( owner, assetId, caller, container );
		
		this.canEdit = true;
	}
}
	
	/**
	 * Open this library
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	AssetLibrary.prototype.onOpen = function () {
		if (!this.mediaList) {
			this.tabIndex = 1;
			this.uploadFormDefaults = new Array();
			
			this.createQuotaDisplay(this.container);
			this.createForm(this.container);
			this.createMediaList(this.container);
			this.fetchMedia();
		}
	}
	
	/**
	 * Answer the url of the media list
	 * 
	 * @return string
	 * @access public
	 * @since 2/26/07
	 */
	AssetLibrary.prototype.getMediaListUrl = function () {
		return Harmoni.quickUrl('media', 'list', {'assetId': this.assetId});
	}
	
	/**
	 * Create an empty quota display
	 * 
	 * @param object DOM_Element container
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	AssetLibrary.prototype.createQuotaDisplay = function (container) {
		this.quotaUsedDisplay = container.appendChild(document.createElement('div'));
		this.quotaUsedDisplay.className = 'media_quota_title';
		this.quotaUsedDisplay.innerHTML = "Media Quota for this site:";
		
		this.quotaDisplay = document.createElement('div');
		this.quotaDisplay.className = 'media_quota';
		this.quotaDisplay.style.width = '100%';
		
		this.quotaUsedDisplay = this.quotaDisplay.appendChild(document.createElement('div'));
		this.quotaUsedDisplay.className = 'media_quota_used';
		this.quotaUsedDisplay.innerHTML = " &nbsp; ";
		
		this.quotaUsedLabel = this.quotaDisplay.appendChild(document.createElement('div'));
		this.quotaUsedLabel.className = 'media_quota_used_label';
		this.quotaUsedLabel.innerHTML = "Used: Unknown";
		
		this.quotaFreeDisplay = this.quotaDisplay.appendChild(document.createElement('div'));
		this.quotaFreeDisplay.className = 'media_quota_free';
		this.quotaFreeDisplay.innerHTML = " &nbsp; ";
		
		this.quotaFreeLabel = this.quotaDisplay.appendChild(document.createElement('div'));
		this.quotaFreeLabel.className = 'media_quota_free_label';
		this.quotaFreeLabel.innerHTML = "Free: Unknown";
		
		this.writeQuota();
		
		container.appendChild(this.quotaDisplay);
		var spacer = document.createElement("div");
		spacer.style.clear = 'both';
		spacer.innerHTML = " &nbsp; ";
		container.appendChild(spacer);
	}
	
	/**
	 * Write the quota values to the quota display
	 * 
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	AssetLibrary.prototype.writeQuota = function () {
		if (this.quota == undefined || this.quotaUsed == undefined) {
			var quota = 2;
			var quotaUsed = 1;
			this.quotaUsedLabel.innerHTML = "Used: Unknown";
			this.quotaFreeLabel.innerHTML = "Free: Unknown";
		} else {
			var quota = this.quota;
			var quotaUsed = this.quotaUsed;
			this.quotaUsedLabel.innerHTML = "Used: " + quotaUsed.asByteSizeString();
			this.quotaFreeLabel.innerHTML = "Free: " + (quota - quotaUsed).asByteSizeString();
		}
		
		var percentUsed = (quotaUsed/quota);
		// Show little bits at either end if not at exactly 0% or 100%
		if (percentUsed < .5)
			percentUsed = Math.ceil(percentUsed * 100);
		else
			percentUsed = Math.floor(percentUsed * 100);
		var percentFree = 100 - percentUsed;
		
		this.quotaUsedDisplay.style.width = (percentUsed) + '%';
		this.quotaFreeDisplay.style.width = (percentFree) + '%';
	}
	
	/**
	 * Update the quota value
	 * 
	 * @param int quota
	 * @param int used
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	AssetLibrary.prototype.updateQuota = function (quota, used) {
		this.quota = quota;
		this.quotaUsed = used;
		
		this.writeQuota();
	}
	
	/**
	 * Load the existing media for this asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	AssetLibrary.prototype.loadMedia = function (xmldoc) {
		AssetLibrary.superclass.loadMedia.call(this, xmldoc);
		
		this.updateQuotaFromDocument(xmldoc);
	}
	
	/**
	 * Update the quota from an xml document
	 * 
	 * @param DOM_Document xmldoc
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	AssetLibrary.prototype.updateQuotaFromDocument = function (xmldoc) {
		var quotaElements = xmldoc.getElementsByTagName('quota');
		var quotaElement = quotaElements.item(0);
		if (quotaElement) {
			this.updateQuota(parseInt(quotaElement.getAttribute('quota')), parseInt(quotaElement.getAttribute('quotaUsed')));
		}
	}
	
	AssetLibrary.prototype.completeUploadCallback = function (xmldoc) {
		AssetLibrary.superclass.completeUploadCallback.call(this, xmldoc);
		
		this.updateQuotaFromDocument(xmldoc);
	}
	
	/**
	 * Create the forms for media uploading
	 * 
	 * @param object DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	AssetLibrary.prototype.createForm = function (container) {
		
		this.uploadForm = document.createElement('form');
		this.uploadForm.action = Harmoni.quickUrl('media', 'upload', {'assetId': this.assetId});
		this.uploadForm.method = 'post';
		this.uploadForm.enctype = 'multipart/form-data';
		
		// IE doesn't like the form as-is. It seems to need to have it written to
		// a string and then re-loaded in order for everything to be submitted properly.
		if (getBrowser()[2] == 'msie') {
			var tempParent = document.createElement('div');
			tempParent.appendChild(this.uploadForm);
			var temp = tempParent.innerHTML;
			tempParent.innerHTML = temp;
			this.uploadForm = tempParent.getElementsByTagName('form')[0];
		}
		
		// Set the submit actions
		var mediaLibrary = this;
		this.uploadForm.onsubmit = function () {
			return AIM.submit(this, 
				{'onStart' : function() {mediaLibrary.startUploadCallback()}, 
				'onComplete' : function(xmlDoc) {mediaLibrary.completeUploadCallback(xmlDoc)}});
		}
		
		var table = this.uploadForm.appendChild(document.createElement('table'));
		var tbody = table.appendChild(document.createElement('tbody'));
		var row1 = tbody.appendChild(document.createElement('tr'));
		var row2 = tbody.appendChild(document.createElement('tr'));
		var row3 = tbody.appendChild(document.createElement('tr'));
		var row4 = tbody.appendChild(document.createElement('tr'));
		
		this.addFieldToRow(row1, 'File', 'file', 'media_file', '', {'size': '10'});
		this.addFieldToRow(row2, 'Name/Title', 'text', 'displayName', '');
		this.addFieldToRow(row3, 'Description', 'text', 'description', '');
		this.addFieldToRow(row4, 'Author/Creator', 'text', 'creator', '');
		
		this.addFieldToRow(row1, 'Source', 'text', 'source', '');
		this.addFieldToRow(row2, 'Publisher', 'text', 'publisher', '');
		this.addFieldToRow(row3, '[Pub.] Date', 'text', 'date', '');
		this.addFieldToRow(row4, '', 'submit', 'submit', 'Upload');
		
		
// 		var subDiv = this.uploadForm.appendChild(document.createElement('div'));
// 		var submit = subDiv.appendChild(document.createElement('input'));
// 		submit.type = 'submit';
		
		container.appendChild(this.uploadForm);
		
		this.uploadForm.elements[0].focus();
	}


SiteLibrary.prototype = new FileLibrary();
SiteLibrary.prototype.constructor = SiteLibrary;
SiteLibrary.superclass = FileLibrary.prototype;

/**
 * <##>
 * 
 * @since 2/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function SiteLibrary ( owner, assetId, caller, container ) {
	if ( arguments.length > 0 ) {
		this.init( owner, assetId, caller, container );
		
		this.canEdit = false;
	}
}

	/**
	 * Open this library
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	SiteLibrary.prototype.onOpen = function () {
		if (!this.mediaList) {			
			this.createMediaList(this.container);
			this.fetchMedia();
		}
	}
	
	/**
	 * Answer the url of the media list
	 * 
	 * @return string
	 * @access public
	 * @since 2/26/07
	 */
	SiteLibrary.prototype.getMediaListUrl = function () {
		return Harmoni.quickUrl('media', 'site_list', {'assetId': this.assetId});
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
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function MediaAsset ( assetId, xmlElement, library ) {
	if ( arguments.length > 0 ) {
		this.init( assetId, xmlElement, library );
	}
}

	/**
	 * Initilize this object
	 * 
	 * @param string assetId The id of the asset this media is attached to.
	 * @param object DOM_Element xmlElement
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.init = function ( assetId, xmlElement, library ) {
		this.library = library;
		this.assetId = assetId;
		
		this.id = xmlElement.getAttribute('id');
		this.repositoryId = xmlElement.getAttribute('repositoryId');
		this.displayName = xmlElement.getElementsByTagName('displayName')[0].firstChild.data;
		var descElement = xmlElement.getElementsByTagName('description')[0];
		if (descElement && descElement.firstChild)
			this.description = descElement.firstChild.data;
		else
			this.description = '';
		this.modificationDate = Date.fromISO8601(
			xmlElement.getElementsByTagName('modificationDate')[0].firstChild.data);
		if (xmlElement.getElementsByTagName('title').length)
			this.title = xmlElement.getElementsByTagName('title')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('source').length)
			this.source = xmlElement.getElementsByTagName('source')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('creator').length)
			this.creator = xmlElement.getElementsByTagName('creator')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('publisher').length)
			this.publisher = xmlElement.getElementsByTagName('publisher')[0].firstChild.data;
		if (xmlElement.getElementsByTagName('date').length) {
			this.date = new Date( xmlElement.getElementsByTagName('date')[0].firstChild.data);
		}
		
		// Authorizations
		this.canEdit = false;
		this.canDelete = false;
		var azs = xmlElement.getElementsByTagName('authorization');
		for (var i = 0; i < azs.length; i++) {
			switch (azs[i].getAttribute('function')) {
			case 'edu.middlebury.authorization.modify':
				this.canEdit = true;
				break;
			case 'edu.middlebury.authorization.delete':
				this.canDelete = true;
				break;
			}
		}
		
		// AZ Icon
		if (xmlElement.getElementsByTagName('permsHtml').length) {
			this.permsHtml = xmlElement.getElementsByTagName('permsHtml')[0].firstChild.data;
		}
		
		this.files = new Array();
		var mediaElements = xmlElement.getElementsByTagName('file');
		for (var i = 0; i < mediaElements.length; i++) {
			this.files.push(new MediaFile(mediaElements[i], this, this.library));
		}
		
		this.uploadFormDefaults = new Array();
	}
	
	/**
	 * Force-reload the whole media library. needed for IE crap.
	 * 
	 * @return void
	 * @access public
	 * @since 2/20/08
	 */
	MediaAsset.prototype.forceReload = function () {
		this.library.forceReload();
	}
	
	/**
	 * Answer the HTML element for this object
	 * 
	 * @return DOM_Element
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.getEntryElement = function () {
		// if our entry element doesn't exist, create it
		if (!this.entryElement) {
			this.entryElement = document.createElement('tbody');
		}
		// if it exists, clear out its contents
		else {
			this.entryElement.innerHTML = '';
		}
		
		var row = this.entryElement.appendChild(document.createElement('tr'));
		
		var filesElement = row.appendChild(document.createElement('td'));
		if (this.files.length) {
			for (var i = 0; i < this.files.length; i++) {
				this.files[i].render(filesElement);
			}
		}
		
		this.infoElement = row.appendChild(document.createElement('td'));
		this.writeInfo(this.infoElement);
		
		var dateElement = row.appendChild(document.createElement('td'));
		dateElement.className = 'modification_date';
		if (this.modificationDate)
			dateElement.innerHTML = this.modificationDate.toFormatedString('E NNN dd, yyyy h:mm a');
		
		if (this.canEdit || this.canDelete) {
			var editDiv = dateElement.appendChild(document.createElement('div'));
			
			if (this.canEdit) {
				this.editLink = editDiv.appendChild(document.createElement('a'));
				this.editLink.innerHTML = 'edit file';
				this.editLink.mediaAsset = this;
				this.editLink.onclick = function () {
					this.mediaAsset.toggleForm();
				}
				
				editDiv.appendChild(document.createTextNode(' '));
			}
			
			if (this.canDelete) {
				this.deleteLink = editDiv.appendChild(document.createElement('a'));
				this.deleteLink.innerHTML = 'delete file';
				this.deleteLink.mediaAsset = this;
				this.deleteLink.onclick = function () {
					if (confirm('Are you sure that you want to delete this file? If it is used in the site, links to it will be broken.'))
					{
						this.mediaAsset.deleteAsset();
					}
				}
			}
		}
		
		var permsElement = row.appendChild(document.createElement('td'));
		permsElement.className = 'perms_col';
		if (this.permsHtml) {
			permsElement.innerHTML = this.permsHtml;
		}
		
		return this.entryElement;
	}
	
	/**
	 * Delete the media asset
	 * 
	 * @return void
	 * @access public
	 * @since 10/25/07
	 */
	MediaAsset.prototype.deleteAsset = function () {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('media', 'delete', {'assetId': this.assetId, 'mediaAssetId': this.id});
		if (req) {
			// Define a variable to point at this mediaAsset that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var mediaAsset = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						if (!req.responseXML)
							alert(req.responseText);
						mediaAsset.completeDelete(req.responseXML);
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
	 * Complete the deletion of the asset, cleaning up the UI
	 * 
	 * @param DOM_Document xmldoc
	 * @return void
	 * @access public
	 * @since 10/25/07
	 */
	MediaAsset.prototype.completeDelete = function (xmldoc) {
		
		var errors = xmldoc.getElementsByTagName('error');
		if (errors.length) {
			for (var i = 0; i < errors.length; i++) {
				alert('Error: ' + errors[i].firstChild.data);
				throw new Error(errors[i].firstChild.data);
			}
		}
		
		if (this.library.updateQuotaFromDocument)
			this.library.updateQuotaFromDocument(xmldoc);
		
		this.entryElement.parentNode.removeChild(this.entryElement);
	}
	
	/**
	 * Toggle display of info and form
	 * 
	 * @return void
	 * @access public
	 * @since 2/13/07
	 */
	MediaAsset.prototype.toggleForm = function () {
		// If we are showing the form, delete it and replace it with the info
		if (this.uploadForm) {
			this.closeForm();
		} 
		// If we aren't showing the form, show the form
		else {
			this.openForm();
		}
	}
	
	/**
	 * Close the form
	 * 
	 * @return void
	 * @access public
	 * @since 2/14/07
	 */
	MediaAsset.prototype.closeForm = function () {
		if (this.uploadForm)
			delete this.uploadForm;
		this.infoElement.innerHTML = '';
		this.writeInfo(this.infoElement);
		this.editLink.innerHTML = 'edit file';
	}
	
	/**
	 * Open the form
	 * 
	 * @return void
	 * @access public
	 * @since 2/14/07
	 */
	MediaAsset.prototype.openForm = function () {
		this.createForm();
		this.editLink.innerHTML = 'cancel';
	}
	
	/**
	 * Write the info to the infoElement
	 * 
	 * @param DOM_Element container
	 * @return void
	 * @access public
	 * @since 2/13/07
	 */
	MediaAsset.prototype.writeInfo = function ( container ) {
		displayNameElement = container.appendChild(document.createElement('div'))
		displayNameElement.innerHTML = this.displayName;
		displayNameElement.className = 'displayName';
		
		if (this.description) {
			var descriptionElement = container.appendChild(document.createElement('div'));
			descriptionElement.innerHTML = this.description;
			descriptionElement.className = 'description';
		}
		
		var citationElement = container.appendChild(document.createElement('div'));
		citationElement.className = 'citation';
		this.writeCitation(citationElement);
	}
	
	/**
	 * Answer a citation 
	 * 
	 * @param DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/31/07
	 */
	MediaAsset.prototype.writeCitation = function ( container ) {
		
		if (this.creator) {			
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.creator;
			
			container.appendChild(document.createTextNode('. '));
		}
		
		if (this.title) {
			container.appendChild(document.createTextNode('"'));
			
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.title;
			
			container.appendChild(document.createTextNode('" '));
		}
		
		if (this.source) {
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.source;
			element.style.fontStyle = 'italic';
			
			container.appendChild(document.createTextNode('. '));
		}
		
		if (this.publisher) {
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.publisher;
			
			container.appendChild(document.createTextNode(', '));
		}
		
		if (this.date) {
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.date.toFormatedString('yyyy');;
			
			container.appendChild(document.createTextNode(' '));
		}
		
		if (this.pages) {
			container.appendChild(document.createTextNode('('));
			
			var element = container.appendChild(document.createElement('span'));
			element.innerHTML = this.pages;
			
			container.appendChild(document.createTextNode(') '));
		}		
	}
	
	/**
	 * Create an edit form for this media asset
	 * 
	 * @return void
	 * @access public
	 * @since 2/13/07
	 */
	MediaAsset.prototype.createForm = function () {
		this.uploadForm = document.createElement('form');
		this.uploadForm.action = Harmoni.quickUrl('media', 'update', {'assetId': this.assetId, 'mediaAssetId': this.id});
		this.uploadForm.method = 'post';
		this.uploadForm.enctype = 'multipart/form-data';
		
		// IE doesn't like the form as-is. It seems to need to have it written to
		// a string and then re-loaded in order for everything to be submitted properly.
		if (getBrowser()[2] == 'msie') {
			var tempParent = document.createElement('div');
			tempParent.appendChild(this.uploadForm);
			var temp = tempParent.innerHTML;
			tempParent.innerHTML = temp;
			this.uploadForm = tempParent.getElementsByTagName('form')[0];
		}
		
		mediaAsset = this;
		this.uploadForm.onsubmit = function () {
			return AIM.submit(this, 
				{'onStart' : function() {mediaAsset.startUploadCallback()}, 
				'onComplete' : function(xmlDoc) {mediaAsset.completeUploadCallback(xmlDoc)}});
		}		
		
		var table = this.uploadForm.appendChild(document.createElement('table'));
		var tbody = table.appendChild(document.createElement('tbody'));
		
		if (this.files.length) {
			for (var i = 0; i < this.files.length; i++) {
				var row = tbody.appendChild(document.createElement('tr'));
				var heading = row.appendChild(document.createElement('th'));
				heading.innerHTML = 'File: ';
				
				var datum = row.appendChild(document.createElement('td'));
				// IE doesn't support setting of the name after create
				if (getBrowser()[2] == 'msie') {
					var dummyField = document.createElement('<input name="dummy">');
				} else {
					var dummyField = document.createElement('input');
					dummyField.name = 'dummy';
				}
				dummyField.type = 'text';
				dummyField.value = this.files[i].name;
				dummyField.readonly = 'readonly';
				dummyField.title = 'Click to Replace';
				dummyField.style.cursor = 'pointer';
				dummyField.mediaAsset = this;
				dummyField.fileId = this.files[i].recordId;
				dummyField.onclick = function () {
					// IE doesn't support setting of the name after create
					if (getBrowser()[2] == 'msie') {
						var uploadField = document.createElement('<input name="file___' + this.fileId+'">');
					} else {
						var uploadField = document.createElement('input');
						uploadField.name = 'file___' + this.fileId;
					}
					uploadField.dummyField = this;
					uploadField.type = 'file';
					uploadField.size = '10';
					this.parentNode.insertBefore(uploadField, this);
					
					var uploadCancel = document.createElement('input');
					uploadCancel.type = 'button';
					uploadCancel.value = 'cancel file replace';
					uploadCancel.dummyField = this;
					uploadCancel.uploadField = uploadField;
					uploadCancel.onclick = function () {
						this.parentNode.removeChild(this.uploadField);
						this.dummyField.style.display = 'inline';
						this.parentNode.removeChild(this);
						delete this.uploadField;
						try {
							delete this;
						} catch (error) {
						}
					}
					this.parentNode.insertBefore(uploadCancel, this);
					
					this.style.display = 'none';
					
// 					alert(this.form.innerHTML);
				}
				
				datum.appendChild(dummyField);
			}
		}
		// Allow the user to add a file
		else {
			this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
				'File', 'file', 'media_file', '', {'size': '10'});
		}
			
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'Name/Title', 'text', 'displayName', this.displayName);
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'Description', 'text', 'description', this.description);
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'Author/Creator', 'text', 'creator', this.creator || '');
		
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'Source', 'text', 'source', this.source || '');
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'Publisher', 'text', 'publisher', this.publisher || '');
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'[Pub.] Date', 'text', 'date', this.date || '');
		this.addFieldToRow(tbody.appendChild(document.createElement('tr')),
			'', 'submit', 'submit', 'Save Changes');
		
		
// 		var subDiv = this.uploadForm.appendChild(document.createElement('div'));
// 		var submit = subDiv.appendChild(document.createElement('input'));
// 		submit.type = 'submit';
		
		this.infoElement.innerHTML = '';
		this.infoElement.appendChild(this.uploadForm);
		
		this.uploadForm.elements[1].focus();
	}
	
	/**
	 * Add an input field to the given table row
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 2/13/07
	 */
	MediaAsset.prototype.addFieldToRow = FileLibrary.prototype.addFieldToRow;
	
	/**
	 * Start the upload callback process.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.startUploadCallback = function () {
		// IE 6 will not load properly, so set a timeout and reload soon after.
		if (getBrowser()[2] == 'msie' && getMajorVersion(getBrowser()[3]) < 7) {
			var currentObj = this;
			window.setTimeout(function() {currentObj.forceReload();}, 15000);
			alert("IE6 does not refresh properly. \nMediaLibrary will reload in 15 seconds. \nIf you file does not appear after the media library reloads, refresh the page and open the media library again.");
		}
		return true;
	}
	
	/**
	 * Finish the upload callback process.
	 * 
	 * @param DOM_Document xmldoc
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	MediaAsset.prototype.completeUploadCallback = function (xmldoc) {
		try {
			var responseElement = xmldoc.firstChild;
			
			var errors = responseElement.getElementsByTagName('error');
			if (errors.length) {
				for (var i = 0; i < errors.length; i++) {
					if (errors[i].hasAttribute('type') && errors[i].getAttribute('type') == 'ImageProcessingFailedException') {
						// do not notify on convert-failed errors
					} else {
						if (errors[i].hasAttribute('type'))
							var type = errors[i].getAttribute('type') + ": ";
						else
							var type = ''
						alert(type + errors[i].firstChild.data);
// 						throw new Error( errors[i].firstChild.data );
					}
				}
			}
		} catch (error) {
			alert (error);
			
			if (responseElement.xml)
				alert(responseElement.xml);
			else {
// 				var xmlSerializer = new XMLSerializer();
// 				alert(xmlSerializer.serializeToString(responseElement));
			}
			
			return false;
		}
		
		if (this.library.updateQuotaFromDocument)
			this.library.updateQuotaFromDocument(xmldoc);
		
// 		var xmlSerializer = new XMLSerializer();
// 		alert(xmlSerializer.serializeToString(responseElement));
		var fileAssets = responseElement.getElementsByTagName('asset');
		if (fileAssets.length) {
			for (var i = 0; i < fileAssets.length; i++) {
				this.init(this.assetId, fileAssets.item(i), this.library);
			
				// refresh our row.
				if (this.entryElement) {
					var nextSibling = this.entryElement.nextSibling;
					var parent = this.entryElement.parentNode;
					parent.removeChild(this.entryElement);
					parent.insertBefore(this.getEntryElement(), nextSibling);
					
					// Refresh the thumbnail images.
					for (var i = 0; i < this.files.length; i++) {
						this.files[i].refreshThumbnail();
					}
				}
				
				break;
			}
		} else if (getBrowser()[2] == 'msie') {
			// IE Renders the iframe document and barfs up the xml. Just reinitialize
			// the entire media library as that is easier
			this.forceReload();
// 			alert('Error in file upload response: no files listed. ');
		}
		this.closeForm();
		
		return true;
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
 * @version $Id: MediaLibrary.js,v 1.24 2008/04/11 21:50:56 adamfranco Exp $
 */
function MediaFile ( xmlElement, asset, library) {
	if ( arguments.length > 0 ) {
		this.init( xmlElement, asset, library );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param <##>
	 * @return void
	 * @access protected
	 * @since 1/31/07
	 */
	MediaFile.prototype.init = function ( xmlElement, asset, library ) {
		this.asset = asset;
		this.library = library;
		this.recordId = xmlElement.getAttribute('id');
		this.mimeType = xmlElement.getAttribute('mimetype');
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
	 * @access protected
	 * @since 1/31/07
	 */
	MediaFile.prototype.render = function ( container ) {
		var fileDiv = container.appendChild(document.createElement('div'));
		fileDiv.style.whiteSpace = 'nowrap';
		
		var useButton = fileDiv.appendChild(document.createElement('button'));
		useButton.innerHTML = 'use';
		
		var allowedTypes = this.library.getAllowedMimeTypes();
		if (!allowedTypes || !allowedTypes.length 
			|| allowedTypes.elementExists(this.mimeType))
		{
			useButton.onclick = this.library.onUse.bind(this.library, this);
		} else {
			useButton.disabled = true;
		}
		
		fileDiv.appendChild(document.createTextNode(' '));
		
		var imageLink = fileDiv.appendChild(document.createElement('a'));
		imageLink.href = this.url;
		
		this.thumbnail = imageLink.appendChild(document.createElement('img'));
		this.thumbnail.src = this.thumbnailUrl;
		this.thumbnail.align = 'center';
		
// 		var url = this.url;
// 		this.img.onclick = function () {
// 			window.open (url, this.recordId, "height=400,width=600,resizable=yes,scrollbars=yes");
// 		}
		this.thumbnail.className = 'thumbnail link';
		
// 		container.appendChild(this.img);
	}
	
	/**
	 * Refresh the thumbnail image
	 * 
	 * @return void
	 * @access public
	 * @since 10/25/07
	 */
	MediaFile.prototype.refreshThumbnail = function () {
		if (this.thumbnail) {
			if (this.thumbnail.src.match(/\?/))
				this.thumbnail.src = this.thumbnail.src + '&refresh=' + Math.random();
			else
				this.thumbnail.src = this.thumbnail.src + '?refresh=' + Math.random();
		}
	}
	
	
	
/*********************************************************
 * Begin - MediaFile public API
 *
 * The methods below comprise the public API for working
 * with MediaFiles in Segue. These methods work hand-in-hand
 * with the equivalent MediaFile PHP class.
 *********************************************************/
	/**
	 * Answer a string identifier for this file
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getId = function () {
		return "repositoryId=" + this.asset.repositoryId 
			+ "&assetId=" + this.asset.id
			+ "&recordId=" + this.recordId;
	}
	
	/**
	 * Answer the URL to the file
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getUrl = function () {
		return this.url;
	}
	
	/**
	 * Answer the thumbnail URL
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getThumbnailUrl = function () {
		return this.thumbnailUrl;
	}
	
	/**
	 * Answer the filename
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getFilename = function () {
		return this.name;
	}
	
	/**
	 * Answer the size of the file in bytes
	 * 
	 * @return integer
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getSize = function () {
		return this.size;
	}
	
	/**
	 * Answer the MIME type of the file
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getMimeType = function () {
		return this.mimeType;
	}
	
	/**
	 * Answer the modification date
	 * 
	 * @return object Date
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getModificationDate = function () {
		return this.asset.modificationDate;
	}
	
	/**
	 * Answer an array of all titles
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getTitles = function () {
		return [this.asset.title];
	}
	
	/**
	 * Answer an array of all descriptions
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getDescriptions = function () {
		return [this.asset.description];
	}
	
	/**
	 * Answer an array of all creators
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getCreators = function () {
		return [this.asset.creator];
	}
	
	/**
	 * Answer an array of all subjects
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getSubjects = function () {
		return [this.asset.subject];
	}
	
	/**
	 * Answer an array of all contributors
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getContributors = function () {
		return [this.asset.contributor];
	}
	
	/**
	 * Answer an array of all Dates
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getDates = function () {
		return [this.asset.date];
	}
	
	/**
	 * Answer an array of all Formats
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getFormats = function () {
		return [this.asset.format];
	}
	
	/**
	 * Answer an array of all Publishers
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getPublishers = function () {
		return [this.asset.publisher];
	}
	
	/**
	 * Answer an array of all languages
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getLanguages = function () {
		return [];
	}
	
	/**
	 * Answer an array of all types
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getTypes = function () {
		return [this.mimeType];
	}
	
	/**
	 * Answer an array of all rights strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getRights = function () {
		return [];
	}
	
	/**
	 * Answer an array of all sources
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getSources = function () {
		return [];
	}
	
	/**
	 * Answer an array of all relations
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.getRelations = function () {
		return [];
	}
	
	/**
	 * Write a citation into the DOM element passed
	 * 
	 * @param object DOM_Element container
	 * @return void
	 * @access public
	 * @since 4/30/07
	 */
	MediaFile.prototype.writeCitation = function (container) {
		this.asset.writeCitation( container );
	}

/*********************************************************
 * End - MediaFile public API
 *********************************************************/


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
//         var style = 'display: block; height: 200px; width: 800px;'; // For debugging
        var style = 'display: none;'; 	// Normal case
        
        d.innerHTML = '<iframe style="' + style + '" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+n+'\')"></iframe>';
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
        // Safari will sometimes not have d.location set, but will have the baseURI property set, 
        // so check for both as well as the value.
//         alert("location: " + d.location + "\nbaseURI: " + d.baseURI);
	    if ((!d.location || d.location.href == "about:blank") && (!d.baseURI || d.baseURI == "about:blank")) {
    		return;
        }
//         alert("completing");

        if (typeof(i.onComplete) == 'function') {
//            i.onComplete(d.body.innerHTML);
			// My action is returning an XML document, so I want to return that
			
			// rather than an HTML page
			 i.onComplete(d);
        }
    }

}


/**
 * Dynamically include scripts using the Prototype library.
 * Script.include(url);
 *
 * by aemkei: http://stackoverflow.com/questions/21294/how-do-you-dynamically-load-a-javascript-file-think-cs-include#242607
 * 
 * @since 1/12/09
 */
var Script = {
  _loadedScripts: [],
  include: function(script){
    // include script only once
    if (this._loadedScripts.include(script)){
      return false;
    }
    // request file synchronous
    var request = new Ajax.Request(script, {
      asynchronous: false, method: "GET",
      evalJS: false, evalJSON: false
    });
    if (!request.success())
    	throw "Error loading JS file '" + script + "'.";
    var code = request.transport.responseText;
    
    // eval code on global level
    if (Prototype.Browser.IE) {
      window.execScript(code);
    } else if (Prototype.Browser.WebKit){
      $$("head").first().insert(Object.extend(
        new Element("script", {type: "text/javascript"}), {text: code}
      ));
    } else {
      window.eval(code);
    }
    // remember included script
    this._loadedScripts.push(script);
  }
};