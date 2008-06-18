<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriver.abstract.php,v 1.17 2008/04/11 20:40:34 adamfranco Exp $
 */ 

require_once (HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(MYDIR."/main/modules/media/MediaAsset.class.php");
require_once(MYDIR."/main/library/Wiki/WikiResolver.class.php");
require_once(MYDIR."/main/library/DiffEngine.php");

require_once(dirname(__FILE__)."/SeguePluginsDriverAPI.interface.php");
require_once(dirname(__FILE__)."/SeguePluginsAPI.interface.php");
require_once(dirname(__FILE__)."/SeguePluginVersion.class.php");

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * Abstract class that all Plugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriver.abstract.php,v 1.17 2008/04/11 20:40:34 adamfranco Exp $
 */
abstract class SeguePluginsDriver 
	implements SeguePluginsDriverAPI, SeguePluginsAPI
{
 	

 	
/*********************************************************
 * Instance Methods - API
 *
 * Use these methods in your plugin as needed, but do not 
 * override them.
 *********************************************************/
	
	/**
	 * Answer an internal Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	final public function url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
		$url = $this->_baseUrl->deepCopy();
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		return $url->write();
	}
	
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
	final public function locationSendString ( $parameters = array() ) {		
		return "'".$this->locationSend($parameters)."'";
	}
	
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
	final public function getFieldName ( $name ) {
		return RequestContext::name($name);
	}
	
	/**
	 * Answer the value of a submitted/requested field (i.e. GET, POST, REQUEST)
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	final public function getFieldValue ( $name ) {
		return RequestContext::value($name);
	}
	
	/**
	 * Answer the persisted 'title' value of this plugin.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	final public function getTitle () {
		return HtmlString::getSafeHtml($this->_asset->getDisplayName());
	}
	
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
	final public function getRawDescription () {
		$idManager = Services::getService("Id");
		$parts = $this->_asset->getPartsByPartStructure(
			$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"));
		
		if ($parts->hasNext()) {
			$part = $parts->next();
			$value = $part->getValue();
			return $value->asString();
		} else {
			return "";
		}
	}
	
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
	final public function setRawDescription ( $description ) {
		$idManager = Services::getService("Id");
		$parts = $this->_asset->getPartsByPartStructure(
			$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"));
		if ($parts->hasNext()) {
			$part = $parts->next();
			$part->updateValue(String::fromString($description));
		} else {
			$records = $this->_asset->getRecordsByRecordStructure(
				$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs"));
			if ($records->hasNext()) {
				$record = $records->next();
			} else {
				$record = $this->_asset->createRecord($idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs"));
			}
			
			$part = $record->createPart($idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.segue_plungin_rs.raw_description"), String::fromString($description));
		}
	}
	
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
	final public function getDescription () {
		return $this->_asset->getDescription();
	}
	
	/**
	 * Answer the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	final public function getContent () {
		$content = $this->_asset->getContent();
		return $content->asString();
	}
	
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
	final public function setContent ( $content ) {
		$string = Blob::withValue($content);
		$this->_asset->updateContent($string);
	}
	
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
	final public function getDataRecords () {
		return $this->data;
	}

	/**
	 * Automagically updates any changed data in the data array
	 *
	 * @return void
	 * @access public
	 * @since 1/18/06
	 */
	final public function updateDataRecords () {
		$this->_storeData();
	}
	
	/**
	 * Answer a valid XHTML with any tag or special-character errors fixed.
	 * 
	 * @param string $htmlString
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	final public function cleanHTML ($htmlString) {
		return HtmlString::getSafeHtml($htmlString);
	}
	
	/**
	 * Answer a valid XHTML string trimmed to the specified word length. Cleans
	 * the syntax and removes XSS markup as well.
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	final public function trimHTML ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj = HtmlString::withValue($htmlString);
		$htmlStringObj->cleanXSS();
 		$htmlStringObj->trim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}
	
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
	final public function stripTagsAndTrim ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj = HtmlString::withValue($htmlString);
 		$htmlStringObj->stripTagsAndTrim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}
	
	/**
	 * Parse and replace any wiki-text with HTML markup. This method will also
	 * untokenize an local-url tokens.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 12/3/07
	 */
	final public function parseWikiText ($text) {
		$wikiResolver = WikiResolver::instance();
		
		$wikiResolver->setViewAction($this->_baseUrl->getModule(), $this->_baseUrl->getAction());
		
		try {
			$siteComponent = $this->getRelatedSiteComponent();
		
			$text = $wikiResolver->parseText($text, $siteComponent);
		} catch (OperationFailedException $e) {
		}
		
		return $text;
	}
	
	/**
	 * Given a block of HTML text, replace any local-system urls with tokenized
	 * placeholders. These placeholders can the be translated back at display time
	 * in order to match the current system base-url 
	 * 
	 * @param string $htmlString
	 * @return string The HTML text with URLs translated into tokens.
	 * @access public
	 * @since 1/24/08
	 */
	public function tokenizeLocalUrls ($htmlString) {
		$patterns = array();
		$harmoni = Harmoni::instance();
		$pattern = '/'.str_replace('/', '\/', MYURL).'[^\'"\s\]]*/i';
		$urls = preg_match_all($pattern, $htmlString, $matches);
		foreach ($matches[0] as $url) {
			$paramString = $harmoni->request->getParameterListFromUrl($url);
			if ($paramString !== false) {
				// File urls
				if ($harmoni->request->getModuleFromUrl($url) == 'repository'
					&& $harmoni->request->getActionFromUrl($url) == 'viewfile')
				{
					$htmlString = $this->str_replace_once($url, '[[fileurl:'.MediaFile::getIdStringFromUrl($url).']]', $htmlString);
				}
				
				// other local urls
				else {
					$htmlString = $this->str_replace_once($url, '[[localurl:'.$paramString.']]', $htmlString);
				}
			}
		}
		
		return $htmlString;
	}
	
	/**
	 * Translate any local-system url-tokens back into valid URLs.
	 * 
	 * @param string $htmlString
	 * @return string The HTML text with tokens translated into valid URLs.
	 * @access public
	 * @since 1/24/08
	 */
	public function untokenizeLocalUrls ($htmlString) {
		$harmoni = Harmoni::instance();
		
		// File URLs
		preg_match_all('/\[\[fileurl:([^\]]*)\]\]/', $htmlString, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			try {
				$mediaFile = MediaFile::withIdString($matches[1][$i]);
				$htmlString = $this->str_replace_once($matches[0][$i], $mediaFile->getUrl(), $htmlString);
			} catch (InvalidArgumentException $e) {
			} catch (UnknownIdException $e) {
			}
		}
		
		// other local urls
		$harmoni->request->startNamespace(null);
		while (preg_match('/\[\[localurl:([^\]]*)\]\]/', $htmlString, $matches)) {
			preg_match_all('/(&(amp;)?)?([^&=]+)=([^&=]+)/', $matches[1], $paramMatches);
			$args = array();
			for ($i = 0; $i < count($paramMatches[1]); $i++) {
				$key = $paramMatches[3][$i];
				$value = $paramMatches[4][$i];
				
				if ($key == 'module')
					$module = $value;
				else if ($key == 'action')
					$action = $value;
				else
					$args[$key] = $value;
			}
			
			if (!isset($module))
				$module = 'ui1';
			if (!isset($action))
				$action = 'view';
			
			$newUrl = $harmoni->request->mkURLWithoutContext($module, $action, $args);
			$htmlString = $this->str_replace_once($matches[0], $newUrl->write(), $htmlString);
		}
		$harmoni->request->endNamespace();
		return $htmlString;
	}
	
	/**
	 * Utility method to do a single string replacement.
	 * 
	 * @param string $search
	 * @param string $replacement
	 * @param string $subject
	 * @return string
	 * @access private
	 * @since 1/24/08
	 */
	private function str_replace_once ($search, $replacement, $subject) {
		$position = strpos($subject, $search);
		return substr_replace($subject, $replacement, $position, strlen($search));
	}
	
	/**
	 * Given an associative array of old Id strings and new Id strings,
 	 * Update any of the old Ids in an HTML string to their new value.
 	 * This method will replace Ids in tokenized URLs and wiki-text.
 	 *
	 * @param array $idMap An associative array of old id-strings to new id-strings.
	 * @param string $htmlString
	 * @return string The updated HTML string
	 * @access public
	 * @since 1/24/08
	 */
	final public function replaceIdsInHtml (array $idMap, $htmlString) {
		$orig = $htmlString;
		// non-wiki urls
		$tokenizedHtml = $this->tokenizeLocalUrls($htmlString);
		preg_match_all('/\[\[localurl:([^\]]*)\]\]/', $htmlString, $matches);
		for ($j = 0; $j < count($matches[1]); $j++) {
			preg_match_all('/(&(amp;)?)?([^&=]+)=([^&=]+)/', $matches[1][$j], $paramMatches);
			$args = array();
			for ($i = 0; $i < count($paramMatches[1]); $i++) {
				$key = $paramMatches[3][$i];
				$value = urldecode($paramMatches[4][$i]);
				
				if ($key != 'module' && $key != 'action' && isset($idMap[$value]))
					$args[] = $key."=".urlencode($idMap[$value]);
				else
					$args[] = $key."=".urlencode($value);
			}
			
			$htmlString = $this->str_replace_once($matches[0][$j], 
				'[[localurl:'.implode('&amp;', $args).']]', $htmlString);
		}
		
		// File URLs
		preg_match_all('/\[\[fileurl:([^\]]*)\]\]/', $htmlString, $matches);
		for ($j = 0; $j < count($matches[1]); $j++) {
			preg_match_all('/(&(amp;)?)?([^&=]+)=([^&=]+)/', $matches[1][$j], $paramMatches);
			$args = array();
			for ($i = 0; $i < count($paramMatches[1]); $i++) {
				$key = $paramMatches[3][$i];
				$value = urldecode($paramMatches[4][$i]);
				
				if ($key != 'module' && $key != 'action' && isset($idMap[$value]))
					$args[] = $key."=".urlencode($idMap[$value]);
				else
					$args[] = $key."=".urlencode($value);
			}
			
			$htmlString = $this->str_replace_once($matches[0][$j], 
				'[[fileurl:'.implode('&amp;', $args).']]', $htmlString);
		}
		
		// Wiki-links
		preg_match_all('/\[\[node:([^\]]*)\]\]/', $htmlString, $matches);
		for ($j = 0; $j < count($matches[1]); $j++) {
			$nodeId = $matches[1][$j];
			
			if (isset($idMap[$nodeId]))			
				$htmlString = $this->str_replace_once($matches[0][$j], 
					'[[node:'.$idMap[$nodeId].']]', $htmlString);
		}
		
		
		return $htmlString;
	}

	/**
	 * Answer TRUE if the current user is authorized to modify this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	final public function canModify () {
		if (isset($this->_canModifyFunction)) {
			$function = $this->_canModifyFunction;
			return $function($this);
		} else {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			return $azManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.modify"),
					$this->_asset->getId());
		}
	}
	
	/**
	 * Answer TRUE if the current user is authorized to view this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	final public function canView () {
		if (isset($this->_canViewFunction)) {
			$function = $this->_canViewFunction;
			return $function($this);
		} else {
			$azManager = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			return $azManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"),
					$this->_asset->getId());
		}
	}
	
	/**
	 * Answer TRUE if modification controls should be displayed, assuming that
	 * authorization is had as well. This method allows the plugin to operate
	 * in two modes, hiding editing controls when they are not needed.
	 *  
	 * @return boolean
	 * @access public
	 * @since 2/22/06
	 */
	final public function shouldShowControls () {
		if ($this->_showControls && $this->canModify())
			return true;
		else
			return false;
	}
	
	/**
	 * Answer the string Id of this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	final public function getId () {
		if (!isset($this->_id)) {
			$id = $this->_asset->getId();
			$this->_id = $id->getIdString();
		}
		return $this->_id;			
	}

	/**
	 * Answer the filesystem filepath for the plugin
	 * 
	 * @return string the filesystem path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	final public function getPluginDir () {
		$dir = $this->_configuration->getProperty('plugin_dir')."/";
		$type = $this->_asset->getAssetType();
		$dir .= $type->getDomain()."/";
		$dir .= $type->getAuthority()."/";
		$dir .= $type->getKeyword()."/";

		return $dir;
	}
	
	/**
	 * This method will give you a url to access files in a 'public'
	 * subdirectory of your plugin. 
	 *
	 * Example, status_image.gif in an 'Assignment' plugin by Example University:
	 *
	 * File Structure
	 *		Assignment/
	 *			EduExampleAssignmentPlugin.class.php
	 *			icon.png
	 *			public/
	 *				status_image.gif
	 *	
	 * Usage: print $this->getPublicFileUrl('status_image.gif');
	 * 
	 * @param string $filename.
	 * @return string
	 * @access public
	 * @since 6/18/08
	 */
	final public function getPublicFileUrl ($filename) {
		$harmoni = Harmoni::instance();
		return $harmoni->request->quickURL('plugin_manager', 'public_file', 
			array('plugin' => HarmoniType::toString($asset->getAssetType()),'file' => $filename));
	}

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
	final public function getThumbnailURL ($idString, $fname) {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();

		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewthumbnail", 
			array(
			"repository_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $idString,
			"thumbnail_name" => $fname));
		$harmoni->request->endNamespace();
		
		return $url;
	}

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
	final public function printFileRecord($fileData, $parts) {
		$idManager = Services::getService("Id");
		$moduleManager = Services::getService("InOutModules");
		
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();
		$rid = $idManager->getId($fileData['assoc_file_id'][0]);
		$record = $this->_asset->getRecord($rid);
		$rs = $record->getRecordStructure();
		
		$setArray = array("FILE_NAME", "FILE_SIZE", "DIMENSIONS", 
			"MIME_TYPE", "FILE_DATA");
		$newArray = array_intersect($setArray, $parts);
		$partStructureArray = array();
		
		foreach ($newArray as $part) {
				$partStructureArray[] = $rs->getPartStructure(
					$idManager->getId($part));
		}

		return $moduleManager->generateDisplayForPartStructures(
			$repositoryId, $assetId, $record, $partStructureArray);
	}

	/**
	 * Answer the URL for the file 
	 * 
	 * @param string $idString of the 'associated file'
	 * @param string $fname the 'FILE_NAME'
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	final public function getFileURL ($idString, $fname) {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");
		$repositoryId = $idManager->getId(
			"edu.middlebury.segue.sites_repository");
		$assetId = $this->_asset->getId();

		$harmoni->request->StartNamespace('polyphony-repository');
		$url = $harmoni->request->quickURL("repository", "viewfile", 
			array(
			"repository_id" => $repositoryId->getIdString(),
			"asset_id" => $assetId->getIdString(),
			"record_id" => $idString,
			"file_name" => $fname));
		$harmoni->request->endNamespace();
		
		return $url;		
	}

	/**
	 * Answer the file data for the file
	 * 
	 * @param string $idString of the 'associated file'
	 * @return blob
	 * @access public
	 * @since 1/26/06
	 */
	final public function getFileData ($idString) {
		$idManager = Services::getService("Id");
		$id = $idManager->getId($idString);
		$fileRS = $this->_asset->getRecord($id);
		$data_id = $idManager->getId("FILE_DATA");
		$data = $fileRS->getPartsByPartStructure($data_id);
		if ($data->hasNext())
			$datum = $data->next();
			
		return $datum;
	}
	
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
	final public function logEvent ($category, $description, $type = 'Event_Notice') {
		ArgumentValidator::validate($category, StringValidatorRule::getRule());
		ArgumentValidator::validate($description, StringValidatorRule::getRule());
		ArgumentValidator::validate($type, ChoiceValidatorRule::getRule('Event_Notice', 'Error'));
		
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", $type,
							"Normal events.");
			
			$item = new AgentNodeEntryItem($category, $description);
			$item->addNodeId($this->_asset->getId());
			
			// Get the site Id (Note: this creates a circular dependancy between
			// the plugins package and the SiteDisplay Package.
			try {
				$idManager = Services::getService("Id");
				$director = SiteDispatcher::getSiteDirector();
				$relatedComponent = $this->getRelatedSiteComponent();
				$rootSiteComponent = $director->getRootSiteComponent($relatedComponent->getId());
				
				$item->addNodeId($idManager->getId($rootSiteComponent->getId()));
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			} catch (OperationFailedException $e) {
			}
		}
	}
	
