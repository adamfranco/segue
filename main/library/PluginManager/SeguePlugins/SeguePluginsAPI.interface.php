<?php
/**
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsAPI.interface.php,v 1.1 2007/10/25 20:27:00 adamfranco Exp $
 */ 

/**
 * <##>
 * 
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsAPI.interface.php,v 1.1 2007/10/25 20:27:00 adamfranco Exp $
 */
interface SeguePluginsAPI {
		
/*********************************************************
 * Instance Methods/Variables - API
 *
 * These are the methods that plugins can and should use 
 * to interact with their environment. 
 * 		Valid additional APIs outside of the methods below:
 *			- OSID interfaces (accessed through Plugin->getManager($managerName))
 *
 * To preserve portability, plugins should not access 
 * other Harmoni APIs, constants, global variables, or
 * the super-globals $_GET, $_POST, $_REQUEST, $_COOKIE.
 *********************************************************/
/*********************************************************
 * Object Variables - API
 *********************************************************/
	/**
	 * @var array $data; 4-dimensional array holding plugin data 
	 * @access protected
	 * @since 3/1/06
	 */
//	protected $data;

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
 	public static function getPluginDescription ();
 	
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
 	public function initialize ();
 	
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
 	public function update ( $request );
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function getMarkup ();
 	
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
 	public function getExtendedMarkup ();
 	
 	/**
 	 * Answer the label to use when linking to the plugin's extented markup.
 	 * For a text-based plugin this may be the default, 'read more >>', for
 	 * an image plugin it might be something like "Large View", etc.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedLinkLabel ();
 	
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
 	public function generateDescription ();
 	
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
 	public function hasContent ();
 	
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
 	public function supportsWizard ();
 	
 	/**
 	 * Return the a {@link WizardComponent} to allow editing of your
 	 * plugin in the Wizard.
 	 * 
 	 * @return object WizardComponent
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function getWizardComponent ();
 	
 	/**
 	 * Update the component from an array of values
 	 * 
 	 * @param array $values
 	 * @return void
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function updateFromWizard ( $values );
 	
/*********************************************************
 * Instance Methods - API
 *
 * Use these methods in your plugin as needed, but do not 
 * override them.
 *********************************************************/
	
	/**
	 * Answer an href tag string with the array values added as parameters to 
	 * an internal url.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 4/30/07
	 */
	public function href ( $parameters = array() );
	
