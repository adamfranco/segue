<?php
/**
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeadingBlockSegue1To2Converter.class.php,v 1.5 2008/03/20 14:14:24 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");

/**
 * A converter for text blocks
 * 
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeadingBlockSegue1To2Converter.class.php,v 1.5 2008/03/20 14:14:24 adamfranco Exp $
 */
class HeadingBlockSegue1To2Converter
	extends TextBlockSegue1To2Converter
{
	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 3/19/08
	 */
	public function convert () {
		$element = parent::convert();
		
		$element->setAttribute('showDisplayNames', 'false');
		$element->setAttribute('commentsEnabled', 'false');
		$element->setAttribute('showHistory', 'false');
		
		return $element;
	}
	
	/**
	 * Answer a description element for this Block.
	 * 
	 * @param object DOMElement $mediaElement
	 * @return object DOMElement
	 * @access protected
	 * @since 3/19/08
	 */
	protected function getDescriptionElement (DOMElement $mediaElement) {
		return $this->createCDATAElement('description', 'A heading between items.');
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 3/19/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {		
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		
		$version->appendChild($this->createCDATAElement('content', 
"<div style='font-weight: bold; font-size: larger;'>".$this->getDisplayName()."</div>"));
		$version->appendChild($this->doc->createElement('abstractLength', 0));
		
		return $currentVersion;
	}
	
}

?>