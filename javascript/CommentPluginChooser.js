/**
 * @since 7/12/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentPluginChooser.js,v 1.1 2007/07/13 15:31:25 adamfranco Exp $
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
 * @version $Id: CommentPluginChooser.js,v 1.1 2007/07/13 15:31:25 adamfranco Exp $
 */
function CommentPluginChooser ( callingElement, destUrl ) {
	if ( arguments.length > 0 ) {
		this.init( callingElement, destUrl );
	}
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
		this.defaultTitle = '';
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
	CommentPluginChooser.run = function ( callingElement, destUrl ) {
		if (callingElement.panel) {
			callingElement.panel.open();
		} else {
			var tmp = new CommentPluginChooser( callingElement, destUrl );
		}
	}