/*********************************************************
 * Versioning
 *********************************************************/
 
 	/**
 	 * Trigger the storage of a new version of the plugin instance. An optional
 	 * comment can be passed.
 	 * 
 	 * @param string $comment
 	 * @return void
 	 * @access public
 	 * @since 1/4/08
 	 */
 	final public function markVersion ($comment = '') {
 		$query = new InsertQuery;
 		$query->setTable('segue_plugin_version');
 		$query->addValue('node_id', $this->getId());
 		$query->addValue('comment', strval($comment));
 		
 		$authN = Services::getService("AuthN");
		$userId = $authN->getFirstUserId();
 		$query->addValue('agent_id', $userId->getIdString());
 		
 		$query->addValue('version_xml', $this->exportVersion()->saveXML());
 		
 		$dbc = Services::getService('DBHandler');
 		$dbc->query($query, IMPORTER_CONNECTION);
 		
 	}
 	
 	/**
 	 * Answer an XHTML 'diff' that compares two arrays of strings. 
 	 * Normal usage would be to explode blocks of text on "\n" to allow a line-by-line
 	 * comparison.
 	 * 
 	 * @param array $oldStrings
 	 * @param array $newStrings
 	 * @return string
 	 * @access public
 	 * @since 1/7/08
 	 */
 	public function getDiff ($oldStrings, $newStrings) {
 		$rule = ArrayValidatorRuleWithRule::getRule(StringValidatorRule::getRule());
 		ArgumentValidator::validate($oldStrings, $rule);
 		ArgumentValidator::validate($newStrings, $rule);
	 		
 		$formatter = new SegueTableDiffFormatter;
 		$diff = new Diff ($oldStrings, $newStrings);
 		return $formatter->format($diff);
 	}

