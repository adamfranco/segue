/**
 * @since 7/12/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentPluginChooser.js,v 1.3 2008/04/11 20:07:41 adamfranco Exp $
 */

CommentPluginChooser.prototype = new PluginChooser();
CommentPluginChooser.prototype.constructor = CommentPluginChooser;
CommentPluginChooser.superclass = PluginChooser.prototype;

/**
 * The PluginChooser provides a pop-up panel with a list of availible plugins and
 * their descriptions from which the user can choose from when creating new plugins.
 * 
 * @since 7/12/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentPluginChooser.js,v 1.3 2008/04/11 20:07:41 adamfranco Exp $
 */
function CommentPluginChooser ( callingElement, destUrl, defaultTitle ) {
	if ( arguments.length > 0 ) {
		this.init( callingElement, destUrl, defaultTitle );
	}
}

	/**
	 * Initialise the object
	 * 
	 * @param string organizerId
	 * @param object DOM_Element	callingElement 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	CommentPluginChooser.prototype.init = function ( callingElement, destUrl, defaultTitle ) {		
		this.defaultTitle = unescape(defaultTitle);
		CommentPluginChooser.superclass.init.call(this, 
								callingElement,
								destUrl);
	}
	
	
	/**
	 * Answer the URL for accessing the plugin list
	 * 
	 * @return string
	 * @access public
	 * @since 4/11/08
	 */
	CommentPluginChooser.prototype.getPluginListUrl = function () {
		return Harmoni.quickUrl('plugin_manager', 'list_comment_plugins', 
// 						{'qualifier_id': this.qualifierId, 'function_id': 'edu.middlebury.authorization.view'}, 
						null,
						'plugin_manager');
	}

	/**
	 * Set some default values
	 * 
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	CommentPluginChooser.prototype.setDefaults = function () {
		this.preTitleText = 'Add a subject and choose a type of content to add.';
		this.titleLabel = 'Subject: ';
// 		this.defaultTitle = '';
		this.titleError = 'Please enter a subject.';
		this.namespace = 'comments';
	}
	
	/**
	 * Initialize and run the CommentPluginChooser
	 * 
	 * @param object DOM_Element	callingElement 
	 * @param string destUrl
	 * @return void
	 * @access public
	 * @since  7/12/07
	 */
	CommentPluginChooser.run = function ( callingElement, destUrl, defaultTitle ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new CommentPluginChooser( callingElement, destUrl, defaultTitle );
		}
	}
