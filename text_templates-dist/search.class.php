<?php
/**
 * @since 7/16/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This search template is a basic example of the usage of text-templates.
 * It provides a search-box that can be configured to search different locations
 * by specifying a search string. This template does not have any configuration files.
 * 
 * @since 7/16/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_search
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
	<h4>search</h4>
	<p>This template will insert a search form that will open a new window with the search criteria supplied. The default search provider is Google, but other search providers can be used by specifying base_url and search_param_name options.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt>base_url<dt>
		<dd>The base url that the search form will submit to. If specified, the param_name must be specified as well.</dd>
		<dt>search_param_name</dt>
		<dd>The name of the query parameter that will be added to the URL.</dd>
		<dt>size</dt>
		<dd>Optional: The number of characters to show in the field</dd>
		<dt>button_text</dt>
		<dd>Optional: The text to display in the button field, the default is 'Search'</dd>
		<dt>additional_params</dt>
		<dd>Optional: A url encoded string of paramName=parmValue&paramName2=paramValue2 to be added to the query string. Example 'lang=en_US&go=true'</dd>
		<dt>provider</dt>
		<dd>Optional: A preconfigured search. If used, all other options except size will be ignored. Allowed values: google, wikipedia, yahoo, youtube</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{search}}</li>
		<li>{{search|size=50}}</li>
		<li>{{search|base_url=http://youtube.com/results|search_param_name=search_query}}</li>
		<li>{{search|base_url=http://youtube.com/results|search_param_name=search_query|additional_params=search_sort=video_date_uploaded&uploaded=d}}</li>
		<li>{{search|provider=yahoo}}</li>
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
		// Set google as the default provider if no other options are specified.
		if (!isset($paramList['provider']) && 
			(!isset($paramList['base_url']) || !strlen($paramList['base_url']) 
			|| !isset($paramList['search_param_name']) || !strlen($paramList['search_param_name'])))
		{
			$paramList['provider'] = 'google';
		}
		
		// Set up the provider if specified
		if (isset($paramList['provider'])) {	
			switch ($paramList['provider']) {
				case 'google':
					$paramList['base_url'] = 'http://www.google.com/search';
					$paramList['search_param_name'] = 'q';
					$paramList['button_text'] = _('Search Google');
					break;
				case 'wikipedia':
					$paramList['base_url'] = 'http://www.wikipedia.org/search-redirect.php';
					$paramList['search_param_name'] = 'search';
					$paramList['button_text'] = _('Search Wikipedia');
					break;
				case 'yahoo':
					$paramList['base_url'] = 'http://search.yahoo.com/search';
					$paramList['search_param_name'] = 'p';
					$paramList['button_text'] = _('Search Yahoo');
					break;
				case 'youtube':
					$paramList['base_url'] = 'http://youtube.com/results';
					$paramList['search_param_name'] = 'search_query';
					$paramList['button_text'] = _('Search YouTube');
					break;
				default:
					throw new InvalidArgumentException("Unsupported provider '".$paramList['provider']."'.");
			}
		}
		
		// Validate our options
		if (!preg_match('/^http(s?):\/\/[a-z0-9_\.\/%+~-]+$/i', $paramList['base_url']))
			throw new InvalidArgumentException("Invalid base_url.");
		if (!preg_match('/^[a-z0-9_-]+$/i', $paramList['search_param_name']))
			throw new InvalidArgumentException("Invalid search_param_name.");
		
		
		// Load any addtional parameters
		$additionalParams = array();
		if (isset($paramList['additional_params'])) {
			preg_match_all('/(?:&(?:amp;)?)?([a-z0-9_-]+)=([^&\'"<>]+)/i', $paramList['additional_params'], $matches);
			foreach ($matches[0] as $i => $match) {
				$additionalParams[$matches[1][$i]] = $matches[2][$i];
			}
		}
		foreach ($additionalParams as $name => $val) {
			if (!preg_match('/^[a-z0-9_-]+$/i', $name))
				throw new InvalidArgumentException("Invalid param name.");
			if (!preg_match('/^[a-z0-9_\s\.-]*$/i', $val))
				throw new InvalidArgumentException("Invalid param value.");
		}
		
		// default size
		if (!isset($paramList['size'])) {
			$paramList['size'] = 25;
		} else {
			$paramList['size'] = strval(intval($paramList['size']));
		}
		
		// default search
		if (!isset($paramList['default_search'])) {
			$paramList['default_search'] = '';
		}
		if (!preg_match('/^[a-z0-9_\s\.-]*$/i', $paramList['default_search']))
			throw new InvalidArgumentException("Invalid default_search.");
		
		// button text
		if (!isset($paramList['button_text'])) {
			$paramList['button_text'] = _('Search');
		}
		if (!preg_match('/^[a-z0-9_\s\.-]*$/i', $paramList['button_text']))
			throw new InvalidArgumentException("Invalid button_text.");
		
		// print out the form and return it.
		ob_start();
		
		print "\n<form action=\"".$paramList['base_url']."\" method=\"get\" target=\"_blank\">";
		print "\n\t<input type=\"text\" name=\"".$paramList['search_param_name']."\" value=\"".$paramList['default_search']."\" />";
		print "\n\t<input type=\"submit\" value=\"".$paramList['button_text']."\" />";
		foreach ($additionalParams as $name => $val) {
			print "\n\t<input type=\"hidden\" name=\"".$name."\" value=\"".$val."\" />";
		}
		print "\n</form>";
		
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
		throw new UnimplementedException();
	}
	
}

?>