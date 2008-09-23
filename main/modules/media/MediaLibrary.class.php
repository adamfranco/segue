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
		return self::getButton($libraryId, _("Insert Image"), $buildString, $writeJsCallback);
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
	public static function getButton ($libraryId, $buttonTitle, $buildStringJs, $writeJsCallback) {
		ob_start();
		print "this.writeCallback = ".$writeJsCallback."; ";
		print "this.onUse = function (mediaFile) { ";
		print		$buildStringJs." ";
		print 		"this.writeCallback(newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$libraryId."', this); ";
		$js = preg_replace("/\s+/", " ", ob_get_clean());
		
		return "<input type='button' value='".$buttonTitle."' onclick=\"".$js."\" />";
	}
	
}

?>