/**
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * This class is a media library that provides access to videos stored in Middlebury's MiddMedia
 * service
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function MiddMediaLibrary ( owner, config, caller, container ) {
	if ( arguments.length > 0 ) {
		this.init( owner, config, caller, container );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param MediaLibrary owner
	 * @param object config
	 * @param DOM_Element	caller 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @param DOM_Element	container the container to render our content in.
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.init = function ( owner, config, caller, container ) {
		if (!owner)
			throw "the owning Media library was not given.";
		if (typeof owner.close != 'function')
			throw "owner must support the close() method";
		if (typeof owner.onContentChange != 'function')
			throw "owner must support the onContentChange() method";
		if (typeof owner.forceReload != 'function')
			throw "owner must support the forceReload() method";
		if (typeof owner.center != 'function')
			throw "owner must support the center() method";
		if (owner.allowedMimeTypes && typeof owner.allowedMimeTypes != 'object')
			throw "owner.allowedMimeTypes must be undefined or an array";
		this.owner = owner;
		
		if (!config)
			throw "the config was not given.";
		this.config = config;
		
		if (!caller)
			throw "the caller was not given.";
		if (typeof caller.onUse != 'function')
			throw "caller must support the onUse() method";
		this.caller = caller;
		
		if (!container)
			throw "the container was not given.";
		if (typeof container.appendChild != 'function'
			// IE case
			&& typeof container.appendChild != 'object')
		{
			throw "container must support the appendChild() method";
		}
		this.container = container;
	}
	
	/**
	 * Open the tab and build our initial view
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.onOpen = function () {
		// Load our directories 
		if (!this.directories) {
			this.fetchDirectories();
		}
	}
	
	MiddMediaLibrary.prototype.onUse = FileLibrary.prototype.onUse;
	
	/**
	 * Fetch a list of directories and send them to our loading callback
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.fetchDirectories = function (callback) {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('middmedia', 'getDirs');
		if (req) {
			// Define a variable to point at this MediaLibrary that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var library = this;
			
			if (!callback) {
				callback = function (xmldoc) {
					library.loadDirectories(xmldoc);
				}
			}
			
			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						callback(req.responseXML);
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
	 * Given a list of directories, create directory objects
	 * 
	 * @param DOM_Document xmldoc
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.loadDirectories = function (xmldoc) {
		// Load the list of allowed file types while we have the info.
		this.loadAllowedFileTypes(xmldoc);
		
		this.directories = {};
		var dirs = xmldoc.getElementsByTagName('directory');
		for (var i = 0; i < dirs.length; i++) {
			this.directories[dirs[i].getAttribute('name')] =
				new MiddMediaDirectory(this,
					dirs[i].getAttribute('name'),
					dirs[i].getAttribute('bytesUsed'),
					dirs[i].getAttribute('bytesAvailable'));
		}
		
		// Make the directory-select menu.
		this.directorySelect = this.container.appendChild(document.createElement('select'));
		var library = this;
		this.directorySelect.onchange = function () {
			UserData.instance().setPreference('MiddMedia_current_dir', this.value);
			if (this.value) {
				library.displayDirectory(this.value);
			}
		}
		
		var option = this.directorySelect.appendChild(document.createElement('option'));
		option.value = null;
		option.innerHTML = "Select a directory...";
		
		var numDirs = 0;
		for (var name in this.directories) {
			var option = this.directorySelect.appendChild(document.createElement('option'));
			option.value = name;
			option.innerHTML = name;
			if (name == UserData.instance().getPreference('MiddMedia_current_dir')) {
				option.selected = true;
				library.displayDirectory(name);
			}
			numDirs++;
		}
		
		if (!numDirs) {
			this.container.innerHTML = "<p class='error'>Your account is not authorized to upload to the MiddMedia system.</p>";
			return;
		}
	}
	
	/**
	 * Reload the quotas from the server
	 * 
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaLibrary.prototype.reloadQuotas = function () {
		library = this;
		var callback = function (xmldoc) {
			var dirs = xmldoc.getElementsByTagName('directory');
			for (var i = 0; i < dirs.length; i++) {
				var dir = library.directories[dirs[i].getAttribute('name')];
				dir.updateQuota(dirs[i].getAttribute('bytesUsed'), dirs[i].getAttribute('bytesAvailable'));
			}
		}
		this.fetchDirectories(callback);
	}
	
	/**
	 * Load the allowed file types listed in the document
	 * 
	 * @param DOM_Document xmldoc
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.loadAllowedFileTypes = function (xmldoc) {
		this.allowedFileExtensions = [];
		this.allowedMimeTypes = [];
		var types = xmldoc.getElementsByTagName('allowedFileType');
		for (var i = 0; i < types.length; i++) {
			this.allowedFileExtensions.push(types[i].getAttribute('extension'));
			this.allowedMimeTypes.push(types[i].getAttribute('mimeType'));
		}
	}
	
	/**
	 * Write out the contents and controls for a directory
	 * 
	 * @param string dirName
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaLibrary.prototype.displayDirectory = function (dirName) {
		var dir = this.directories[dirName];
		this.currentDirectory = dir;
		
		if (this.dirContainer)
			this.dirContainer.innerHTML = '';
		else
			this.dirContainer = this.container.appendChild(document.createElement('div'));
		
		dir.createQuotaDisplay(this.dirContainer);
		
		dir.createUploadForm(this.dirContainer);
		
		dir.displayFiles(this.dirContainer);
	}
	
	MiddMediaLibrary.prototype.getAllowedMimeTypes = FileLibrary.prototype.getAllowedMimeTypes;
	MiddMediaLibrary.prototype.onContentChange = FileLibrary.prototype.onContentChange;


/**
 * The MiddMediaDirectory is a representation of a directory on the MiddMedia streaming
 * server
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function MiddMediaDirectory ( library, name, bytesUsed, bytesAvailable ) {
	if ( arguments.length > 0 ) {
		this.init( library, name, bytesUsed, bytesAvailable );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param MediaLibrary owner
	 * @param string name
	 * @param int bytesUsed
	 * @param int bytesAvailable
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaDirectory.prototype.init = function ( library, name, bytesUsed, bytesAvailable ) {
		this.library = library;
		this.name = name;
		
		this.quotaTitle = "Media quota for the '" + name + "' directory:";
		
		bytesUsed = new Number(bytesUsed);
		bytesAvailable = new Number(bytesAvailable);
		
		this.quota = bytesUsed + bytesAvailable;
		this.quotaUsed = bytesUsed;
	}
	
	/**
	 * Update Quota
	 * 
	 * @param int bytesUsed
	 * @param int bytesAvailable
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaDirectory.prototype.updateQuota = function (bytesUsed, bytesAvailable) {
		bytesUsed = new Number(bytesUsed);
		bytesAvailable = new Number(bytesAvailable);
		
		this.quota = bytesUsed + bytesAvailable;
		this.quotaUsed = bytesUsed;
		
		this.writeQuota();
	}
	
	// Take on the quota-display methods of the AssetLibrary
	MiddMediaDirectory.prototype.createQuotaDisplay = AssetLibrary.prototype.createQuotaDisplay;
	MiddMediaDirectory.prototype.writeQuota = AssetLibrary.prototype.writeQuota;
// 	MiddMediaDirectory.prototype.updateQuota = AssetLibrary.prototype.updateQuota;
	
	/**
	 * Force-reload the whole media library. needed for IE crap.
	 * 
	 * @return void
	 * @access public
	 * @since 2/20/08
	 */
	MiddMediaDirectory.prototype.forceReload = function () {
		this.files = null;
		this.library.displayDirectory(this.name);
	}
	
	/**
	 * Answer an upload form
	 * 
	 * @param DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaDirectory.prototype.createUploadForm = function (container) {
		var heading = container.appendChild(document.createElement('div'));
		heading.className = 'media_quota_title';
		heading.innerHTML = 'Upload an audio or video file:';
		
		this.uploadForm = document.createElement('form');
		this.uploadForm.action = Harmoni.quickUrl('middmedia', 'addVideo', {'directory': this.name});
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
		var directory = this;
		this.uploadForm.onsubmit = function () {
			var matches = this.media_file.value.match(/.+\.([a-z0-9]+)$/);
			if (!matches) {
				alert("Please choose a file");
				return false;
			}
			
			var extension = matches[1];
			
			if (!directory.library.allowedFileExtensions.elementExists(extension)) {
				var message = "Only files of the following types can be uploaded to MiddMedia:\n\t";
				for (var i = 0; i < directory.library.allowedFileExtensions.length; i++) {
					message += "\n\t." + directory.library.allowedFileExtensions[i];
					message += "\t\t(" + directory.library.allowedMimeTypes[i] + ")";
				}
				
				message += "\n\n." + extension + " is now allowed.";
				alert(message);
				this.media_file.value = '';
				return false;
			}
			
			return AIM.submit(this, 
				{'onStart' : function() {directory.startUploadCallback()}, 
				'onComplete' : function(xmlDoc) {directory.completeUploadCallback(xmlDoc)}});
		}
		
		this.uploadForm.innerHTML = "<input type='file' name='media_file' /> <input type='submit' value='Upload'/>";
		
		var note = document.createElement('div');
		note.style.fontStyle = 'italic';
		var message = "Only files of the following types can be uploaded to MiddMedia:\n\t";
		for (var i = 0; i < directory.library.allowedFileExtensions.length; i++) {
			message += "\n\t<br/>&nbsp; &nbsp; &nbsp; &nbsp;." + directory.library.allowedFileExtensions[i];
			message += "\t\t(" + directory.library.allowedMimeTypes[i] + ")";
		}
		note.innerHTML = message + " <br/>See <a href='https://mediawiki.middlebury.edu/wiki/LIS/MiddMedia' target='_blank'>MiddMeda Help</a> for more information.\n<br/><br/>For faster multi-file uploads use <a href='https://middmedia.middlebury.edu/' target='_blank'>MiddMeda</a> directly.";
		container.appendChild(note);
		
		container.appendChild(this.uploadForm);
		
		note.style.cssFloat = 'right';
		note.style.float = 'right';
	}
	
	/**
	 * Start the upload callback process.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	MiddMediaDirectory.prototype.startUploadCallback = function () {
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
	MiddMediaDirectory.prototype.completeUploadCallback = function (xmldoc) {
// 		alert('xmldoc = ' + xmldoc);
// 		alert('xmldoc.firstChild = ' + xmldoc.firstChild);
// 		alert('xmldoc.documentElement = ' + xmldoc.documentElement);
// 		alert('xmldoc.documentElement.firstChild = ' + xmldoc.documentElement.firstChild);
		try {
			var responseElement = xmldoc.firstChild;
			
			var errors = responseElement.getElementsByTagName('error');
			if (errors.length) {
				for (var i = 0; i < errors.length; i++) {
					if (errors[i].hasAttribute('type'))
						var type = errors[i].getAttribute('type') + ": ";
					else
						var type = ''
					alert(type + errors[i].firstChild.data);
//					throw new Error( errors[i].firstChild.data );
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
			
		var files = responseElement.getElementsByTagName('file');
		if (files.length) {
			for (var i = 0; i < files.length; i++) {
				this.addMediaFile(new MiddMediaFile(this.library, this, files[i]));
			}
		} else if (getBrowser()[2] == 'msie') {
			// IE Renders the iframe document and barfs up the xml. Just reinitialize
			// the entire media library as that is easier
			this.forceReload();
// 			alert('Error in file upload response: no files listed. ');
			return;
		}
		
		// In Safari, the FCKEditor area gets popped to the front, move our panel back in front
		if (getBrowser()[0] == 'safari') {
			var owner = this.library.owner;
			window.setTimeout(function () {
				owner.moveToFront();
			}, 10);
			
			// Set a second timeout, just to be sure we don't miss it.
			window.setTimeout(function () {
				owner.moveToFront();
			}, 500);
		}
		
		this.uploadForm.media_file.value = '';
		
		this.library.owner.center();
		
		this.library.reloadQuotas();
		
		return true;
	}
	
	/**
	 * Remove a file. This function will remove a file from the directory, but not 
	 * delete it. It is used by the file to unlink itself from the directory
	 * 
	 * @param string fileName
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaDirectory.prototype._removeFile = function (fileName) {
		var newFiles = [];
		for (var i = 0; i < this.files.length; i++) {
			if (this.files[i].name != fileName)
				newFiles.push(this.files[i]);
		}
		this.files = newFiles;
		
		this.library.reloadQuotas();
	}
	
	/**
	 * Build a listing of the files in this directory
	 * 
	 * @param DOM_Element container
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaDirectory.prototype.displayFiles = function (container) {
		if (!this.files) {
			var req = Harmoni.createRequest();
			var url = Harmoni.quickUrl('middmedia', 'getVideos', {directory: this.name});
			if (req) {
				// Define a variable to point at this MediaLibrary that will be in the
				// scope of the request-processing function, since 'this' will (at that
				// point) be that function.
				var directory = this;
	
				req.onreadystatechange = function () {
					// only if req shows "loaded"
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200) {
	// 						alert(req.responseText);
							directory.loadFiles(req.responseXML);
							directory.displayFiles(container);
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
			
			return;
		}
		
		// Print out the listing of files.
		this.mediaList = document.createElement('table');	
		this.mediaList.className = 'medialist';
		this.mediaListHead = this.mediaList.appendChild(document.createElement('thead'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode(' '));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('name'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('type'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('size'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('modification date'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		element.appendChild(document.createTextNode('creator'));
		
		var element = this.mediaListHead.appendChild(document.createElement('th'));
		
		this.mediaListBody = this.mediaList.appendChild(document.createElement('tbody'));
		
		container.appendChild(this.mediaList);
		
		
		for (var i = 0; i < this.files.length; i++) {
			this.mediaListBody.appendChild(this.files[i].getListingRow());
		}
		
		this.library.onContentChange();
	}
	
	/**
	 * Load the file listing from the XML document
	 * 
	 * @param DOM_Document xmldoc
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaDirectory.prototype.loadFiles = function (xmldoc) {
		this.files = [];
		var files = xmldoc.getElementsByTagName('file');
		for (var i = 0; i < files.length; i++) {
			this.files.push(new MiddMediaFile(this.library, this, files[i]));
		}
	}
	
	/**
	 * Add a media file and add it to our output
	 * 
	 * @param MiddMediaFile file
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaDirectory.prototype.addMediaFile = function (file) {
		this.files.push(file);
		this.mediaListBody.insertBefore(file.getListingRow(), this.mediaListBody.firstChild);
	}

/**
 * This class represents a media file in the MiddMedia system. 
 * 
 * To be transparently usable in segue it should support the same methods as the
 * regular media file so as to not cause problems when it is selected and passed
 * back to the calling client
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function MiddMediaFile ( library, directory, xmlElement ) {
	if ( arguments.length > 0 ) {
		this.init( library, directory, xmlElement );
	}
}

	/**
	 * Initialize a new object
	 * 
	 * @param MiddMediaLibrary library
	 * @param MiddMediaDirectory directory
	 * @param DOM_Element xmlElement
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaFile.prototype.init = function ( library, directory, xmlElement ) {
		this.library = library;
		this.directory = directory;
		
		this.mimeType = xmlElement.getAttribute('mimeType');
		this.name = xmlElement.getAttribute('name');
		this.size = new Number(xmlElement.getAttribute('size'));
		
		this.url = xmlElement.getAttribute('httpUrl');
		this.url = decodeURI(this.url);
		this.url = this.url.urlDecodeAmpersands();
		
		this.date = Date.fromISO8601(xmlElement.getAttribute('date'));
		
		this.creator = xmlElement.getAttribute('creator');
		
		if (xmlElement.getAttribute('thumbUrl').length > 0) {
			this.thumbnailUrl = xmlElement.getAttribute('thumbUrl');
			this.thumbnailUrl = decodeURI(this.thumbnailUrl);
			this.thumbnailUrl = this.thumbnailUrl.urlDecodeAmpersands();
		}
		
		this.embedCode = xmlElement.getElementsByTagName('embedCode')[0].firstChild.data;
	}
	
	/**
	 * Answer a table row for this file
	 * 
	 * @return DOM_Element
	 * @access public
	 * @since 1/13/09
	 */
	MiddMediaFile.prototype.getListingRow = function () {
		var row = document.createElement('tr');
		
		// Use button
		var datum = row.appendChild(document.createElement('td'));
		datum.style.whiteSpace = 'nowrap';
		
		var useButton = datum.appendChild(document.createElement('button'));
		useButton.innerHTML = 'use';
		
		var allowedTypes = this.library.getAllowedMimeTypes();
		if (!allowedTypes || !allowedTypes.length 
			|| allowedTypes.elementExists(this.mimeType))
		{
			useButton.onclick = this.library.onUse.bind(this.library, this);
		} else {
			useButton.disabled = true;
		}
		
		datum.appendChild(document.createTextNode(' '));
		var imageLink = datum.appendChild(document.createElement('a'));
		if (this.embedCode) {
			imageLink.href = '#';
			var file = this;
			imageLink.onclick = function() {
				if (this.panel) {
					this.panel.open();
				} else {
					var panel = new CenteredPanel('Previewing ' + file.getTitles()[0], 400, 500, this);
					panel.contentElement.style.textAlign = 'center';
					
					var mediaContainer = panel.contentElement.appendChild(document.createElement('p'));
					mediaContainer.innerHTML = file.embedCode;
					
					this.panel.open();
					
					// Force the panel to clear the embed code and delete itself to 
					// stop any playing media. Otherwise media keeps playing in the background
					panel.onClose = function () {
						var objects = this.contentElement.getElementsByTagName("embed");
						for (var i=0; i < objects.length; i++) {
							// It seems that setting the src attribute to null is the only way to stop the Flow player
							// This doesn't seem to stop the video in IE 7
							objects[i].src = null;
						}

						this.contentElement = null;
						this.screen.style.display = 'none';
						this.positionElement.panel = null;
					}
				}
				
				return false;
			}
		} else {
			imageLink.href = this.url;
			imageLink.target = '_blank';	
		}
		
		this.thumbnail = imageLink.appendChild(document.createElement('img'));
		this.thumbnail.src = this.getThumbnailUrl();
		this.thumbnail.align = 'center';
		
// 		var url = this.url;
// 		this.img.onclick = function () {
// 			window.open (url, this.recordId, "height=400,width=600,resizable=yes,scrollbars=yes");
// 		}
		this.thumbnail.className = 'thumbnail link';
		
		// Name
		var datum = row.appendChild(document.createElement('td'));
		datum.innerHTML = this.name;
		
		// MIME Type
		var datum = row.appendChild(document.createElement('td'));
		datum.innerHTML = this.mimeType;
		
		// Size
		var datum = row.appendChild(document.createElement('td'));
		datum.innerHTML = this.size.asByteSizeString();
		
		// Date
		var datum = row.appendChild(document.createElement('td'));
		datum.innerHTML = this.date.toFormatedString('E NNN dd, yyyy h:mm a');
		
		// Creator
		var datum = row.appendChild(document.createElement('td'));
		datum.innerHTML = this.creator;
		
		// Delete link
		var datum = row.appendChild(document.createElement('td'));
		var link = datum.appendChild(document.createElement('a'));
		link.href = '#';
		link.innerHTML = 'delete';
		var mediaFile = this;
		link.onclick = function () {
			if (!confirm("Deleting this file will remove it from the MiddMedia service. It will no longer be accessible from anywhere it has been used.\n\nAre you sure you want to delete?"))
			{
				return false;
			}
			
			mediaFile.deleteSelf();
			
			var row = this.parentNode.parentNode;
			row.parentNode.removeChild(row);
			return false;
		}
		
		return row;
	}
	
	/**
	 * Delete this file
	 * 
	 * @return void
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaFile.prototype.deleteSelf = function () {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('middmedia', 'delVideo', {directory: this.directory.name, file: this.name});
		if (req) {
			// Define a variable to point at this MediaLibrary that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var directory = this.directory;
			var fileName = this.name;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						var errors = req.responseXML.getElementsByTagName('error');
						for (var i = 0; i < errors.length; i++) {
							alert(errors[i].firstChild.nodeValue);
						}
						directory._removeFile(fileName);
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
	MiddMediaFile.prototype.getId = function () {
		return this.url;
	}
	
	/**
	 * Answer the URL to the file
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getUrl = function () {
		return this.url;
	}
	
	/**
	 * Answer the thumbnail URL
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getThumbnailUrl = function () {
		if (this.thumbnailUrl)
			return this.thumbnailUrl;
		
		base = Harmoni.POLYPHONY_PATH + '/icons/filetypes/';
		switch(this.mimeType.split('/')[0]) {
			case 'video':
				return base + 'video.png';
			case 'audio':
				return base + 'sound.png';
			default:
				return base + 'unknown.png';
		}
	}
	
	/**
	 * Answer the text-template code used for embedding this file
	 *
	 * Will throw an exception if unsupported
	 * 
	 * @return string
	 * @access public
	 * @since 1/14/09
	 */
	MiddMediaFile.prototype.getEmbedTextTemplate = function () {
		switch(this.mimeType) {
			case 'video/x-flv':
				return '{{video|service=middmedia|dir=' + this.directory.name + '|id=' + this.name + '}}';
			case 'video/mp4':
				return '{{video|service=middmedia|dir=' + this.directory.name + '|id=' + this.name + '}}';
			case 'audio/mpeg':
				return '{{audio|url=' + this.url + '}}';
			default:
				throw "Embedding '" + this.mimeType + "' files is unsupported";
		}
	}
	
	/**
	 * Answer the filename
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getFilename = function () {
		return this.name;
	}
	
	/**
	 * Answer the size of the file in bytes
	 * 
	 * @return integer
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getSize = function () {
		return this.size;
	}
	
	/**
	 * Answer the MIME type of the file
	 * 
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getMimeType = function () {
		return this.mimeType;
	}
	
	/**
	 * Answer the modification date
	 * 
	 * @return object Date
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getModificationDate = function () {
		return this.date;
	}
	
	/**
	 * Answer an array of all titles
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getTitles = function () {
		return [this.name];
	}
	
	/**
	 * Answer an array of all descriptions
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getDescriptions = function () {
		return [];
	}
	
	/**
	 * Answer an array of all creators
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getCreators = function () {
		return [];
	}
	
	/**
	 * Answer an array of all subjects
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getSubjects = function () {
		return [];
	}
	
	/**
	 * Answer an array of all contributors
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getContributors = function () {
		return [];
	}
	
	/**
	 * Answer an array of all Dates
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getDates = function () {
		return [];
	}
	
	/**
	 * Answer an array of all Formats
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getFormats = function () {
		return [this.mimeType];
	}
	
	/**
	 * Answer an array of all Publishers
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getPublishers = function () {
		return [];
	}
	
	/**
	 * Answer an array of all languages
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getLanguages = function () {
		return [];
	}
	
	/**
	 * Answer an array of all types
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getTypes = function () {
		return [this.mimeType];
	}
	
	/**
	 * Answer an array of all rights strings
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getRights = function () {
		return [];
	}
	
	/**
	 * Answer an array of all sources
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getSources = function () {
		return [];
	}
	
	/**
	 * Answer an array of all relations
	 * 
	 * @return array
	 * @access public
	 * @since 4/30/07
	 */
	MiddMediaFile.prototype.getRelations = function () {
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
	MiddMediaFile.prototype.writeCitation = function (container) {
		return this.name;
	}

/*********************************************************
 * End - MediaFile public API
 *********************************************************/

