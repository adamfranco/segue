<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ImageBlockSegue1To2Converter.class.php,v 1.3 2008/03/21 21:11:24 adamfranco Exp $
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
 * @version $Id: ImageBlockSegue1To2Converter.class.php,v 1.3 2008/03/21 21:11:24 adamfranco Exp $
 */
class ImageBlockSegue1To2Converter
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
		$filename = $this->getStringValue($this->getSingleSourceElement('./filename', $this->sourceElement));
		
		// Content
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		
		try {
			$fileUrlVal = $this->attachFile($filename, $mediaElement);
			$version->appendChild($this->createCDATAElement('content', 
			"<img src='[[fileurl:".$fileUrlVal."]]' alt=\"".$title."\"/>\n<br/>".$descHtml));
		}
		// If the HTML references a file that doesn't exist, just put a link
		// to a missing file action.
		catch (MissingNodeException $e) {
			$version->appendChild($this->createCDATAElement('content', 
			_("The image referenced was not found.")."\n<br/>".$descHtml));
		}
		// If the HTML references a file that doesn't exist, just put a link
		// to a missing file action.
		catch (Segue1To2_MissingFileException $e) {
			$version->appendChild($this->createCDATAElement('content', 
			_("The image referenced was not found.")."\n<br/>".$descHtml));
		}
		
		$version->appendChild($this->doc->createElement('abstractLength', '0'));
		
		return $currentVersion;
	}
	
}

?>