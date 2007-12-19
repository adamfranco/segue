<?php
/**
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsTemplate.abstract.php,v 1.2 2007/12/19 21:55:26 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SeguePluginsDriver.abstract.php");

/**
 * This class includes all of the methods that SeguePlugins can
 * or must override. All SeguePlugins must extend this class, but they should directly
 * extend either SeguePlugin or SegueAjaxPlugin.
 *
 * For a list of SeguePlugins API methods that SeguePlugins are allowed to make
 * use of, please see the SeguePluginsAPI.abstract.php
 * 
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsTemplate.abstract.php,v 1.2 2007/12/19 21:55:26 adamfranco Exp $
 */
abstract class SeguePluginsTemplate
	extends SeguePluginsDriver
{

/*********************************************************
 * Object Variables - API
 *********************************************************/
	/**
	 * @var array $data; 4-dimensional array holding plugin data 
	 * @access protected
	 * @since 3/1/06
	 */
	protected $data;
	
/*********************************************************
 * Instance Methods - API - Override in Children
 *
 * Override these methods to implement the functionality of
 * a plugin.
 *********************************************************/
 	
 	/**
 	 * Answer a description of the the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	public static function getPluginDescription () {
 		return _("Override this method in your plugin.");
 	}
 	
 	/**
 	 * Answer a display name for the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginDisplayName () {
 		return _("Override this method in your plugin.");
 	}
 	
 	/**
 	 * Answer an array of the creators of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return array of strings
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginCreators () {
 		return array(_("Override this method in your plugin."));
 	}
 	
 	/**
 	 * Answer the version of the plugin.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersion () {
 		return '0.0';
 	}
 	
 	/**
 	 * Answer the latest version of the plugin available. Null if no version information
 	 * is available.
 	 * 
 	 * @return mixed a string or null
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersionAvailable () {
 		return null;
 	}
 	
 	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.  This is where you would make more complex data that your 
 	 * plugin needs.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function initialize () {
		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function update ( $request ) {
 		// Override as needed.
 	}
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function getMarkup () {
 		return _("<p>Override this method to display your plugin.</p>");
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedMarkup () {
 		return $this->getMarkup();
 	}
 	
 	/**
 	 * Answer the label to use when linking to the plugin's extented markup.
 	 * For a text-based plugin this may be the default, 'read more >>', for
 	 * an image plugin it might be something like "Large View", etc.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedLinkLabel () {
 		return _("read more &raquo;");
 	}
 	
 	/**
 	 * Generate a plain-text or HTML description string for the plugin instance.
 	 * This may simply be a stored 'raw description' string, it could be generated
 	 * from other content in the plugin instance, or some combination there-of.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/22/07
 	 */
 	public function generateDescription () {
 		return $this->getRawDescription();
 	}
 	
 	/**
 	 * Answer true if this instance of a plugin 'has content'. This method is called
 	 * to determine if the plugin instance is ready to be 'published' or is a newly-created
 	 * placeholder awaiting content addition. If the plugin has no appreciable 
 	 * difference between have content or not, this method should return true. For
 	 * example: an interactive calendar plugin should probably be 'published' 
 	 * whether or not events have been added to it.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 7/13/07
 	 */
 	public function hasContent () {
 		// Override as needed
 		return true;
 	}
 	
 	/*********************************************************
 	 * The following three methods allow plugins to work within
 	 * the "Segue Classic" user interface.
 	 *
 	 * If plugins do not support the wizard directly, then their
 	 * markup with 'show controls' enabled will be put directly 
 	 * in the wizard.
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin natively supports editing via wizard components.
 	 * Override to return true if you implement the getWizardComponent(), 
 	 * and updateFromWizard() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 5/9/07
 	 */
 	public function supportsWizard () {
 		return false;
 	}
 	/**
 	 * Return the a {@link WizardComponent} to allow editing of your
 	 * plugin in the Wizard.
 	 * 
 	 * @return object WizardComponent
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function getWizardComponent () {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
 	
 	/**
 	 * Update the component from an array of values
 	 * 
 	 * @param array $values
 	 * @return void
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function updateFromWizard ( $values ) {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
	
}

?>