<?php
/**
 * @since 7/9/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 
require_once(dirname(__FILE__)."/RssBlockSegue1To2Converter.class.php");

/**
 * This class overrides the default settings for page-level rss feeds
 * 
 * @since 7/9/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class PageRssBlockSegue1To2Converter
	extends RssBlockSegue1To2Converter
{
		
	/**
	 * Set the attributes for the feed
	 * 
	 * @param object DOMElement $sourceUrlElement
	 * @param object DOMElement $feed
	 * @return void
	 * @access protected
	 * @since 7/9/08
	 */
	protected function setAttributes (DOMElement $sourceUrlElement, DOMElement $feed) {
		// Set the max items to be the same
		if ($sourceUrlElement->hasAttribute('maxItems') 
			&& $sourceUrlElement->getAttribute('maxItems'))
		{
			$feed->setAttribute('maxItems', $sourceUrlElement->getAttribute('maxItems'));
			$feed->setAttribute('extendedMaxItems', $sourceUrlElement->getAttribute('maxItems'));
		}
		
		$feed->setAttribute('showChannelTitles', 'true');
		$feed->setAttribute('showChannelDescriptions', 'false');
		$feed->setAttribute('showChannelDivider', 'false');
		$feed->setAttribute('showItemTitles', 'true');
		$feed->setAttribute('showItemDescriptions', 'false');
		$feed->setAttribute('showItemDivider', 'false');
		$feed->setAttribute('showAttribution', 'false');
		$feed->setAttribute('showDates', 'false');
		$feed->setAttribute('showCommentLinks', 'false');
	}
	
}

?>