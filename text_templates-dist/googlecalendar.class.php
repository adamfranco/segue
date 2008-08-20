<?php
/**
 * @since 8/19/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * Embeds a google-calendar in a text-block
 * 
 * @since 8/19/08
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_googlecalendar
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
	<h4>googlecalendar</h4>
	<p>This template will insert an embedded google calendar. It is easiest to copy paste the embed code from <a href='http://www.google.com/calendar/' target='_blank'>GoogleCalendar</a> rather than manually writing the template markup.</p>
	<h4>Parameters:</h4>
	<dl>
		<dt><strong>id</strong></dt>
		<dd>The id of a calendar usually an email address sort of thing. You can specify multiple id parameters. Example: gqq3gqfr4jvo79o1b3e2000fh8@group.calendar.google.com</dd>
		<dt><strong>color</strong></dt>
		<dd>The color of a calendar. You can specify multiple color parameters, one for each calendar id. Example: #ff0044</dd>
		<dt><strong>title</strong></dt>
		<dd>The title to display for the embeded calendar (Optional)</dd>
		<dt><strong>width</strong></dt>
		<dd>The integer width of the calendar in pixels. (Optional) Example: 325</dd>
		<dt><strong>height</strong></dt>
		<dd>The integer height of the calendar in pixels. (Optional) Example: 250</dd>
		<dt><strong>mode</strong></dt>
		<dd>The mode of the calendar - WEEK, MONTH, or AGENDA (Optional) Example: MONTH</dd>
		<dt><strong>show_nav</strong></dt>
		<dd>Show or hide the navigation buttons at the top of the calendar - 0 or 1 - default is 1 (Optional)</dd>
		<dt><strong>show_title</strong></dt>
		<dd>Show or hide the title at the top of the calendar - 0 or 1 - default is 1 (Optional)</dd>
		<dt><strong>show_date</strong></dt>
		<dd>Show or hide the date text at the top of the calendar - 0 or 1 - default is 1 (Optional)</dd>
		<dt><strong>show_tabs</strong></dt>
		<dd>Show or hide the tabs to week, month, or agenda views at the top of the calendar - 0 or 1 - default is 1 (Optional)</dd>
		<dt><strong>show_calendars</strong></dt>
		<dd>Show or hide the calendar list at the top of the calendar - 0 or 1 - default is 1 (Optional)</dd>
		<dt><strong>bg_color</strong></dt>
		<dd>Background Color - default is white #ffffff (Optional) Example #a3902d</dd>
		<dt><strong>week_start</strong></dt>
		<dd>Day of the week to start with - 1 (Sunday), 2 (Monday), or 7 (Saturday) default is 1 (Optional) Example: 2</dd>
		<dt><strong>time_zone</strong></dt>
		<dd>The time-zone to use default is empty (Optional) Example: America/New_York</dd>
	</dl>
	<h4>Example Usage:</h4>
	<ul>
		<li>{{googlecalendar|title=My Calendar Title|bg_color=#FFFFFF|week_start=1|time_zone=America/New_York|color=#528800|id=gqq3gqfr4jvo79o1b3e2000fh8@group.calendar.google.com|height=400}}</li>
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
				
		// Validate our options
		if (!is_array($paramList['id'])) 
			$paramList['id'] = array($paramList['id']);
		
		if (!isset($paramList['color']))
			$paramList['color'] = array();
		if (!is_array($paramList['color'])) 
			$paramList['color'] = array($paramList['color']);
		
		$validRegexes = array(
					'title' 		=> '/^.+$/i',
					'mode'			=> '/^(WEEK|AGENDA|MONTH)?$/i',
					'show_nav'		=> '/^(0|1)$/',
					'show_title'	=> '/^(0|1)$/',
					'show_date'		=> '/^(0|1)$/',
					'show_tabs'		=> '/^(0|1)$/',
					'show_calendars'	=> '/^(0|1)$/',
// 					'color'			=> '/^#[a-f0-9]{3,6}$/i',
					'bg_color'		=> '/^#[a-f0-9]{3,6}$/i',
					'week_start'	=> '/^[1-7]$/',
					'lang'			=> '/^[a-z_]{2,6}$/i',
// 					'id'			=> '/^[a-z0-9_@%\.-]+$/i',
					'time_zone'		=> '/^[a-z0-9_%\.-]$/i',
					'width'			=> '/^[0-9]+$/',
					'height'		=> '/^[0-9]+$/'
				);
		
		$defaults = array(
					'title' 		=> '',
					'mode'			=> 'MONTH',
					'show_nav'		=> '1',
					'show_title'	=> '1',
					'show_date'		=> '1',
					'show_tabs'		=> '1',
					'show_calendars'	=> '1',
// 					'color'			=> '#2952A3',
					'bg_color'		=> '#ffffff',
					'week_start'	=> '1',
					'lang'			=> 'en',
// 					'id'			=> 'src',
					'time_zone'		=> '',
					'width'			=> 425,
					'height'		=> 350
				);
		
		foreach ($defaults as $key => $val) {
			// default width
			if (!isset($paramList[$key]) || !preg_match($validRegexes[$key], $paramList[$key])) {
				$paramList[$key] = $val;
			}
		}
		
		// Ensure that no enties are in the title. These sometimes get added by the FCKEditor.
		$paramList['title'] = html_entity_decode($paramList['title'], ENT_QUOTES, 'UTF-8');
		
		// check Ids and colors.
		foreach ($paramList['id'] as $id) {
			if (!preg_match('/^[a-z0-9_@%\.-]+$/i', $id))
				throw new InvalidArgumentException("Invalid id, '$id'.");
		}
		foreach ($paramList['color'] as $color) {
			if (!preg_match('/^#[a-f0-9]{3,6}$/i', $color))
				throw new InvalidArgumentException("Invalid color, '$color'.");
		}
		
		
		// print out the form and return it.
		$urlParams = array(
					'title' 		=> 'title',
					'mode'			=> 'mode',
					'show_nav'		=> 'showNav',
					'show_title'	=> 'showTitle',
					'show_date'		=> 'showDate',
					'show_tabs'		=> 'showTabs',
					'show_calendars'	=> 'showCalendars',
					'color'			=> 'color',
					'bg_color'		=> 'bgcolor',
					'week_start'	=> 'wkst',
					'lang'			=> 'hl',
					'id'			=> 'src',
					'time_zone'		=> 'ctz',
					'width'			=> 'width',
					'height'		=> 'height'
				);
		$outputParams = array();
		foreach ($urlParams as $localName => $remoteName)
			$outputParams[$remoteName] = $paramList[$localName];
		
		$queryString = http_build_query($outputParams, '', '&amp;');
		// Fix up any array values.
		$queryString = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $queryString);
		$url = "http://www.google.com/calendar/embed?".$queryString;
		
		return "\n".'<iframe width="'.$paramList['width'].'" height="'.$paramList['height'].'" frameborder="0" style="border-width: 0"  scrolling="no" marginheight="0" marginwidth="0" src="'.$url.'"></iframe>';
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
<iframe

[^<>]*		# Other stuff

src=[\'"]
	(http:\/\/www\.google\.com\/calendar\/embed\?[a-z0-9_\.\/?&=,;:%+~-]+)
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
				$url = $matches[1][$i];
				
				$urlParams = array(
					'title' 		=> 'title',
					'mode'			=> 'mode',
					'show_nav'		=> 'showNav',
					'show_title'	=> 'showTitle',
					'show_date'		=> 'showDate',
					'show_tabs'		=> 'showTabs',
					'show_calendars'	=> 'showCalendars',
					'bg_color'		=> 'bgcolor',
					'week_start'	=> 'wkst',
					'lang'			=> 'hl',
					'time_zone'		=> 'ctz',
					'color'			=> 'color',
					'id'			=> 'src'
				);
				
				foreach ($urlParams as $localName => $paramName) {
					try {
						$matchParams[$localName] = $this->getParamFromUrl($paramName, $url);
					} catch (Exception $e) {}
				}
				
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