/*********************************************************
 *********************************************************
 *********************************************************
 *********************************************************
 * Non-API vars/methods
 * 
 * The variables and methods listed below are not part of the
 * plugin API and should never be called by plugins. Their
 * functionality is used by the plugin system internally
 *
 *********************************************************/

/*********************************************************
 * Class Methods - Instance Creation
 *********************************************************/
	/**
	 * Instantiate a new plugin for an Asset
	 * 
	 * @param object Asset $asset
	 * @param object ConfigurationProperties $configuration
	 * @return object Plugin OR string (error string) on error.
	 * @access public
	 * @since 1/12/06
	 * @static
	 */
	public static function newInstance ( Asset $asset, Properties $configuration ) {
		$type = $asset->getAssetType();
		$pluginManager = Services::getService("PluginManager");
		$pluginDir = $pluginManager->getPluginDir($type);
		$pluginClass = $pluginManager->getPluginClass($type);
		
		if (preg_match('/[^a-z0-9_]/i', $pluginClass))
			throw new Exception("Invalid plugin class, '".$pluginClass."'.");
		
		$pluginFile = $pluginDir.$pluginClass.".class.php";
		
		
		// Check for the file
		if (!file_exists($pluginFile))	
			return _("Error: Plugin not found at '$pluginFile'.");
		
		require_once($pluginFile);
		
		// Check for the class
		if (!class_exists($pluginClass)) 
			return _("Error: Plugin class, '$pluginClass', not found.");
		
		// Ensure that the plugin writer didn't override the constructor
		if (in_array($pluginClass, get_class_methods($pluginClass)))
			return _("Error: Plugin class should not have a constructor method, '$pluginClass'.");
		
		// Instantiate the plugin
		$plugin = new $pluginClass;
		
		$plugin->setConfiguration($configuration);
		$plugin->setAsset($asset);
		
		// Execute the decendent's initialization
		$plugin->initialize();
		
		return $plugin;
	}
	
