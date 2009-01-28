<?php
/**
 * @since 1/27/09
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This template allows the embedding of mp3 audio in a page
 * 
 * @since 1/27/09
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_audio
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
	 * @since 1/27/09
	 */
	public function isEditorSafe () {
		return false;
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
	 * @since 1/27/09
	 */
	public function getDescriptionHtml () {
		return _(
"<div>
	<h4>audio</h4>
	<p>This template allows the embedding of mp3 audio in a page.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt><strong>url</strong></dt>
		<dd>The url to the MP3 file. (Required)</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{audio|url=http://media.freesound.org/data/14/previews/14777__acclivity__WavesOnBeach_preview.mp3}}</li>
		
	</ul>
</div>");
	}
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 1/27/09
	 */
	public function generate (array $paramList) {
		if (!isset($paramList['url']))
			throw new InvalidArgumentException("url is required.");
		
		// Validate our options
		if (!preg_match('/^https?:\/\/[a-z0-9_\.\/?&=,;:%+~\s-]+$/i', $paramList['url']))
			throw new InvalidArgumentException("Invalid url.");
		
		ob_start();
		$playerUrl = $this->getPublicFileUrl("player.swf");
		print "<script type='text/javascript' src='".$this->getPublicFileUrl('audio-player.js')."'></script>";
		print '
<object width="290" height="24" id="audio_'.rand(1,10000000).'" data="'.$playerUrl.'" type="application/x-shockwave-flash">
	<param value="'.$playerUrl.'" name="movie" />
	<param value="high" name="quality" />
	<param value="false" name="menu" />
	<param value="transparent" name="wmode" />
	<param value="soundFile='.$paramList['url'].'" name="FlashVars" />
</object>';
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
	 * @since 1/27/09
	 */
	public function getHtmlMatches ($text) {
	
		$regex = '@
<script 
\s+
type=[\'"]text/javascript[\'"]
\s+
src=["\'](?: [^\'"]+)audio-player.js[\'"]
\s*
>\s*</script>
\s*

(?: <br\s*/>\s*)*

# Begin object open-tag
<object
\s*
[^>]*

data=["\'](?: [^\'"]+)AudioPlayer/player\.swf[\'"]

[^>]*
>		
# End of the object open-tag

# optional param tags
(?: \s* <param [^>]+ />)*

\s*
<param 
[^>]+
value=[\'"]soundFile=([^\'"]+)[\'"]
[^>]+
/>

# optional param tags
(?: <param [^>]+ />)*

\s*
# object close tag
</object>

			@iUx';
		
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
				else
					throw new OperationFailedException("url is not specified.");
				
				$results[$match] = $matchParams;
			} catch (Exception $e) {
			}
		}
		
// 		printpre(htmlentities(print_r($results, true)));
// 		throw new Exception('test');
// exit;
		return $results;
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
	private function getPublicFileUrl ($filename) {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$url = $harmoni->request->quickURL('plugin_manager', 'public_file', 
			array('plugin' => HarmoniType::typeToString(new Type('SeguePlugins', 'edu.middlebury', 'AudioPlayer')),
				'file' => $filename));
		$harmoni->request->endNamespace();
		return $url;
	}
	
}

?>