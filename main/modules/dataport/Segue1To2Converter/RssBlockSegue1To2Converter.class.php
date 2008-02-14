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
		return $this->createPluginType('TextBlock');
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
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		$version->appendChild($this->createCDATAElement('content', 
			"<h4><a href='".$url."'>RSS Feed: ".$title."</a></h4>\n".$descHtml));
		$version->appendChild($this->doc->createElement('abstractLength', '0'));
		
		return $currentVersion;
	}
	
}

?>