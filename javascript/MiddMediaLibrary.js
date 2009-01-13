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
		
		this.directories = [];
		var dirs = xmldoc.getElementsByTagName('directory');
		for (var i = 0; i < dirs.length; i++) {
			this.directories.push(
				new MiddMediaDirectory(this.owner,
					dirs[i].getAttribute('name'),
					dirs[i].getAttribute('bytesUsed'),
					dirs[i].getAttribute('bytesAvailable')));
		}
		
		if (!this.directories.length) {
			this.container.innerHTML = "<p class='error'>Your account is not authorized to upload to the MiddMedia system.</p>";
			return;
		}
		
		// Make the directory-select menu.
		var form = this.container.appendChild(document.createElement('form'));
		form.action = '#';
		form.type = 'get';
		this.directorySelect = form.appendChild(document.createElement('select'));
		
		var option = this.directorySelect.appendChild(document.createElement('option'));
		option.value = null;
		option.innerHTML = "Select a directory...";
		
		for (var i = 0; i < this.directories.length; i++) {
			var option = this.directorySelect.appendChild(document.createElement('option'));
			option.value = this.directories[i].name;
			option.innerHTML = this.directories[i].name;
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
function MiddMediaDirectory ( owner, name, bytesUsed, bytesAvailable ) {
	if ( arguments.length > 0 ) {
		this.init( owner, name, bytesUsed, bytesAvailable );
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
	MiddMediaDirectory.prototype.init = function ( owner, name, bytesUsed, bytesAvailable ) {
		this.owner = owner;
		this.name = name;
		this.bytesUsed = bytesUsed;
		this.bytesAvailable = bytesAvailable;
	}
