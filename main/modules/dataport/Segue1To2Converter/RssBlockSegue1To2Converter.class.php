<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/LinkBlockSegue1To2Converter.class.php");

/**
 * A converter for text blocks
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class RssBlockSegue1To2Converter
	extends LinkBlockSegue1To2Converter
{
	
	/**
	 * Answer a new Type DOMElement for this plugin
	 * 
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function createMyPluginType () {
		return $this->createPluginType('RssFeed');
	}
	
	/**
	 * Answer a description element for this Block
	 * 
	 * @param object DOMElement $mediaElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getDescriptionElement (DOMElement $mediaElement) {
		// Content/Description
		$descElement = $this->sourceXPath->query('./description', $this->sourceElement)->item(0);
		if ($descElement) {
			$descHtml = $this->getStringValue($descElement);
			$descHtml = $this->rewriteLocalLinks($descHtml);
			
			return $this->createCDATAElement('description', $this->trimHtml($descHtml, 50));
		} else {
			return $this->createCDATAElement('description', '');
		}
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {
		// Content/Description
		$descElement = $this->sourceXPath->query('./description', $this->sourceElement)->item(0);
		if ($descElement)
			$descHtml = $this->cleanHtml($this->getStringValue($descElement));
		else 
			$descHtml = '';
		$descHtml = $this->rewriteLocalLinks($descHtml);
		
		$title = $this->getDisplayName();
		$url =  $this->rewriteLocalLinks(
			$this->getStringValue($this->getSingleSourceElement('./url', $this->sourceElement)));
		
		// Content
		$dataDoc = new Harmoni_DOMDocument();
		$root = $dataDoc->appendChild($dataDoc->createElement('RssFeedPlugin'));
		$feed = $root->appendChild($dataDoc->createElement('RssFeed'));
		$urlElement = $feed->appendChild($dataDoc->createElement('Url', str_replace('&', '&amp;', $url)));
		
		$sourceUrlElement = $this->getSingleSourceElement('./url', $this->sourceElement);
		$this->setAttributes($sourceUrlElement, $feed);
		
		$currentContent = $this->doc->createElement('currentContent');
		$currentContent->appendChild($this->createCDATAElement('content', $dataDoc->saveXMLWithWhitespace()));
		$currentContent->appendChild($this->doc->createElement('rawDescription', ''));
		
		return $currentContent;
	}
	
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
		if ($sourceUrlElement->hasAttribute('maxItems') 
			&& $sourceUrlElement->getAttribute('maxItems'))
		{
			$feed->setAttribute('maxItems', $sourceUrlElement->getAttribute('maxItems'));
		}
		
		if ($sourceUrlElement->hasAttribute('extendedMaxItems') 
			&& $sourceUrlElement->getAttribute('extendedMaxItems'))
		{
			$feed->setAttribute('extendedMaxItems', $sourceUrlElement->getAttribute('extendedMaxItems'));
		}
		
		$feed->setAttribute('showChannelTitles', 'true');
		$feed->setAttribute('showChannelDescriptions', 'true');
		$feed->setAttribute('showChannelDivider', 'false');
		$feed->setAttribute('showItemTitles', 'true');
		$feed->setAttribute('showItemDescriptions', 'true');
		$feed->setAttribute('showItemDivider', 'true');
		$feed->setAttribute('showAttribution', 'true');
		$feed->setAttribute('showDates', 'true');
		$feed->setAttribute('showCommentLinks', 'true');
	}
	
	/**
	 * Answer a element that represents the history for this Block, null if not
	 * supported
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getHistoryElement (DOMElement $mediaElement) {
		// @todo Fill history support
	}
	
}

?>