<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DownloadBlockSegue1To2Converter.class.php,v 1.3 2008/03/21 21:11:24 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Segue1To2Converter.abstract.php");

/**
 * A converter for text blocks
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DownloadBlockSegue1To2Converter.class.php,v 1.3 2008/03/21 21:11:24 adamfranco Exp $
 */
class DownloadBlockSegue1To2Converter
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
		return $this->createPluginType('Download');
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
		$descHtml = $this->getStringValue($descElement);
		$descHtml = $this->rewriteLocalLinks($descHtml);
		
		return $this->createCDATAElement('description', $this->trimHtml($descHtml, 50));
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
		$descHtml = $this->getStringValue($descElement);
		$descHtml = $this->rewriteLocalLinks($descHtml);
		
		// Content
		$filename = $this->getStringValue($this->getSingleSourceElement('./filename', $this->sourceElement));
		$currentContent = $this->doc->createElement('currentContent');
		
		try {
			$fileUrlString = $this->attachFile($filename, $mediaElement);
			$fileUrlString = str_replace('asset_id', 'assetId', $fileUrlString);
			$fileUrlString = str_replace('record_id', 'recordId', $fileUrlString);
			$fileUrlString = str_replace('&amp;', '&', $fileUrlString);
			$content = $currentContent->appendChild($this->createCDATAElement('content', $fileUrlString));
		}
		// If the HTML references a file that doesn't exist, just put a link
		// to a missing file action.
		catch (MissingNodeException $e) {
			$content = $currentContent->appendChild($this->createCDATAElement('content', ''));
		}
		// If the HTML references a file that doesn't exist, just put a link
		// to a missing file action.
		catch (Segue1To2_MissingFileException $e) {
			$content = $currentContent->appendChild($this->createCDATAElement('content', ''));
		}
		
		$rawDesc = $currentContent->appendChild($this->createCDATAElement('rawDescription',  $this->cleanHtml($descHtml)));
		
		return $currentContent;
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