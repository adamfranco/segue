<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TextBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/BlockSegue1To2Converter.abstract.php");

/**
 * A converter for text blocks
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TextBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class TextBlockSegue1To2Converter
	extends BlockSegue1To2Converter
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
	 * Answer a description element for this Block.
	 * 
	 * @param object DOMElement $mediaElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getDescriptionElement (DOMElement $mediaElement) {
		try {
			$shortTextElement = $this->getSingleSourceElement('./shorttext', $this->sourceElement);
		}
		// Page content and comments have their text in the 'text' node instead of shorttext
		catch (MissingNodeException $e) {
			$shortTextElement = $this->getSingleSourceElement('./text', $this->sourceElement);
		}
		
		$shortHtml = $this->getStringValue($shortTextElement);
		// Attach any media linked from the HTML
		$shortHtml = $this->attachMediaFromHtml($shortHtml, $mediaElement);
		$shortHtml = $this->rewriteLocalLinks($shortHtml);

		
		return $this->createCDATAElement('description', $this->trimHtml($shortHtml, 50));
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {
		try {
			$shortTextElement = $this->getSingleSourceElement('./shorttext', $this->sourceElement);
		}
		// Page content and comments have their text in the 'text' node instead of shorttext
		catch (MissingNodeException $e) {
			$shortTextElement = $this->getSingleSourceElement('./text', $this->sourceElement);
		}
		$shortHtml = $this->getStringValue($shortTextElement);
		
		$longTextElement = $this->sourceXPath->query('./longertext', $this->sourceElement)->item(0);
		if ($longTextElement)
			$longHtml = $this->getStringValue($longTextElement);
		else
			$longHtml = '';
		
		// Replace links to media files with new versions
		$shortHtml = $this->attachMediaFromHtml($shortHtml, $mediaElement);
		$shortHtml = $this->rewriteLocalLinks($shortHtml);
		$longHtml = $this->attachMediaFromHtml($longHtml, $mediaElement);
		$longHtml = $this->rewriteLocalLinks($longHtml);
		
		// Clean out any bad html
		$shortHtml = $this->cleanHtml($shortHtml);
		$longHtml = $this->cleanHtml($longHtml);
		
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		
		if (strlen(trim($longHtml))) {
			$version->appendChild($this->createCDATAElement('content', 
				$shortHtml."\n".$longHtml));
			$version->appendChild($this->doc->createElement('abstractLength', 
				strlen($shortHtml)));
		} else {
			$version->appendChild($this->createCDATAElement('content', 
				$shortHtml));
			$version->appendChild($this->doc->createElement('abstractLength',  0));
		}
		
		return $currentVersion;
	}
	
}

?>