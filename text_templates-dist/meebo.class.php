<?php
/**
 * @since 8/25/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * Embeds a Meebo chat widget.
 * 
 * @since 8/25/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_meebo
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
	 * @since 8/19/08
	 */
	public function getDescriptionHtml () {
		return _(
"<div>
	<h4>meebo</h4>
	<p>This template will insert a mebo chat widget. It is easiest to copy paste the embed code from <a href='http://www.meebome.com/' target='_blank'>http://www.meebome.com/</a> rather than manually writing the template markup.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt><strong>id</strong></dt>
		<dd>The id of a MeeboMe widget. Example: kmpwkwLNWz</dd>
		<dt><strong>width</strong></dt>
		<dd>The integer width of the chat widget in pixels. (Optional) Example: 325</dd>
		<dt><strong>height</strong></dt>
		<dd>The integer height of the chat widget in pixels. (Optional) Example: 250</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{meebo|id=kmpwkwLNWz|width=190|height=275}}</li>
	</ul>
</div>");
	}
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 8/19/08
	 */
	public function generate (array $paramList) {
		if (!isset($paramList['id']))
			throw new InvalidArgumentException("id is required.");
		
		$validRegexes = array(
					'width'			=> '/^[0-9]+$/',
					'height'		=> '/^[0-9]+$/'
				);
		
		$defaults = array(
					'width'			=> 190,
					'height'		=> 275
				);
		
		foreach ($defaults as $key => $val) {
			// default width
			if (!isset($paramList[$key]) || !preg_match($validRegexes[$key], $paramList[$key])) {
				$paramList[$key] = $val;
			}
		}
				
		// check Id
		if (!preg_match('/^[a-z0-9_-]+$/i', $paramList['id']))
			throw new InvalidArgumentException("Invalid id, '".$paramList['id']."'.");
		
		
		return "\n"
		."<!-- Beginning of meebo me widget code.
Want to talk with visitors on your page?  
Go to http://www.meebome.com/ and get your widget! -->\n"
		.'<embed src="http://widget.meebo.com/mm.swf?'.$paramList['id'].'"  type="application/x-shockwave-flash" width="'.$paramList['width'].'" height="'.$paramList['height'].'"></embed>';
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
	 * @since 8/19/08
	 */
	public function getHtmlMatches ($text) {
		$regex = '/
<embed

[^<>]*		# Other stuff

src=[\'"]
	http:\/\/widget\.meebo\.com\/mm\.swf\?([a-z0-9_-]+)
[\'"]

[^<>]*		# Other stuff

><\/embed>
			/ix';
		
		$results = array();
		preg_match_all($regex, $text, $matches);
// 		printpre(htmlentities(print_r($matches, true)));
		foreach ($matches[0] as $i => $match) {
// 			printpre("working on: ".htmlentities($match));
			try {
				$matchParams = array();
				$matchParams['id'] = $matches[1][$i];
				
				
				// If the url/id is valid, try searching for the rest of our params
				try {
					$matchParams['width'] = $this->getWidthFromHtml($match);
				} catch (Exception $e) { print $e->getMessage();}
				try {
					$matchParams['height'] = $this->getHeightFromHtml($match);
				} catch (Exception $e) {}
				
				$results[$match] = $matchParams;
			} catch (Exception $e) {
			}
		}
		
// 		printpre(htmlentities(print_r($results, true)));
// 		throw new Exception('test');
		return $results;
	}
	
	/**
	 * Answer a parameter from a url. An operation failed exception will be thrown
	 * if not found.
	 * 
	 * @param string $param
	 * @param string $url
	 * @return string The value.
	 * @access protected
	 * @since 8/19/08
	 */
	protected function getParamFromUrl ($param, $url) {
		$query = parse_url($url, PHP_URL_QUERY);
// 		printpre(htmlentities($query));
		
		// Google Calendar doesn't use brackets [] on multi-valued parameters, so
		// add those in to allow parse_str to work.
		$query = preg_replace('/&(amp;)?(src|color)=/', '&\1\2[]=', $query);
		
		mb_parse_str($query, $queryParams);
		
// 		printpre($queryParams);
		
		if (isset($queryParams[$param]))
			return $queryParams[$param];
		else if (isset($queryParams['amp;'.$param]))
			return $queryParams['amp;'.$param];
		else
			throw new OperationFailedException("Could not find param '$param' in url '$url'");
	}
	
	/**
	 * Answer a width from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 8/19/08
	 */
	protected function getWidthFromHtml ($embedHtml) {
		$regex = '/

# Width Attributes
width=[\'"]([0-9]+)(?: px)?[\'"]
		
		/ix';
		
		if (!preg_match($regex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match width against ".$regex.".");

		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match width against ".$regex.".");
	}
	
	/**
	 * Answer a height from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 8/19/08
	 */
	protected function getHeightFromHtml ($embedHtml) {
		$regex = '/

# Height Attributes
height=[\'"]([0-9]+)(?: px)?[\'"]

# style-based height
| 
style=[\'"]		# Attribute start
	[^\'"]*		# other style properties
	height:\s?([0-9]+)(?: px)?
	[^\'"]*		# other style properties
[\'"]			# Attribute end
		
		/ix';
		
		if (!preg_match($regex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match height against ".$regex.".");
		
		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match height against ".$regex.".");
	}
	
}

?>