/*********************************************************
 * Object Properties - Non-API
 *********************************************************/
	/**
	 * @var string $_id; The string Id of the Plugin/Asset 
	 * @access private
	 * @since 1/17/06
	 */
	private $_id;
	
	/**
	 * If true, editing controls will be displayed (assuming authorization)
	 * @var boolean $_showControls;  
	 * @access private
	 * @since 2/22/06
	 */
	private $_showControls = false;
	
	/**
	 * @var object URLWriter $_baseURL; URL for the plugin w/o mods
	 * @access private
	 * @since 3/1/06
	 */
	private $_baseURL;
	
	/**
	 * @var object HarmoniAsset $_asset; the asset that contains the plugin data 
	 * @access private
	 * @since 3/1/06
	 */
	private $_asset;
	
	/**
	 * @var object HarmoniConfiguration $_configuration; holds config data 
	 * @access private
	 * @since 3/1/06
	 */
	private $_configuration;
	
	/**
	 * @var string $_pluginDir; filesystem path to appropriate plugin class 
	 * @access private
	 * @since 3/1/06
	 */
	private $_pluginDir;
	
	/**
	 * @var aray $_data_ids; parallel structure to 'data' w/ part ids
	 * @access private
	 * @since 3/1/06
	 */
	private $_data_ids;
	
	/**
	 * @var array $_loadedData; the data that persisted since the last change
	 * @access private
	 * @since 3/1/06
	 */
	private $_loadedData;
	
	/**
	 * @var array $_structures; array mapping 'data' indices to structure ids 
	 * @access private
	 * @since 3/1/06
	 */
	private $_structures;
	
