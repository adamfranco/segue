<?php
/**
 * @since 9/23/08
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY_DIR.'/main/modules/user/UserDataHelper.class.php');

/**
 * The MediaLibrary contains general methods for working with the javascript media library.
 * 
 * @since 9/23/08
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_MediaLibrary {
		
	/**
	 * Answer the HTML for a set of media library buttons for common usage.
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getMediaButtons ($libraryId, $writeJsCallback) {
		ob_start();
		print "\n\t".self::getImageButton($libraryId, $writeJsCallback);
		print "\n\t".self::getFileLinkButton($libraryId, $writeJsCallback);
		print "\n\t".self::getFileThumbnailLinkButton($libraryId, $writeJsCallback);
		print "\n\t".self::getEmbedAudioVideoButton($libraryId, $writeJsCallback);
		return ob_get_clean();
	}
	
	/**
	 * Answer the HTML for an insert image button.
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getImageButton ($libraryId, $writeJsCallback) {
		$buildString = "
var title = mediaFile.getTitles()[0];
if (title) {
	title = title.escapeHTML();
};
var newString = '\\n<img src=\'' + mediaFile.getUrl().escapeHTML() + '\' title=\'' + title + '\' border=\'0\' />';
";
		return self::getButton($libraryId, _("Insert Image"), $buildString, $writeJsCallback,
			array('image/jpeg', 'image/gif', 'image/png', 'image/tiff')
		);
	}
	
	/**
	 * Answer the HTML for an insert file-link button.
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getFileLinkButton ($libraryId, $writeJsCallback) {
		$buildString = "
var downloadBar = document.createElement('div'); 
var link = downloadBar.appendChild(document.createElement('a'));
link.href = mediaFile.getUrl().escapeHTML();
var title = mediaFile.getTitles()[0];
if (title) {
	title = title.escapeHTML();
};
link.title = '"._("Download")." '.escapeHTML() + '&quot;' + title + '&quot;';
link.innerHTML = title;
var newString = downloadBar.innerHTML;
";
		return self::getButton($libraryId, _("Insert File Link"), $buildString, $writeJsCallback);
	}
	
	/**
	 * Answer the HTML for an insert embedded audio or video button.
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getEmbedAudioVideoButton ($libraryId, $writeJsCallback) {
		$buildString = "
try {
	var newString = mediaFile.getEmbedTextTemplate();
} catch (e) {
	alert(e);
	var newString = '';
}
";
		return self::getButton($libraryId, _("Embed Audio/Video"), $buildString, $writeJsCallback);
	}
	
	/**
	 * Answer the HTML for an insert file-link button.
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getFileThumbnailLinkButton ($libraryId, $writeJsCallback) {
		$buildString = "
var downloadBar = document.createElement('div'); 
var link = downloadBar.appendChild(document.createElement('a'));
link.href = mediaFile.getUrl().escapeHTML();
var title = mediaFile.getTitles()[0];
if (title) {
	title = title.escapeHTML();
};
link.title = link.title = '"._("Download")." '.escapeHTML() + '&quot;' + title + '&quot;';
link.innerHTML = '<img src=\'' + mediaFile.getThumbnailUrl().escapeHTML() + '\' border=\'0\' />';
var newString = downloadBar.innerHTML;
";
		return self::getButton($libraryId, _("Insert File Thumbnail Link"), $buildString, $writeJsCallback);
	}
	
	/**
	 * Answer the HTML for a button
	 * 
	 * @param string $libraryId The id of the component this library is attached to.
	 * @param string $buttonTitle
	 * @param string $buildStringJs		Javascript code that should write a 'newString' variable.
	 * @param string $writeJsCallback	A Javascript function to execute to write out the
	 *									results. Must take a single parameter which is an
	 *									HTML string. Example:
	 *									
	 *		function (htmlString) { edInsertContent(document.get_element_by_id('12345'), htmlString); }
	 *
	 * @return string
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function getButton ($libraryId, $buttonTitle, $buildStringJs, $writeJsCallback, array $allowedMimeTypes = array()) {
		ob_start();
		print "this.writeCallback = ".$writeJsCallback."; ";
		print "this.onUse = function (mediaFile) { ";
		print		$buildStringJs." ";
		print 		"this.writeCallback(newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$libraryId."', this";
		if (count($allowedMimeTypes)) {
			print ", ['".implode("', '", $allowedMimeTypes)."']";
		}
		print "); ";
		$js = preg_replace("/\s+/", " ", ob_get_clean());
		
		return "<input type='button' value='".$buttonTitle."' onclick=\"".$js."\" />";
	}
	
	/**
	 * Answer the HEAD html for the media library javascript
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/09
	 * @static
	 */
	public static function getHeadHtml () {		
		ob_start();
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/TabbedContent.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/prototype.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/js_quicktags.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/brwsniff.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/MediaLibrary.js'></script>";
		print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/MediaLibrary.css'/>";
		
		print "
		<script type='text/javascript'>
		// <![CDATA[";
		
		foreach (self::$externalLibraries as $library) {
			print '
		MediaLibrary.externalLibraries.push({
			title: "'.$library['title'].'",
			jsClass: "'.$library['jsClass'].'",
			jsSourceUrl: "'.$library['jsSourceUrl'].'"';
			
			foreach ($library['extraParams'] as $key => $val) {
				print ',
			'.$key.': "'.$val.'"';
			}
			print '
		});';
			
		}
		
		print "
		// ]]>
		</script>";
		return ob_get_clean();
	}
	
	private static $externalLibraries = array();
	
	/**
	 * Add an external library to our configuration
	 * 
	 * @param string $title
	 * @param string $jsClass The javascript class of the library.
	 * @param string $jsSourceUrl The path to the javascript file that defines the $jsClass.
	 * @param array $extraParams An associative array of other parameters to pass to the library.
	 * @return void
	 * @access public
	 * @since 1/13/09
	 * @static
	 */
	public static function addExternalLibrary ($title, $jsClass, $jsSourceUrl, array $extraParams = array()) {
		ArgumentValidator::validate($title, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($jsClass, NonzeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($jsSourceUrl, NonzeroLengthStringValidatorRule::getRule());
		
		self::$externalLibraries[] = array(
			'title' => $title,
			'jsClass' => $jsClass,
			'jsSourceUrl' => $jsSourceUrl,
			'extraParams' => $extraParams);
	}
	
}

?>