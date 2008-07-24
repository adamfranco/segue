<?php
/**
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This inteface defines methods needed for 'text templates', Segue's way of 
 * supporting pluggable strings in HTML. 
 * 
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
interface Segue_Wiki_TextTemplate {
	
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
	public function getDescriptionHtml ();
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 7/14/08
	 */
	public function generate (array $paramList);
	
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
	public function getHtmlMatches ($text);
	
}

?>