/*********************************************************
 * Instance Methods - Non-API
 *********************************************************/
	
	/**
	 * Set the plugin's environmental configuration
	 * 
	 * @param object ConfigurationProperties $configuration
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	final private function setConfiguration ( $configuration ) {
		if (isset($this->_configuration))
			throwError(new Error("Configuration already set.", "Plugin.abstract", true));
			
		$this->_configuration = $configuration;
	}
	
	/**
	 * Inialize ourselves with our data-source asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	final private function setAsset ( $asset ) {
		if (isset($this->_asset))
			throwError(new Error("Asset already set.", "Plugin.abstract", true));
		
		$this->_asset = $asset;

		$type = $this->_asset->getAssetType();

		$this->_pluginDir = $this->_configuration->getProperty("plugin_dir")."/".$type->getDomain()."/".
						$type->getAuthority()."/".$type->getKeyword()."/";
		
		$this->_loadData();
	}
	
	/**
	 * Answer a site component that relates to this plugin instance. This site component
	 * may be for this plugin instance or not. This method will throw an OperationFailedException
	 * if no corresponding site component exists or is set.
	 *
	 * @return object SiteComponent
	 * @access public
	 * @since 3/31/08
	 */
	final public function getRelatedSiteComponent () {
		if (isset($this->relatedSiteComponent))
			return $this->relatedSiteComponent;
		
		// If we don't have a related site component, assume that this plugin equates
		// to a site component and try to return a matching one.
		// @todo This part should probably be removed and the code that instantiates the plugin
		// should be forced to call the setRelatedSiteComponent() method.
		
		// Get the site Id (Note: this creates a circular dependancy between
		// the plugins package and the SiteDisplay Package.
		$idManager = Services::getService("Id");
		$nodeId = $this->_asset->getId();
		$director = SiteDispatcher::getSiteDirector();
		$siteComponent = $director->getSiteComponentById($nodeId->getIdString());
		
		// Test if getting the parent component works.
		try {
			$siteComponent->getParentComponent();
		} catch (NonNavException $e) {
			throw new OperationFailedException("No SiteComponent available for ".get_class($this).", '".$this->_asset->getDisplayName()."'.");
		}
		
		return $siteComponent;
	}
	
	/**
	 * @var object SiteComponent $relatedSiteComponent;  
	 * @access private
	 * @since 3/31/08
	 */
	private $relatedSiteComponent;
	
	/**
	 * Set a SiteComponent that is related to this plugin. It may be another representation
	 * of the same data in the system or an entirely different object. This SiteComponent
	 * will be used for doing WikiResolving and Logging.
	 * 
	 * @param object SiteComponent $relatedSiteComponent
	 * @return void
	 * @access public
	 * @since 3/31/08
	 */
	final public function setRelatedSiteComponent (SiteComponent $relatedSiteComponent) {
		$this->relatedSiteComponent = $relatedSiteComponent;
	}
	
	/**
	 * Set the status of showControls.
	 * 
	 * @param boolean $showControls
	 * @return void
	 * @access private
	 * @since 2/22/06
	 */
	final private function setShowControls ($showControls) {
		ArgumentValidator::validate($showControls, BooleanValidatorRule::getRule());
		$this->_showControls = $showControls;
	}
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended	If true, return the extended version. Default: false.
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function executeAndGetMarkup ( $showControls = false, $extended = false ) {
		$obLevel = ob_get_level();
		try {
			
			$this->setShowControls($showControls);
			
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace(
				get_class($this).':'.$this->getId());
			
			if (isset($this->localModule) && $this->localModule && isset($this->localAction) && $this->localAction) 
			{
				$this->_baseUrl = $harmoni->request->mkURL($this->localModule, $this->localAction);
			} else {
				$this->_baseUrl = $harmoni->request->mkURL();
			}
			
			$this->update($this->_getRequestData());
			
			if ($extended)
				$markup = $this->getExtendedMarkup();
			else
				$markup = $this->getMarkup();
			
			// update the description if needed
			$this->setShowControls(false);
			$desc = $this->generateDescription();
			$this->setShowControls($showControls);
			if ($desc != $this->_asset->getDescription()) {
				$this->_asset->updateDescription($desc);
			}
			
			$this->_storeData();
			
			
			$harmoni->request->endNamespace();
		} catch (Exception $e) {
			while (ob_get_level() > $obLevel)
				ob_end_clean();
			
			HarmoniErrorHandler::logException($e);
			$markup = _("An Error has occured in the plugin with the following message: ");
			$markup .= $e->getMessage();
		}
		return $markup;
	}
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	final public function executeAndGetExtendedMarkup ( $showControls = false) {
		return $this->executeAndGetMarkup($showControls, true);
	}
	
	/**
	 * Answer true if this plugin instance has extended content that should
	 * be linked to.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/23/07
	 */
	final public function hasExtendedMarkup () {
		$showControls = $this->_showControls;
		$this->_showControls = false;
		
		if ($this->getMarkup() == $this->getExtendedMarkup())
			$hasExtended = false;
		else
			$hasExtended = true;
		
		$this->_showControls = $showControls;
		return $hasExtended;
	}
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	final public function setCanModifyFunction ($function) {
		$this->_canModifyFunction = $function;
	}
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	final public function setCanViewFunction ($function) {
		$this->_canViewFunction = $function;
	}
	
	/**
	 * Set what module and action the plugin urls should use. This is needed when
	 * generating markup to be used in another context.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 2/18/08
	 */
	final public function setLocalModuleAndAction ($module, $action) {
		ArgumentValidator::validate($module, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($action, NonzeroLengthStringValidatorRule::getRule());
		
		$this->localModule = $module;
		$this->localAction = $action;
	}
	
	/**
	 * Answer the REQUEST data for this plugin instance.
	 * 
	 * @return array
	 * @access private
	 * @since 1/13/06
	 */
	final private function _getRequestData () {
		return array();
	}
	
	/**
	 * Load our data from our Asset
	 * 
	 * NOTE: Part Id's are used to maintain order in the part array
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	final private function _loadData () {
		// one array for the data, a second for the persistence of ids
		if (isset($this->data))
			unset($this->data, $this->_data_ids);
		$this->data = array();
		$this->_data_ids = array();

		foreach (array_keys($this->getStructuresForPlugin()) as $struct) {
			$this->data[$struct] = array();
			$this->_data_ids[$struct] = array();
		}
// @todo rework using getStructures...
// initialize the data array with the structures from getstructures, but do not
// give any instances to the structures.
// get the records from the asset.  load 'em up

		// get all the records for this asset
		$records = $this->_asset->getRecords();
		
		// maintain record order
		$sets = Services::getService("Sets");
		$recordOrder = $sets->getPersistentSet($this->_asset->getId());
		$ordered = array();
		
		while ($records->hasNext()) {
			$record = $records->next();
			$rid = $record->getId();
			if (!$recordOrder->isInSet($rid))
				$recordOrder->addItem($rid);
			$ordered[$recordOrder->getPosition($rid)] = $rid;
		}

// @todo make sure the array exists for each structure, but not an instance yet

		foreach ($ordered as $recid) {
			$record = $this->_asset->getRecord($recid);
			
			// for each new recordstructure add an array for holding instances
			$recordStructure = $record->getRecordStructure();
			$rsId = $recordStructure->getId();
			$rsIdString = $rsId->getIdString();
			if ($rsIdString != "FILE") {
				$rsName = $recordStructure->getDisplayName();
				if (!in_array($rsName, array_keys($this->data))) {
					$this->data[$rsName] = array();
					$this->_data_ids[$rsName] = array();
				}
				
				// each instance itself should be acessible via index (1,2,3...)
				$this->data[$rsName][] = array();
				$this->_data_ids[$rsName][] = array();
				$instance = count($this->data[$rsName]) - 1; // current instance
	
				// each instance populates its parts like the records
				$parts = $record->getParts();
				while ($parts->hasNext()) {
					$part = $parts->next();
	
				// for each new partstructure add an array for holding instances
					$partStructure = $part->getPartStructure();
					$psName = $partStructure->getDisplayName();
					if (!in_array($psName, 	
							array_keys($this->data[$rsName][$instance]))) {
						$this->data[$rsName][$instance][$psName] = array();
						$this->_data_ids[$rsName][$instance][$psName] = array();
					}
					
					// again with the instances
					$partValue = $part->getValue();
					$id = $part->getId();
					$idString = $id->getIdString();
					$idArray = explode("::", $idString);
					$this->data[$rsName][$instance][$psName][$idArray[2]] = 
						$partValue->asString();
					$this->_data_ids[$rsName][$instance][$psName][$idArray[2]]
						= $part->getId();
				}
			}
		}
		// this call does all FILE records information
		$this->_populateFileInfo();
		
		// keep original data for modification check.
		$this->_loadedData = $this->data;
	}
	
	/**
	 * Store and changes to our data set to our Asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	final private function _storeData () {
		if (isset($changes))
			unset($changes);
		// only change things when you must
		if (isset($this->data) && $this->_dataChanged()) {
			$changes = array();	// array for storing a part id and its new value
			
			// go through all recordstructures
			foreach ($this->data as $rs => $instances) {
				
				if (is_array($instances) && ($rs != 'FILE')) {
					// go through each instance of the recordstructure
					foreach ($instances as $instance => $record) {
						if (!isset($this->_data_ids[$rs][$instance])) 
							$this->_createInstance($rs, $instance);
						else if (is_array($record)) {
					// for each array of part values find out which have changed
							foreach ($record as $ps => $values) {
								$differences = array_diff_assoc(
									$values, 
									$this->_loadedData[$rs][$instance][$ps]);
								
								// add each change to the array of changes
								if (count($differences) > 0) {
									foreach ($differences as $key => $value) {
$changes[$this->_data_ids[$rs][$instance][$ps][$key]->getIdString()] = $value;
									}
								}
							}
						}
					}
				}
			}
		}
		if (isset($this->data['FILE']) && (count($this->data['FILE']) > 0) && 
				$this->_fileDataChanged()) {
			// handle all file data changes
			$this->_changeFileInfo();
		}
		
		// make them changes
		if (isset($changes)) {
		$idManager = Services::getService("Id");
			foreach ($changes as $idString => $value) {
				$id = $idManager->getId($idString);
				$part = $this->_asset->getPart($id);
				$part->updateValueFromString($value);
			}
 		$this->_loadedData = $this->data;	// new data persisted
		}
	}
	
	/**
	 * Changes the stored file information
	 *
	 * All changes to files are done here (even deletion) 
	 * @return void
	 * @access private
	 * @since 1/27/06
	 */
	final private function _changeFileInfo () {
		$idManager = Services::getService("Id");
		$changes = array();
		foreach ($this->data['FILE'] as $instance => $file) {
			$fpids = $this->_data_ids['FILE'][$instance];
			$lfile = $this->_loadedData['FILE'][$instance];
			$frecord = $this->_asset->getRecord(
				$idManager->getId($file['assoc_file_id'][0]));
		
			// delete_file can change
			if ($file['delete_file'][0] != $lfile['delete_file'][0]) {
				$this->_asset->deleteRecord($idManager->getId(
					$file['assoc_file_id'][0]));
				// unset the file in the data arrays
				unset($this->data['FILE'][$instance],
					$this->_loadedData['FILE'][$instance],
					$this->_data_ids['FILE'][$isntance]);
				if (count($this->data['FILE']) == 0)
					unset($this->data['FILE']);
			} else {
				// new_file_path can change
				if ($file['new_file_path'][0] != $lfile['new_file_path'][0]) { 
					$fparts = $this->_asset->getPartsByPartStructure(
						$idManager->getId('FILE_DATA'));
					$fpart = $fparts->next();
					$fpart->updateValue(
						file_get_contents($file['new_file_path'][0]));
				}
				if ($file['FILE_NAME'][0] != $lfile['FILE_NAME'][0]) {
				// FILE_NAME can change
					$fpart = $this->_asset->getPart(
						$idManager->getId(
						$fpids['FILE_NAME'][0]));
					$fpart->updateValueFromString($file['FILE_NAME'][0]);
				}
				
				// FILE_SIZE can't change
				if ($file['FILE_SIZE'][0] != $lfile['FILE_SIZE'][0])
					$file['FILE_SIZE'][0] = $lfile['FILE_SIZE'][0];
				
				// DIMENSIONS can't change
				if ($file['DIMENSIONS'][0] != $lfile['DIMENSIONS'][0])
					$file['DIMENSIONS'][0] = $lfile['DIMENSIONS'][0];
				
				// MIME_TYPE can't change
				if ($file['MIME_TYPE'][0] != $lfile['MIME_TYPE'][0])
					$file['MIME_TYPE'][0] = $lfile['MIME_TYPE'][0];
				
				// assoc_file_id can't change
				if ($file['assoc_file_id'][0] != $lfile['assoc_file_id'][0])
					$file['assoc_file_id'][0] = $lfile['assoc_file_id'][0];
			}			
		}	
		// @todo determine if this is a necessary step	
		$this->_populateFileInfo();
	}

	/**
	 * Populates the data array with usable file information
	 *
	 * Plugins get only minimal access to file information and handling
	 * @return void
	 * @access private
	 * @since 1/27/06
	 */
	final private function _populateFileInfo () {
		// plugins get specific file information, can request URL or 
		// data via functions defined above
		$idManager = Services::getService("Id");
		$frecords = $this->_asset->getRecordsByRecordStructure(
			$idManager->getId("FILE"));
		$farray = array("FILE_DATA", "THUMBNAIL_DATA", "THUMBNAIL_MIME_TYPE", 
			"THUMBNAIL_DIMENSIONS");

		// always reset the data
		$this->data['FILE'] = array();
		$this->_data_ids['FILE'] = array();

		// maintain record order
		$sets = Services::getService("Sets");
		$recordOrder = $sets->getPersistentSet($this->_asset->getId());
		$fordered = array();

		// populate fordered array with current file records
		while ($frecords->hasNext()) {
			$frecord = $frecords->next();
			$frid = $frecord->getId();
			if (!$recordOrder->isInSet($frid))
				$recordOrder->addItem($frid);
			$fordered[$recordOrder->getPosition($frid)] = $frid;
		}

		// removing outdated id's in the set.		
		$recordOrder->reset();
		while ($recordOrder->hasNext()) {
			$recId = $recordOrder->next();
			
			if (!isset($fordered[$recordOrder->getPosition($recId)]))
				$recordOrder->removeItem($recId);
		}
		
		// populate the data array with the file data
		foreach ($fordered as $frecid) {
			$frecord = $this->_asset->getRecord($frecid);
			
			$this->data['FILE'][] = array();
			$this->_data_ids['FILE'][] = array();
			
			$instance = count($this->data['FILE']) - 1;
			$file = $this->data['FILE'][$instance];
			$file_ids = $this->_data_ids['FILE'][$instance];

			$parts = $frecord->getParts();
			while ($parts->hasNext()) {
				$part = $parts->next();
				$id = $part->getId();
				$ps = $part->getPartStructure();
				$psid = $ps->getId();
				$psidString = $psid->getIdString();
				// plugin safe parts
				if (!in_array($psidString, $farray)) {
					$file[$psidString] = array();
					$file_ids[$psidString] = array();
					$file[$psidString][] = $part->getValue();
					$file_ids[$psidString][] = $id->getIdString();
				}
			}
			$file['assoc_file_id'][] = $frecid->getIdString();
			$file['new_file_path'][] = '';
			$file['delete_file'][] = '';
		}
	}
	
	/**
	 * Answer true if our data has been modified
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/13/06
	 */
	final private function _dataChanged () {
		// @todo test different implementations of this function
		$new = serialize($this->data);
		$old = serialize($this->_loadedData);
		if ($old == $new)
			return false;
		return true;
	}
	
	/**
	 * Answer true if our data has been modified
	 * 
	 * @return boolean
	 * @access private
	 * @since 1/13/06
	 */
	final private function _fileDataChanged () {
		// @todo test different implementations of this function
		$new = serialize($this->data['FILE']);
		$old = serialize($this->_loadedData['FILE']);
		if ($old == $new)
			return false;
		return true;
	}

	/**
	 * Initializes the structures of the Asset to allow for record creation
	 * 
	 * @return void
	 * @access public
	 * @since 3/1/06
	 */
	final public function getStructuresForPlugin () {
		if (!isset($this->_structures)) {
			$db = Services::getService("DBHandler");
			
			$type = $this->_asset->getAssetType();
			
			$query = new SelectQuery();
			$query->addTable("plugin_manager");
			$query->addTable("plugin_type", INNER_JOIN, "plugin_type.type_id = plugin_manager.fk_plugin_type");
			$query->addWhere("plugin_type.type_domain = '".
				addslashes($type->getDomain())."'");
			$query->addWhere("plugin_type.type_authority = '".
				addslashes($type->getAuthority())."'");
			$query->addWhere("plugin_type.type_keyword = '".
				addslashes($type->getKeyword())."'");
			$query->addColumn("*");
			
			$results = $db->query($query, IMPORTER_CONNECTION);
	
			$id = Services::getService("Id");
			$rm = Services::getService("Repository");
			$sites_rep = $rm->getRepository($id->getId(
				"edu.middlebury.segue.sites_repository"));
			
			$structures = array();
			// populate structures array with displayname to id association
			while ($results->hasMoreRows()) {
				$result = $results->next();
				
				$rs = $sites_rep->getRecordStructure($id->getId(
					$result['plugin_manager.fk_schema']));
				
				$structures[$rs->getDisplayName()] =
					$result['plugin_manager.fk_schema'];
			}
			$this->_structures = $structures;
		}

		return $this->_structures;
	}

	/**
	 * Creates a new Record for the instance held in $this->data
	 * 
	 * @param string $dname RecordStructure display name
	 * @param integer $instance index in $this->data for record
	 * @access private
	 * @since 3/1/06
	 */
	final private function _createInstance ($dname, $instance) {
		// @todo take the data in $this->data[$rs][$instance] and create a 
		// proper record for it in the database.
		
		$rm = Services::getService("Repository");
		$id = Services::getService("Id");
		$pm = Services::getService("Plugs");
		$dtm = Services::getService("DataTypeManager");
		
		$sites_rep = $rm->getRepository($id->getId(
			"edu.middlebury.segue.sites_repository"));

		// need: RecordStructureId, asset, data
		$structures = $this->getStructuresForPlugin();
		$rs = $sites_rep->getRecordStructure($id->getId($structure[$dname]));
		$partstructs = $rs->getPartStructures();
		
		$record = $this->_asset->createRecord($id->getId($structures[$dname]));
		
		while ($partstructs->hasNext()) {
			$partstruct = $partstructs->next();
			$type = $partstruct->getType();
			// this is the class I need for the part object
			$class = $dtm->primitiveClassForType($type->getKeyword());

			foreach 
				($this->data[$dname][$instance][$partstruct->getDisplayName()] 		
					as $inst => $val) {
				eval('$object = '.$class.'::fromString($val);');
				if (!is_object($object)) {
					throwError( new Error("PluginManager", "bad part object: creating instance", true));
					// @todo handle an error here.
				} else {
					$part = $record->createPart($partstruct->getId(), $object);
					$partId = $part->getId();
					$this->_data_ids[$dname][$instance] = array();
					$this->_data_ids[$dname][$instance][$partstruct->getDisplayName()] = array();
					$this->_data_ids[$dname][$instance][$partstruct->getDisplayName()][$inst] = $partId->getIdString();
				}
			}
		}
	}
	
/*********************************************************
 * Versioning
 *********************************************************/
	/**
	 * Answer an array of the versions for this plugin instance with the most 
	 * recent version first.
	 *
	 * @return array of SeguePluginVersion objects
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersions () {
		if (!isset($this->versions)) {
			$this->versions = array();
			$query = new SelectQuery;
			$query->addTable('segue_plugin_version');
			$query->addColumn('version_id');
			$query->addColumn('tstamp');
			$query->addColumn('comment');
			$query->addColumn('agent_id');
			$query->addWhereEqual('node_id', $this->getId());
			$query->addOrderBy('tstamp', SORT_DESC);
						
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			
			$idMgr = Services::getService("Id");
			$number = $result->getNumberOfRows();
			while ($result->hasNext()) {
				$row = $result->next();
				$this->versions[] = new SeguePluginVersion($this, $row['version_id'], DateAndTime::fromString($row['tstamp']), $idMgr->getId($row['agent_id']), $number, $row['comment']);
				
				$number--;
			}
		}
		
		return $this->versions;
	}
	
	/**
	 * Answer a particular version.
	 * 
	 * @param string $versionId
	 * @return object SeguePluginVersion
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersion ($versionId) {
		ArgumentValidator::validate($versionId, NonZeroLengthStringValidatorRule::getRule());
		
		if (!isset($this->versions))
			$this->getVersions();
		
		foreach ($this->versions as $version) {
			if ($version->getVersionId() == $versionId)
				return $version;
		}
		
		throw new UnknownIdException("No version with id, '$versionId', was found for this plugin instance.");
	}
	
	/**
	 * Execute the plugin and return the markup for a version.
	 * 
	 * @param object DOMDocument $versionXml
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	public function executeAndGetVersionMarkup ( DOMDocument $versionXml ) {
		$this->setShowControls(false);
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(
			get_class($this).':'.$this->getId());
		$this->_baseUrl = $harmoni->request->mkURL();
		
		$markup = $this->getVersionMarkup($versionXml);		
		
		$harmoni->request->endNamespace();
		
		return $markup;
	}
	
	/**
	 * Import a historical version, for instance from a backup system.
	 * 
	 * @param object DOMDocument $versionXml The version markup.
	 * @param object Id $agentId The agent id that created the version.
	 * @param object DateAndTime $timestamp The time the version was created.
	 * @param string $comment A comment associated with the version.
	 * @return void
	 * @access public
	 * @since 1/23/08
	 */
	public function importVersion (DOMDocument $versionXml, Id $agentId, DateAndTime $timestamp, $comment) {
		$query = new InsertQuery;
		$query->setTable('segue_plugin_version');
		$query->addValue('node_id', $this->getId());
		$query->addValue('tstamp', $timestamp->asString());
		$query->addValue('agent_id', $agentId->getIdString());
		$query->addValue('comment', $comment);
		$query->addValue('version_xml', $versionXml->saveXML());
					
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
		
		unset($this->versions);
	}
	
}
?>