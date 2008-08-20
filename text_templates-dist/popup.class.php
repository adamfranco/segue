<?php
/**
 * @since 8/20/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This template allows the ability to open a popup window from a link. A feature otherwise
 * removed due to its use of javascript.
 * 
 * @since 8/20/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_popup
	implements Segue_Wiki_TextTemplate
{
	
	/**
	 * Answer true if this text template is safe for inclusion and editing with
	 * the WYSIWYG HTML editor. If true, users will not see the text template 
	 * markup in the editor, but rather the generated code. This should really
	 * only be used for templates that work with HTML generated directly by the editor.
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/20/08
	 */
	public function isEditorSafe () {
		return true;
	}
	
	/**
	 * Answer a block of HTML text that describes this template and its parameters.
	 * Use an h4 tag for the template name.
	 * Use a definition list for parameters. 
	 *
	 * Example:
	 *
	 * <div>
	 *		<h4>video</h4>
	 *		<p>This template will allow the embedding of Flash Video from a variety
	 *		of sources. These sources must be configured by the Segue administrator
	 *		if they are not included in the default configuration.</p>
	 * 		<h4>Parameters:</h4>
	 *		<dl>
	 *			<dt>service<dt>
	 *			<dd>The service at which this video is hosted -- all lowercase.
	 *			Examples: youtube, youtube_playlist, google, vimeo, hulu</dd>
	 *			<dt>id</dt>
	 *			<dd>The Id of the video, the specific form of this dependent on the
	 *			service, but generally this can be found in the URL for flash-video
	 *			file. Example for YouTube: s13dLaTIHSg</dd>
	 *			<dt>width</dt>
	 *			<dd>The integer width of the player in pixels. Example: 325</dd>
	 *			<dt>height</dt>
	 *			<dd>The integer height of the player in pixels. Example: 250</dd>
	 *		</dl>
	 *		<h4>Example Usage:</h4>
	 *		<p>{{video|service=youtube|id=s13dLaTIHSg|width=425|height=344}}</p>
	 *		<p>Note: If you paste the embed code from a supported service into
	 *		a text block, it will automatically be converted into the template markup
	 *		when saved</p>
	 * </div>
	 * 
	 * 
	 * @return string
	 * @access public
	 * @since 7/16/08
	 */
	public function getDescriptionHtml () {
		return _(
"<div>
	<h4>popup</h4>
	<p>This template will open a link in a popup window.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt><strong>url</strong></dt>
		<dd>The url to link to. (Required)</dd>
		<dt><strong>text</strong></dt>
		<dd>The text to display in the link. (Required)</dd>
		<dt><strong>window_name</strong></dt>
		<dd>A name for the new window. If given, multiple links can open in the same popup window rather than spawning new windows. Must be letters, numbers, or underscores. (Optional) Example: help_window</dd>
		<dt><strong>width</strong></dt>
		<dd>The integer width of the popup window in pixels. (Optional) Example: 325</dd>
		<dt><strong>height</strong></dt>
		<dd>The integer height of the popup window in pixels. (Optional) Example: 250</dd>
		<dt><strong>resizable</strong></dt>
		<dd>If yes, the popup window will be resizable. 'yes' or 'no' - default is 'yes' (Optional)</dd>
		<dt><strong>location</strong></dt>
		<dd>If yes, the popup window will have the location bar. 'yes' or 'no' - default is 'no' (Optional)</dd>
		<dt><strong>scrollbars</strong></dt>
		<dd>If yes, the popup window will have scrollbars if needed. 'yes' or 'no' - default is 'yes' (Optional)</dd>
		<dt><strong>status</strong></dt>
		<dd>If yes, the popup window will have a status bar displayed at the bottom of the window. 'yes' or 'no' - default is 'yes' (Optional)</dd>
		<dt><strong>menubar</strong></dt>
		<dd>If yes, the popup window will have the menu bar with file, edit, menus. 'yes' or 'no' - default is 'no' (Optional)</dd>
		<dt><strong>toolbar</strong></dt>
		<dd>If yes, the popup window will have the tool bar with back/forward/refresh buttons. 'yes' or 'no' - default is 'no' (Optional)</dd>
		<dt><strong>top</strong></dt>
		<dd>The integer position of the popup window in pixels from the top of the screen. (Optional) Example: 25</dd>
		<dt><strong>left</strong></dt>
		<dd>The integer position of the popup window in pixels from the left side of the screen. (Optional) Example: 250</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{popup|url=http://slashdot.org|text=Slashdot|resizable=no|location=yes|menubar=yes|scrollbars=yes|status=no|toolbar=no|fullscreen=no|dependent=no|width=300|height=600}}</li>
		
	</ul>
</div>");
	}
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 7/14/08
	 */
	public function generate (array $paramList) {
		if (!isset($paramList['url']))
			throw new InvalidArgumentException("url is required.");
		
		// Validate our options
		if (!preg_match('/^http:\/\/[a-z0-9_\.\/?&=,;:%+~-]+$/i', $paramList['url']))
			throw new InvalidArgumentException("Invalid url.");
		
		if (!isset($paramList['text']) || !preg_match('/^.+$/i', $paramList['text']))
			throw new InvalidArgumentException("Invalid text.");
			
		if (!isset($paramList['window_name']))
			$paramList['window_name'] = '';
		
		if (!preg_match('/^[a-z0-9_]*$/i', $paramList['window_name']))
			throw new InvalidArgumentException("Invalid window_name.");
		
		$sizes = array('height', 'width', 'top', 'left');
		foreach ($sizes as $size) {
			if (isset($paramList[$size]))
				$paramList[$size] = strval(intval($paramList[$size]));
		}
		
		$options = array(
			'resizable'		=>	'yes', 
			'scrollbars'	=>	'yes', 
			'status'		=>	'yes', 
			'location'		=>	'no', 
			'menubar'		=>	'no',
			'toolbar'		=>	'no'
		);
		foreach ($options as $option => $default) {
			if (!isset($paramList[$option]) || ($paramList[$option] != 'yes' && $paramList[$option] != 'no'))
				$paramList[$option] = $default;
		}
		
		ob_start();
		print '<a href="'.$paramList['url'].'" ';
		print 'onclick="';
// 		print "var newWindow=";
		print "window.open(this.href, '".$paramList['window_name']."', '";
		
		$optionOutput = array();
		foreach ($options as $option => $default) {
			$optionOutput[] = $option.'='.$paramList[$option];
		}
		foreach ($sizes as $size) {
			if (isset($paramList[$size]))
				$optionOutput[] = $size.'='.$paramList[$size];
		}
		print implode(',', $optionOutput);
		
		print "');";
// 		print " newWindow.focus();";
		print " return false;\">".$paramList['text']."</a>";
		return ob_get_clean();
	}
	
	/**
	 * Answer an array of strings in the HTML that look like this template's output
	 * and list of parameters that the HTML corresponds to. e.g:
	 * 	array(
	 *		"<img src='http://www.example.net/test.jpg' width='350px'/>" 
	 *				=> array (	'server'	=> 'www.example.net',
	 *							'file'		=> 'test.jp',
	 * 							'width'		=> '350px'))
	 * 
	 * This method may throw an UnimplementedException if this is not supported.
	 *
	 * @param string $text
	 * @return array
	 * @access public
	 * @since 7/14/08
	 */
	public function getHtmlMatches ($text) {
		$regex = '/
<a
\s

# HREF first
(?: 
	href="([^"]+)"		#href
	\s+
)?

onclick="
(?: var\snewWindow=)?
window\.open\(this\.href,
\s?
\'([^\']*)\',		# window name
\s?
\'([^\']*)\'\);		# options

(?: \snewWindow\.focus\(\); )?

\s?
return\sfalse;?"

# HREF first
(?: 
	\s*
	href="([^"]+)"		#href
	\s*
)?

