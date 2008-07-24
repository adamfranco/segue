<?php
/**
 * @since 7/24/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This text template allows the embedding of a google map.
 * 
 * @since 7/24/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_googlemap
	implements Segue_Wiki_TextTemplate
{
		
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
	<h4>googlemap</h4>
	<p>This template will insert an embedded google map.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt>url<dt>
		<dd>The base url of the map.</dd>
		<dt>s<dt>
		<dd>The security token that Google maps uses for their embeded maps, visible in the embed-source code. (Required)</dd>
		<dt>width</dt>
		<dd>The integer width of the map in pixels. (Optional) Example: 325</dd>
		<dt>height</dt>
		<dd>The integer height of the map in pixels. (Optional) Example: 250</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{googlemap|url=http://maps.google.com/?ie=UTF8&amp;t=h&amp;ll=44.009021,-73.174374&amp;spn=0.011451,0.013046&amp;z=16|s=AARTsJqzARj-Z8VnW5pkPMLMmZbqrJcYpw}}</li>
		
		<li>{{googlemap|url=http://maps.google.com/?ie=UTF8&amp;t=h&amp;ll=44.009021,-73.174374&amp;spn=0.011451,0.013046&amp;z=16|s=AARTsJqzARj-Z8VnW5pkPMLMmZbqrJcYpw|width=425|height=350}}</li>
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
		
		// pull out the embed option and 's' from the url if needed.
		if (preg_match('/^(http:\/\/maps\.google\.com\/[a-z0-9_\.\/?&=,;:%+~-]+)&amp;output=embed&amp;s=([a-z0-9_-]+)$/i', $paramList['url'], $matches)) {
			$paramList['url'] = rtrim($matches[1], '&');
			$paramList['s'] = $matches[2];
		}
				
		// Validate our options
		if (!preg_match('/^http:\/\/maps\.google\.com\/[a-z0-9_\.\/?&=,;:%+~-]+$/i', $paramList['url'])) {
			printpre("Invalid url.");
			throw new InvalidArgumentException("Invalid url.");
		}
		if (!isset($paramList['s']) || !preg_match('/^[a-z0-9_-]+$/i', $paramList['s']))
			throw new InvalidArgumentException("Invalid s.");
		
		
		// default width
		if (!isset($paramList['width'])) {
			$paramList['width'] = 425;
		} else {
			$paramList['width'] = strval(intval($paramList['width']));
		}
		
		// default height
		if (!isset($paramList['height'])) {
			$paramList['height'] = 350;
		} else {
			$paramList['height'] = strval(intval($paramList['height']));
		}
		
		
		// print out the form and return it.
		ob_start();
		
		print "\n".'<iframe width="'.$paramList['width'].'" height="'.$paramList['height'].'" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="'.$paramList['url'].'&amp;output=embed&amp;s='.$paramList['s'].'"></iframe>';
		
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
<iframe

[^<>]*		# Other stuff

src=[\'"]
	(http:\/\/maps\.google\.com\/[a-z0-9_\.\/?&=,;:%+~-]+)&amp;output=embed&amp;s=([a-z0-9_-]+)
[\'"]

[^<>]*		# Other stuff

><\/iframe>
			/ix';
		
		$results = array();
		preg_match_all($regex, $text, $matches);
// 		printpre(htmlentities(print_r($matches, true)));
		foreach ($matches[0] as $i => $match) {
// 			printpre("working on: ".htmlentities($match));
			try {
				$matchParams = array();
				$matchParams['url'] = $matches[1][$i];
				$matchParams['s'] = $matches[2][$i];

				// If the url/id is valid, try searching for the rest of our params
				try {
					$matchParams['width'] = $this->getWidthFromHtml($match);
				} catch (Exception $e) {}
				try {
					$matchParams['height'] = $this->getHeightFromHtml($match);
				} catch (Exception $e) {}
				
				$results[$match] = $matchParams;
			} catch (Exception $e) {
			}
		}
		return $results;
	}
	
	/**
	 * Answer a width from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 7/15/08
	 */
	protected function getWidthFromHtml ($embedHtml) {
		$regex = '/

# Width Attributes
width=[\'"]([0-9]+)(?: px)?[\'"]

# style-based width
| 
style=[\'"]		# Attribute start
	[^\'"]*		# other style properties
	width:\s?([0-9]+)(?: px)?
	[^\'"]*		# other style properties
[\'"]			# Attribute end
		
		/ix';
		
		if (!preg_match($regex, $embedHtml, $matches))
			throw new OperationFailedException("Could not match width against ".$regex." for service ".$this->name.".");
		
		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match width against ".$regex." for service ".$this->name.".");
	}
	
	/**
	 * Answer a height from an object or embed block of HTML. 
	 * Throw an OperationFailedException if not matched.
	 * 
	 * @param string $embedHtml
	 * @return string The id
	 * @access protected
	 * @since 7/15/08
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
			throw new OperationFailedException("Could not match height against ".$regex." for service ".$this->name.".");
		
		if ($matches[1])
			return $matches[1];
		else if ($matches[2])
			return $matches[2];
		
		throw new OperationFailedException("Could not match height against ".$regex." for service ".$this->name.".");
	}
}

?>