	/**
	 * Answer an internal Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function url ( $parameters = array() );
	
	/**
	 * Answer a Javascript command -- in quoted string form -- to send the window to 
	 * an internal url with the  parameters passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick=".$this->locationSend(array('item' => 123))
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	public function locationSendString ( $parameters = array() );
	
	/**
	 * Answer a Javascript command to send the window to an internal url with the 
	 * parameters passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick=".$this->locationSend(array('item' => 123))
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	public function locationSend ( $parameters = array() );
	
	/**
	 * Answer a url with the parameters passed, for a form. As well, specify
	 * an optional boolean second parameter, 'isMultipart' if this is a multipart
	 * form with file uploads.
	 *
	 * Use this method, e.g.:
	 *		$this->formTagWithAction(array('item' => 123), false);
	 * instead of the following:
	 * 		"<form action='".$this->url(array('item' => 123))."' method='post>";
	 *
	 * Usage of this method instead of manually writing the form start tag
	 * is optional, but will allow the plugin to more easily be ported to being
	 * an 'AjaxPlugin' later on as the AjaxPlugin redefines the behavior of
	 * this method.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @param string $method post OR get
	 * @param boolean $isMultipart
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	public function formStartTagWithAction ( $parameters = array(), $method = 'post', 
		$isMultipart = false );
	
	/**
	 * Answer the name-spaced field-name for a given name.
	 * This method MUST be used in form inputs for their values to be
	 * accessible to the plugin.
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function getFieldName ( $name );
	
	/**
	 * Answer the value of a submitted/requested field (i.e. GET, POST, REQUEST)
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function getFieldValue ( $name );
	
	/**
	 * Answer the persisted 'title' value of this plugin.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function getTitle ();
	
	/**
	 * Answer the persisted raw description value of this plugin. 
	 * It is up to the plugin writer what data to store in the raw description 
	 * field. The 'raw description' will only be used internally in this plugin.
	 * External access to the plugin's description will all go through 
	 * the getDescription() method, which may include additional operations.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function getRawDescription ();
	
	/**
	 * Set the persisted 'raw description' value of this plugin.
	 * It is up to the plugin writer what data to store in the raw description 
	 * field. The 'raw description' will only be used internally in this plugin.
	 * External access to the plugin's description will all go through 
	 * the getDescription() method, which may include additional operations.
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	public function setRawDescription ( $description );
	
	/**
	 * Answer the description markup for the plugin instance. This description
	 * will have been generated via the plugin's generateDescription() method.
	 * 
	 * This method MAY be overridden in your plugin implementation and 
	 * WILL BE used to display a description of the plugin instance in external 
	 * contexts (such as a site-map).
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	public function getDescription ();
	
	/**
	 * Answer the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function getContent ();
	
	/**
	 * Set the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @param string $content
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	public function setContent ( $content );
	
	/**
	 * Answer the persisted data of this plugin. Changes to this data can be
	 * persisted via updateDataRecords()
	 * 
	 * The data array returned from this function is a 4-dimensional array of 
	 * the following organization:
	 * level 1: associative array using the record name as the 'key' and an 
	 * array of instances of the record as the 'value'
	 * level 2: array of instances of the record, each index (0,1,...) maps to 
	 * an associative array of the fields in the record
	 * level 3: associative array using the field name as the 'key' and an array
	 * of instances of the fields as the 'value'
	 * level 4: array of instances of the field, each index (0,1,...) maps to
	 * the actual value for its instance of this field in this record
	 * Example: to get a value you will need to access the data array with 4
	 * indices; $myDataArray['recordName'][0]['fieldName'][3] would return the 
	 * fourth instance of 'fieldName' in the first instance of 'recordName'
	 * NOTE: Files are accessible through $myDataArray['FILE'][?] where the
	 * field names are: FILE_NAME DIMENSIONS FILE_SIZE MIME_TYPE 
	 * NOTE: you can also just access this data array through $this->data
	 *
	 * @return array this is the data array for your plugin
	 * @access public
	 * @since 1/13/06
	 */
	public function getDataRecords ();
	
	/**
	 * Automagically updates any changed data in the data array
	 *
	 * @return void
	 * @access public
	 * @since 1/18/06
	 */
	public function updateDataRecords ();
	
	/**
	 * Answer a valid XHTML with any tag or special-character errors fixed.
	 * 
	 * @param string $htmlString
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	public function cleanHTML ($htmlString);
	
	/**
	 * Answer a valid XHTML string trimmed to the specified word length. Cleans
	 * the syntax as well.
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	public function trimHTML ($htmlString, $maxWords, $addElipses = true);
	
	/**
	 * Answer a string trimmed to the specified word length. Strips html tags
	 * as well
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	public function stripTagsAndTrim ($htmlString, $maxWords, $addElipses = true);

	/**
	 * Answer TRUE if the current user is authorized to modify this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	public function canModify ();
	
	/**
	 * Answer TRUE if the current user is authorized to view this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	public function canView ();
	
	/**
	 * Answer TRUE if modification controls should be displayed, assuming that
	 * authorization is had as well. This method allows the plugin to operate
	 * in two modes, hiding editing controls when they are not needed.
	 *  
	 * @return boolean
	 * @access public
	 * @since 2/22/06
	 */
	public function shouldShowControls ();
	
	/**
	 * Answer the string Id of this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	public function getId ();

	/**
	 * Answer the filesystem filepath for the plugin
	 * 
	 * @return string the filesystem path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	public function getPluginDir ();

	/**
	 * Answer the url filepath for the plugin?
	 * 
	 * @return string the url path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	public function getPluginPath ();

/*********************************************************
 * Files
 *
 * There are two ways that files can be stored and accessed
 * by plugins. 
 * 		1. 	Reference files from the media library
 *		2. 	Store and access file records in the plugin's 
 *			data array.
 *
 * 
 *********************************************************/
 