>
(.+)				# link text
<\/a>
			/iUx';
		
		$results = array();
		preg_match_all($regex, $text, $matches);
// 		printpre(htmlentities($text));
// 		printpre(htmlentities(print_r($matches, true)));
// 		throw new Exception('Test');
		foreach ($matches[0] as $i => $match) {
// 			printpre("working on: ".htmlentities($match));
			try {
				$matchParams = array();
				if (strlen($matches[1][$i]))
					$matchParams['url'] = $matches[1][$i];
				else if (strlen($matches[4][$i]))
					$matchParams['url'] = $matches[4][$i];
				else
					throw new OperationFailedException("href is not specified.");
				$matchParams['window_name'] = $matches[2][$i];
				$matchParams['text'] = $matches[5][$i];
				
				$srcParts = explode(',', $matches[3][$i]);
				$srcOptions = array();
				foreach ($srcParts as $part) {
					if (preg_match('/^([a-z]+)=(yes|no|[0-9]+)$/', $part, $partMatches))
						$matchParams[$partMatches[1]] = $partMatches[2];
				}
				
				$results[$match] = $matchParams;
			} catch (Exception $e) {
			}
		}
		
// 		printpre(htmlentities(print_r($results, true)));
// 		throw new Exception('test');
		return $results;
	}
	
}

?>