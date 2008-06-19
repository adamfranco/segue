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
 			if (!preg_match('/^https?:\/\/[a-z0-9]+/i', $url))
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
	 			
	 		if ($this->getFieldValue('show_item_titles') == 'true')
	 			$this->_setShowItemTitles(true);
	 		else
	 			$this->_setShowItemTitles(false);
	 			
	 		if ($this->getFieldValue('show_item_descriptions') == 'true')
	 			$this->_setShowItemDescriptions(true);
	 		else
	 			$this->_setShowItemDescriptions(false);
	 		
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
 		print "\"/>";
 		
 		print "\n\t<br/>";
 		
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_channel_titles')."' value='true' ".(($this->_showChannelTitles())?'checked="checked"':'')."/> ";
 		print _("Show Channel Titles?");
 		
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_channel_descriptions')."' value='true' ".(($this->_showChannelDescriptions())?'checked="checked"':'')."/> ";
 		print _("Show Channel Descriptions?");
 		
 		print "\n\t<br/>";
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_item_titles')."' value='true' ".(($this->_showItemTitles())?'checked="checked"':'')."/> ";
 		print _("Show Item Titles?");
 		
 		print "\n\t<input type='checkbox' name='".$this->getFieldName('show_item_descriptions')."' value='true' ".(($this->_showItemDescriptions())?'checked="checked"':'')."/> ";
 		print _("Show Item Descriptions?");
 		
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
 		ob_start();
 		
 		if ($this->editing && $this->canModify()) {
			$this->printEditForm();
 		} else if ($this->canView()) {
 			
 			if ($this->hasContent()) {
 				// Add our js libraries to the document <head>
 				$this->addHeadJavascript('RssFeedReader.js');
 				$this->addHeadCss('RssFeedReader.css');
 				
 				// Print our placeholder
 				$id = __CLASS__."_".$this->getId()."_feed";
				print "\n<div id='$id'></div>";
				
				// Initialize display
				print "\n<script type='text/javascript'>";
 				print "\n// <![CDATA[";
 				print "
 	var container = document.get_element_by_id('$id');
 	var reader = new RssFeedReader('".$this->_getFeedAccessUrl()."',
 						{loadingImage: '".$this->getPublicFileUrl('loading.gif')."'});
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
//  	public function getExtendedMarkup ();
 	
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
//  	public function generateDescription ();
 	
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
 		return $this->_getFeedUrl();
 		
 		// For remote urls, pass through a local data-fetching gateway.
 		// @todo
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
 		if (strlen($url))
 			return $url;
 			
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
 		// @todo Tokenize local URLs for portability when moving across servers and importing/exporting.
 		
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
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return true;
 		
 		$elem = $elements->item(0);
 		if ($elem->hasAttribute('showChannelTitles') && $elem->getAttribute('showChannelTitles') == 'false')
 			return false;
 		
 		return true;
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
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute('showChannelTitles', (($showTitles)?'true':'false'));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer true if the Descriptions of channels in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showChannelDescriptions () {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return true;
 		
 		$elem = $elements->item(0);
 		if ($elem->hasAttribute('showChannelDescriptions') && $elem->getAttribute('showChannelDescriptions') == 'false')
 			return false;
 		
 		return true;
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
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute('showChannelDescriptions', (($showDescriptions)?'true':'false'));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer true if the titles of Items in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showItemTitles () {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return true;
 		
 		$elem = $elements->item(0);
 		if ($elem->hasAttribute('showItemTitles') && $elem->getAttribute('showItemTitles') == 'false')
 			return false;
 		
 		return true;
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
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute('showItemTitles', (($showTitles)?'true':'false'));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer true if the Descriptions of Items in the feed should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 6/17/08
 	 */
 	protected function _showItemDescriptions () {
 		$elements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if (!$elements->length)
 			return true;
 		
 		$elem = $elements->item(0);
 		if ($elem->hasAttribute('showItemDescriptions') && $elem->getAttribute('showItemDescriptions') == 'false')
 			return false;
 		
 		return true;
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
 		$feedElements = $this->xpath->query('/RssFeedPlugin/RssFeed');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('RssFeed'));
 		
 		$feedElement->setAttribute('showItemDescriptions', (($showDescriptions)?'true':'false'));
 		
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
 	
 	
	
}

?>