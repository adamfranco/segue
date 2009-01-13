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
			
		}
	}

	