 /*********************************************************
  * Files referenced from the media library.
  * -----------------------------------------
  *
  * Segue has a built-in "media library" that allows users
  * to upload and manage files across their site and from
  * remote sources.
  *
  * MediaFiles are identified by a string id. To access a MediaFile
  * object use the MediaFile::withIdString() static accessor method:
  *		$mediaFile = MediaFile::withIdString('repositoryId=123&assetId=456&recordId=789');
  *
  * MediaFiles provide access to Dublin Core metadata as as urls and file 
  * properties.
  *
  * To load the media library create a button or link that
  * calls the media library's static initializer method, run(),
  * and pass it the plugin's id and a DOM Element for reference.
  * To the referenced DOM element you must attach an "onUse" method
  * which will be given a MediaFile javascript object when the
  * user chooses a file in the media library.
  *
  * Example (Note that the line-returns in the Javascript must be removed for actual usage):
  *		<input type="button" 
  *			onclick="this.onUse = function (mediaFile) {
  *
  *						alert(mediaFile.getTitles()[0]
  *							+ '\n' + mediaFile.getUrl()
  *							+ '\n' + mediaFile.getThumbnailUrl());
  *
  *					 }
  *
  *					 MediaLibrary.run('12345', this);"
  *			value="Select File"/>
  *
  * 
  * This is the preferred method for handling files.
  *
  * The methods availible in the MediaFile class are as follows:
  *
  * 	Instance Creation (static):
  *			withIdString($idString)
  *
  *		Access:
  *			getIdString()
  *			getSize()
  *			getFilename()
  *			getMimeType()
  *			getFileContents()
  *			getUrl()
  *			getThumbnailUrl()
  *			getModificationDate()
  *			
  *		Access - Dublin Core:
  *			getTitle()
  *			getTitles()
  *			getCreator()
  *			getCreators()
  *			getDescription()
  *			getDescriptions()
  *			getSource()
  *			getSources()
  *			getPublisher()
  *			getPublishers()
  *			getDate()
  *			getDates()
  *			getContributor()
  *			getContributors()
  *			getRight()
  *			getRights()
  *			getRelation()
  *			getRelations()
  *			getLanguage()
  *			getLanguages()
  *			
  *********************************************************/
 
 
 /*********************************************************
  * Files in the Plugin's data array.
  * -----------------------------------------
  *
  * Your plugin can store files in its data array if needed.
  * This may be useful if the plugin needs to handle file
  * uploads that should not be directly accessible.
  *********************************************************/
 
	/**
	 * Answer the URL for the file 
	 * 
	 * @param string $idString of the 'associated file'
	 * @param string $fname the 'FILE_NAME'
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	public function getThumbnailURL ($idString, $fname);

	/**
	 * Returns the HTML string for viewing files associated with your plugin
	 *
	 * Choose which information to print by passing two arrays, one that is the
	 * file record array from your plugin data, and the other that is an array
	 * of the data part keys (chosen from {"FILE_NAME", "FILE_SIZE",
	 * "DIMENSIONS", "MIME_TYPE", "FILE_DATA"} having "FILE_DATA" in the array 
	 * will print the thumbnail for the file record) that you want printed.
	 *
	 * @param array $fileData array referencing the file record
	 * @param array $parts array listing parts to print 
	 * @return string 
	 * @access public
	 * @since 1/31/06
	 */
	public function printFileRecord($fileData, $parts);

	/**
	 * Answer the URL for the file 
	 * 
	 * @param string $idString of the 'associated file'
	 * @param string $fname the 'FILE_NAME'
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	public function getFileURL ($idString, $fname);

	/**
	 * Answer the file data for the file
	 * 
	 * @param string $idString of the 'associated file'
	 * @return blob
	 * @access public
	 * @since 1/26/06
	 */
	public function getFileData ($idString);
	
/*********************************************************
 * Logging
 *********************************************************/
	
	/**
	 * Log an event. Plugins should log events that involve data modification
	 * with type 'Event_Notice' and events that involve errors with type 'Error'
	 * 
	 * @param string $category
	 * @param string $description
	 * @param optional string $type
	 * @return void
	 * @access public
	 * @since 3/6/06
	 */
	public function logEvent ($category, $description, $type = 'Event_Notice');
}

?>