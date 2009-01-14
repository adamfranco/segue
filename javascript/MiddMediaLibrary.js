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
		if (typeof container.appendChild != 'function')
			throw "container must support the appendChild() method";
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
	MiddMediaLibrary.prototype.fetchDirectories = function () {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('middmedia', 'getDirs');
		if (req) {
			// Define a variable to point at this MediaLibrary that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var library = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert(req.responseText);
						library.loadDirectories(req.responseXML);
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
		var types = xmldoc.getElementsByTagName('allowedFileTypes');
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
		
		if (this.dirContainer)
			this.dirContainer.innerHTML = '';
		else
			this.dirContainer = this.container.appendChild(document.createElement('div'));
		
		dir.createQuotaDisplay(this.dirContainer);
		
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
	
	// Take on the quota-display methods of the AssetLibrary
	MiddMediaDirectory.prototype.createQuotaDisplay = AssetLibrary.prototype.createQuotaDisplay;
	MiddMediaDirectory.prototype.writeQuota = AssetLibrary.prototype.writeQuota;
	MiddMediaDirectory.prototype.updateQuota = AssetLibrary.prototype.updateQuota;
	
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
		
// 		this.thumbnailUrl = xmlElement.getElementsByTagName('thumbnailUrl')[0].firstChild.data;
// 		this.thumbnailUrl = decodeURI(this.thumbnailUrl);
// 		this.thumbnailUrl = this.thumbnailUrl.urlDecodeAmpersands();
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
		imageLink.href = this.url;
		
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
		datum.innerHTML = this.date.toFormatedString('E NNN dd, yyyy h:mm a');;
		
		return row;
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
				return '{{video|service=middtube|user=' + this.directory.name + '|id=' + this.name.replace(/.flv$/, '') + '}}';
			case 'video/mp4':
				return '{{video|service=middtube|user=' + this.directory.name + '|id=mp4:' + this.name.replace(/.mp4$/, '') + '}}';
			case 'audio/mpeg':
				return '{{video|service=middtube|user=' + this.directory.name + '|id=mp3:' + this.name.replace(/.mp3$/, '') + '}}';
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

