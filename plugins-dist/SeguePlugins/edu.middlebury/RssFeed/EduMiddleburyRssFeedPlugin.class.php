<?php
/**
 * @since 6/17/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * The RSS Feed plugin displays an RSS feed in-line in a site.
 * 
 * @since 6/17/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class EduMiddleburyRssFeedPlugin
	extends SegueAjaxPlugin
// 	extends SeguePlugin
	implements SeguePluginsAPI
{
		
/*********************************************************
 * Instance Methods - API - Override in Children
 *
 * Override these methods to implement the functionality of
 * a plugin.
 *********************************************************/
 	
 	/**
 	 * Answer a description of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	public static function getPluginDescription () {
 		return _("The RSS Feed Display plugin displays an RSS feed in-line in the site.");
 	}
 	
 	/**
 	 * Answer a display name for the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginDisplayName () {
 		return _("RSS Feed Display");
 	}
 	
 	/**
 	 * Answer an array of the creators of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return array of strings
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginCreators () {
 		return array("Adam Franco");
 	}
 	
 	/**
 	 * Answer the version of the plugin.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersion () {
 		return '0.1';
 	}
 	
 	/**
 	 * Answer the latest version of the plugin available. Null if no version information
 	 * is available.
 	 * 
 	 * @return mixed a string or null
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersionAvailable (){
 		return null;
 	}
 	
 	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.  This is where you would make more complex data that your 
 	 * plugin needs.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function initialize () {
 		$this->editing = false;
 		$this->doc = new Harmoni_DOMDocument;
 		$this->doc->preserveWhiteSpace = false;
 		if (strlen($this->getContent()))
	 		$this->doc->loadXML($this->getContent());
	 	else
	 		$this->doc->loadXML("<RssFeedPlugin></RssFeedPlugin>");
 		$this->xpath = new DOMXPath($this->doc);
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function update ( $request ) {
 		if ($this->getFieldValue('edit'))
	 		$this->editing = true;
	 	
 		if ($this->getFieldValue('submit_pressed')) {
 			$url = $this->getFieldValue('feed_url');
 			if (!preg_match('/^(http|https):\/\/[a-zA-Z0-9_.-]+(:[0-9]+)?(\/[a-zA-Z0-9_.,?%+=\/-]*)/i', $url))
 				$url = '';
 			
 			$this->_setFeedUrl($url);
 			
 			if ($this->getFieldValue('show_channel_titles') == 'true')
	 			$this->_setShowChannelTitles(true);
	 		else
	 			$this->_setShowChannelTitles(false);
	 			
	 		if ($this->getFieldValue('show_channel_descriptions') == 'true')
	 			$this->_setShowChannelDescriptions(true);
	 		else
	 			$this->_setShowChannelDescriptions(false);
	 		
	 		if ($this->getFieldValue('show_channel_divider') == 'true')
	 			$this->_setShowChannelDivider(true);
	 		else
	 			$this->_setShowChannelDivider(false);
	 			
	 		if ($this->getFieldValue('show_item_titles') == 'true')
	 			$this->_setShowItemTitles(true);
	 		else
	 			$this->_setShowItemTitles(false);
	 			
	 		if ($this->getFieldValue('show_item_descriptions') == 'true')
	 			$this->_setShowItemDescriptions(true);
	 		else
	 			$this->_setShowItemDescriptions(false);
	 		
	 		if ($this->getFieldValue('show_item_divider') == 'true')
	 			$this->_setShowItemDivider(true);
	 		else
	 			$this->_setShowItemDivider(false);
	 		
	 		if ($this->getFieldValue('show_attribution') == 'true')
	 			$this->_setShowAttribution(true);
	 		else
	 			$this->_setShowAttribution(false);
	 		
	 		if ($this->getFieldValue('show_dates') == 'true')
	 			$this->_setShowDates(true);
	 		else
	 			$this->_setShowDates(false);
	 		
	 		if ($this->getFieldValue('show_comment_links') == 'true')
	 			$this->_setShowCommentLinks(true);
	 		else
	 			$this->_setShowCommentLinks(false);
	 		
	 		$this->_setMaxItems(intval($this->getFieldValue('max_items')));
	 		$this->_setExtendedMaxItems(intval($this->getFieldValue('extended_max_items')));
	 		
 		}
 	}
 	
 	/**
 	 * Print out the editing form
 	 * 
 	 * @return void
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function printEditForm () {
 		print $this->formStartTagWithAction();
 		print _("Feed URL: ");
 		
 		print "\n\t<input type='text' name='".$this->getFieldName('feed_url')."' size='30' value=\"";
 		if ($this->_getFeedUrl())
 			print $this->_getFeedUrl();
 		else
 			print "http://";
 		print "\" onchange='if(!RssFeedReader.validateUrl(this.value)) {alert(\"Feed URL is not valid.\"); this.focus()}'/>";
 		
 		print "\n\t<table width='100%' border='0'><tr><td>";
 		
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_channel_titles')."' value='true' ".(($this->_showChannelTitles())?'checked="checked"':'')."/> ";
 		print _("Show Channel Titles?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_channel_descriptions')."' value='true' ".(($this->_showChannelDescriptions())?'checked="checked"':'')."/> ";
 		print _("Show Channel Descriptions?");
 		
 		print "\n\t<br/>";
		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_channel_divider')."' value='true' ".(($this->_showChannelDivider())?'checked="checked"':'')."/> ";
 		print _("Show Channel Divider?");
 		
 		print "\n\t</td><td>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_item_titles')."' value='true' ".(($this->_showItemTitles())?'checked="checked"':'')."/> ";
 		print _("Show Item Titles?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_item_descriptions')."' value='true' ".(($this->_showItemDescriptions())?'checked="checked"':'')."/> ";
 		print _("Show Item Descriptions?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_item_divider')."' value='true' ".(($this->_showItemDivider())?'checked="checked"':'')."/> ";
 		print _("Show Item Divider?");
 		
 		print "\n\t</td><td>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_attribution')."' value='true' ".(($this->_showAttribution())?'checked="checked"':'')."/> ";
 		print _("Show Attribution?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_dates')."' value='true' ".(($this->_showDates())?'checked="checked"':'')."/> ";
 		print _("Show Dates?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_comment_links')."' value='true' ".(($this->_showCommentLinks())?'checked="checked"':'')."/> ";
 		print _("Show Comment Links?");
 		
 		print "\n\t</td></tr></table>\n";
 		print _("Maximum number Items to show:")." ";
 		print "\n\t<input type='text' name='".$this->getFieldName('max_items')."' value='";
 		if ($this->_getMaxItems())
 			print $this->_getMaxItems();
 		print "' size='4' /> ";
 		print " <em>("._('clear to show all').")</em>";
 		
 		print "\n\t<br/>";
 		print _("Maximum number Items to show in detail view:")." ";
 		print "\n\t<input type='text' name='".$this->getFieldName('extended_max_items')."' value='";
 		if ($this->_getExtendedMaxItems())
 			print $this->_getExtendedMaxItems();
 		print "' size='4' /> ";
 		print " <em>("._('clear to show all').")</em>";
 		
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='submit' name='".$this->getFieldName('submit_pressed')."' value='"._("Submit")."'/>";
 		print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";
 		print "\n</form>";
 	}
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function getMarkup () {
 		return $this->_getMarkup($this->_getMaxItems());
 	}
 	
 	/**
 	 * Answer the markup for a number of items
 	 * 
 	 * @param int $numItems
 	 * @return string
 	 * @access protected
 	 * @since 7/8/08
 	 */
 	protected function _getMarkup ($numItems) {
 		// Add our js libraries to the document <head>
		$this->addHeadJavascript('RssFeedReader.js');
		$this->addHeadCss('RssFeedReader.css');
 		ob_start();
 		
 		if ($this->editing && $this->canModify()) {
			$this->printEditForm();
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div style='text-align: right; white-space: nowrap; float: right;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
 			
 			if ($this->hasContent()) {
 				
 				// Print our placeholder
 				$id = __CLASS__."_".$this->getId()."_feed";
				print "\n<div id='$id'></div>";
				
				// Initialize display
				print "\n<script type='text/javascript'>";
 				print "\n// <![CDATA[";
 				print "
 	var container = document.get_element_by_id('$id');
 	var reader = new RssFeedReader('".$this->_getFeedAccessUrl()."',
 				{loadingImage: '".$this->getPublicFileUrl('loading.gif')."'";
 				if ($this->_showChannelTitles())
 					print ",\n\t\t\t\tshowChannelTitles: true";
 				if ($this->_showChannelDescriptions())
 					print ",\n\t\t\t\tshowChannelDescriptions: true";
 				if ($this->_showChannelDivider())
 					print ",\n\t\t\t\tshowChannelDivider: true";
 				if ($this->_showItemTitles())
 					print ",\n\t\t\t\tshowItemTitles: true";
				if ($this->_showItemDescriptions())
 					print ",\n\t\t\t\tshowItemDescriptions: true";
 				if ($this->_showItemDivider())
 					print ",\n\t\t\t\tshowItemDivider: true";
 				if ($this->_showAttribution())
 					print ",\n\t\t\t\tshowAttribution: true";
 				if ($this->_showDates())
 					print ",\n\t\t\t\tshowDates: true";
 				if ($this->_showCommentLinks())
 					print ",\n\t\t\t\tshowCommentLinks: true";
 				
 				print ",\n\t\t\t\tmaxItems: ".$numItems;
 				print "}";
 				if ($this->_getFeedAlternateUrl())
 					print ",\n\t\t\t\t'".$this->_getFeedAlternateUrl()."'";
 				print ");
 	reader.displayIn(container);
 	
 ";
 				print "\n// ]]>";
 				print "\n</script>";
				
				
			} else {
				print "\n<div class='plugin_empty'>";
				print _("No feed has been chosen yet. ");
				if ($this->shouldShowControls()) {
					print "<br/>"._("Click the 'edit' link to enter a feed url. ");
				}
				print "</div>";
			}
		 	
	 		
	 		if ($this->shouldShowControls()) {
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
				
 		}
 		
//  		printpre(htmlentities($this->getContent()));
 		return ob_get_clean();
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedMarkup () {
 		return $this->_getMarkup($this->_getExtendedMaxItems());
 	}
 	
 	/**
 	 * Answer the label to use when linking to the plugin's extented markup.
 	 * For a text-based plugin this may be the default, 'read more >>', for
 	 * an image plugin it might be something like "Large View", etc.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
//  	public function getExtendedLinkLabel ();
 	
 	/**
 	 * Generate a plain-text or HTML description string for the plugin instance.
 	 * This may simply be a stored 'raw description' string, it could be generated
 	 * from other content in the plugin instance, or some combination there-of.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/22/07
 	 */
 	public function generateDescription () {
 		return str_replace('%1', $this->_getFeedUrl(), _("A display of an RSS feed: %1"));
 	}
 	
 	/**
 	 * Answer true if this instance of a plugin 'has content'. This method is called
 	 * to determine if the plugin instance is ready to be 'published' or is a newly-created
 	 * placeholder awaiting content addition. If the plugin has no appreciable 
 	 * difference between have content or not, this method should return true. For
 	 * example: an interactive calendar plugin should probably be 'published' 
 	 * whether or not events have been added to it.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 7/13/07
 	 */
 	public function hasContent () {
 		if ($this->_getFeedUrl())
 			return true;
 		return false;
 	}
 	
 	/**
 	 * Answer the url the JS functions should use to get the feed.
 	 * Due to browser restrictions on the location of documents loaded via the
 	 * XMLHTTPRequest, this may be different from the url returned by _getFeedUrl();
 	 * 
 	 * @return string
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _getFeedAccessUrl () {
 		// For local-server urls, return the feed url
 		if ($this->isLocal($this->_getFeedUrl()) 
 				&& $this->isContentTrusted($this->_getFeedUrl()))
	 		return $this->_getFeedUrl();
 		
 		// For remote urls, pass through a local data-fetching gateway.
 		else
	 		return $this->getPluginActionUrl('remote_feed', 
 				array('url' => $this->_getFeedUrl()));
 	}
 	
 	/**
 	 * Answer an alternate url for accessing the feed that pipes it through
 	 * our remote-access scripts to handle conversion of Atom feeds on the local
 	 * server.
 	 * 
 	 * @return string
 	 * @access protected
 	 * @since 7/7/08
 	 */
 	protected function _getFeedAlternateUrl () {
 		return $this->getPluginActionUrl('remote_feed', 
 				array('url' => $this->_getFeedUrl()));
 	}
 	/**
 	 * Answer true if the feed is local to this server and can be accessed via
 	 * XMLHTTPRequest.
 	 * 
 	 * @param string $url
 	 * @return boolean
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function isLocal ($url) {
 		if (!preg_match('/^[a-z]{3,6}:\/\/([a-zA-Z0-9_.-]+)(:\/)?/i', $url, $matches))
 			throw new Exception("Invalid URL syntax: $url");
 		$host = $matches[1];
 		if (strtolower($host) == strtolower($_SERVER['HTTP_HOST'])) 
	 		return true;
	 	else
	 		return false;
 	}
 	
 	/**
 	 * Answer true if the feed source is trusted to provide safe content. Generally
 	 * this will only be Segue-Generated feeds.
 	 * 
 	 * @param string $url
 	 * @return boolean
 	 * @access protected
 	 * @since 7/7/08
 	 */
 	protected function isContentTrusted ($url) {
 		$trustedActions = array(
 			array('module' => 'rss', 'action' => 'comments'),
 			array('module' => 'rss', 'action' => 'content'),
 			array('module' => 'logs', 'action' => 'browse_rss')
 		);
 		$trustedUrls = array();
 		
 		// Check allowed list.
 		if (in_array($url, $trustedUrls))
 			return true;
 		
 		// Build allowed urls and check against them.
 		$harmoni = Harmoni::instance();
 		foreach ($trustedActions as $pair) {
 			if (strpos($url, $harmoni->request->quickURL($pair['module'], $pair['action'])) === 0)
 				return true;
 		}
 		
 		return false;
 	}
 	
 	/**
 	 * Answer the feed url
 	 * 
 	 * @return string or null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _getFeedUrl () {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed/Url');
 		if (!$elements->length)
 			return null;
 		
 		$url = $elements->item(0)->nodeValue;
 		if (strlen($url)) {
 			return $this->untokenizeLocalUrls($url);
 		}
 		return null;
 	}
 	
 	/**
 	 * Set the feed url
 	 * 
 	 * @param string $url
 	 * @return null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _setFeedUrl ($url) {
 		// Tokenize local URLs for portability when moving across servers and importing/exporting.
 		$url = $this->tokenizeLocalUrls($url);
 		
 		// Reencode ampersands for XML
 		$url = str_replace('&', '&amp;', $url);
 		
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$urlElements = $this->xpath->query('./Url', $feedElement);
 		if ($urlElements->length) {
 			$urlElement = $urlElements->item(0);
 			$urlElement->nodeValue = $url;
 		} else
 			$feedElement->appendChild($this->doc->createElement('Url', $url));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer true if the titles of channels in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showChannelTitles () {
 		return $this->_getBoolean('showChannelTitles');
 	}
 	
 	/**
 	 * Set the feed url
 	 * 
 	 * @param boolean $showTitles
 	 * @return null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _setShowChannelTitles ($showTitles) {
 		$this->_setBoolean('showChannelTitles', $showTitles);
 	}
 	
 	/**
 	 * Answer true if the Descriptions of channels in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showChannelDescriptions () {
 		return $this->_getBoolean('showChannelDescriptions');
 	}
 	
 	/**
 	 * Set the feed url
 	 * 
 	 * @param boolean $showDescriptions
 	 * @return null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _setShowChannelDescriptions ($showDescriptions) {
 		$this->_setBoolean('showChannelDescriptions', $showDescriptions);
 	}
 	
 	/**
 	 * Answer true if the Channels in the feed should have a diver between them
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _showChannelDivider () {
 		return $this->_getBoolean('showChannelDivider', false);
 	}
 	
 	/**
 	 * Set the value of the option to show Channel dividers
 	 * 
 	 * @param boolean $showChannelDivider
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setShowChannelDivider ($showChannelDivider) {
 		$this->_setBoolean('showChannelDivider', $showChannelDivider);
 	}
 	
 	/**
 	 * Answer true if the titles of Items in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showItemTitles () {
 		return $this->_getBoolean('showItemTitles');
 	}
 	
 	/**
 	 * Set the feed url
 	 * 
 	 * @param boolean $showTitles
 	 * @return null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _setShowItemTitles ($showTitles) {
 		$this->_setBoolean('showItemTitles', $showTitles);
 	}
 	
 	/**
 	 * Answer true if the Descriptions of Items in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showItemDescriptions () {
 		return $this->_getBoolean('showItemDescriptions');
 	}
 	
 	/**
 	 * Set the feed url
 	 * 
 	 * @param boolean $showDescriptions
 	 * @return null
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _setShowItemDescriptions ($showDescriptions) {
 		$this->_setBoolean('showItemDescriptions', $showDescriptions);
 	}
 	
 	/**
 	 * Answer true if the Items in the feed should have a diver between them
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _showItemDivider () {
 		return $this->_getBoolean('showItemDivider');
 	}
 	
 	/**
 	 * Set the value of the option to show item dividers
 	 * 
 	 * @param boolean $showItemDivider
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setShowItemDivider ($showItemDivider) {
 		$this->_setBoolean('showItemDivider', $showItemDivider);
 	}
 	
 	/**
 	 * Answer true if the author should be shown for Items in the feed.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _showAttribution () {
 		return $this->_getBoolean('showAttribution', false);
 	}
 	
 	/**
 	 * Set true if the author should be shown for Items in the feed.
 	 * 
 	 * @param boolean $showAttribution
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setShowAttribution ($showAttribution) {
 		$this->_setBoolean('showAttribution', $showAttribution);
 	}
 	
 	/**
 	 * Answer true if the dates should be shown for Items in the feed.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _showDates () {
 		return $this->_getBoolean('showDates', false);
 	}
 	
 	/**
 	 * Set true if the dates should be shown for Items in the feed.
 	 * 
 	 * @param boolean $showDates
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setShowDates ($showDates) {
 		$this->_setBoolean('showDates', $showDates);
 	}
 	
 	/**
 	 * Answer true if the comment links should be shown for Items in the feed.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _showCommentLinks () {
 		return $this->_getBoolean('showCommentLinks', false);
 	}
 	
 	/**
 	 * Set true if the comment links should be shown for Items in the feed.
 	 * 
 	 * @param boolean $showCommentLinks
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setShowCommentLinks ($showCommentLinks) {
 		$this->_setBoolean('showCommentLinks', $showCommentLinks);
 	}
 	
 	/**
 	 * Answer the maximum number of Items in the feed.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _getMaxItems () {
 		return $this->_getInt('maxItems', 0);
 	}
 	
 	/**
 	 * Set the maximum number of Items in the feed. Set 0 for unlimited.
 	 * 
 	 * @param boolean $maxItems
 	 * @return null
 	 * @access protected
 	 * @since 6/18/08
 	 */
 	protected function _setMaxItems ($maxItems) {
 		$this->_setInt('maxItems', $maxItems);
 	}
 	
 	/**
 	 * Answer the maximum number of Items in the feed in detail view.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 7/8/08
 	 */
 	protected function _getExtendedMaxItems () {
 		return $this->_getInt('extendedMaxItems', 0);
 	}
 	
 	/**
 	 * Set the maximum number of Items in the feed in detail view. Set 0 for unlimited.
 	 * 
 	 * @param boolean $maxItems
 	 * @return null
 	 * @access protected
 	 * @since 7/8/08
 	 */
 	protected function _setExtendedMaxItems ($maxItems) {
 		$this->_setInt('extendedMaxItems', $maxItems);
 	}
 	
 	/**
 	 * Answer a boolean option
 	 * 
 	 * @param string $name
 	 * @param optional $default
 	 * @return boolean
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _getBoolean ($name, $default = true) {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return $default;
 		
 		$elem = $elements->item(0);
 		if (!$elem->hasAttribute($name))
 			return $default;
 			
 		if ($elem->getAttribute($name) == 'false')
 			return false;
 		
 		return true;
 	}
 	
 	/**
 	 * Set a boolean option
 	 * 
 	 * @param string $name
 	 * @param boolean $value
 	 * @return void
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _setBoolean ($name, $value) {
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute($name, (($value)?'true':'false'));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer a integer option
 	 * 
 	 * @param string $name
 	 * @param optional $default
 	 * @return int
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _getInt ($name, $default = 0) {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return $default;
 		
 		$elem = $elements->item(0);
 		if (!$elem->hasAttribute($name))
 			return $default;
 			
 		return intval($elem->getAttribute($name));
 	}
 	
 	/**
 	 * Set an integer option
 	 * 
 	 * @param string $name
 	 * @param int $value
 	 * @return void
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _setInt ($name, $value) {
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute($name, strval(intval($value)));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/*********************************************************
 	 * The following three methods allow plugins to work within
 	 * the "Segue Classic" user interface.
 	 *
 	 * If plugins do not support the wizard directly, then their
 	 * markup with 'show controls' enabled will be put directly 
 	 * in the wizard.
 	 *********************************************************/
//  	/**
//  	 * Answer true if this plugin natively supports editing via wizard components.
//  	 * Override to return true if you implement the getWizardComponent(), 
//  	 * and updateFromWizard() methods.
//  	 * 
//  	 * @return boolean
//  	 * @access public
//  	 * @since 5/9/07
//  	 */
//  	public function supportsWizard ();
//  	
//  	/**
//  	 * Return the a {@link WizardComponent} to allow editing of your
//  	 * plugin in the Wizard.
//  	 * 
//  	 * @return object WizardComponent
//  	 * @access public
//  	 * @since 5/8/07
//  	 */
//  	public function getWizardComponent ();
//  	
//  	/**
//  	 * Update the component from an array of values
//  	 * 
//  	 * @param array $values
//  	 * @return void
//  	 * @access public
//  	 * @since 5/8/07
//  	 */
//  	public function updateFromWizard ( $values );
 	
 	/*********************************************************
 	 * The following methods are needed to support restoring
 	 * from backups and importing/exporting plugin data.
 	 *********************************************************/
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings,
 	 * update any of the old Ids that this plugin instance recognizes to their
 	 * new value.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
 	public function replaceIds (array $idMap) {
 		$this->_setFeedUrl($this->replaceIdsInHtml($idMap, $this->_getFeedUrl()));
 	}
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings,
 	 * update any of the old Ids in the version XML to their new value.
 	 * The version DOMDocument should have its content updated in place.
 	 * This method is only needed if versioning is supported.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @param object DOMDocument $version
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
//  	public function replaceIdsInVersion (array $idMap, DOMDocument $version);
	
}

?>