<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeadingBlockSegue1To2Converter.class.php,v 1.2 2008/03/19 17:02:03 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");

/**
 * A converter for text blocks
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HeadingBlockSegue1To2Converter.class.php,v 1.2 2008/03/19 17:02:03 adamfranco Exp $
 */
class HeadingBlockSegue1To2Converter
	extends TextBlockSegue1To2Converter
{
	
	/**
	 * Answer a description element for this Block.
	 * 
	 * @param object DOMElement $mediaElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getDescriptionElement (DOMElement $mediaElement) {
		return $this->createCDATAElement('description', '');
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {		
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		
		$version->appendChild($this->createCDATAElement('content', 
		"<h3>".$this->getDisplayName()."</h3>"));
		$version->appendChild($this->doc->createElement('abstractLength', 0));
		
		return $currentVersion;
	}
	
}

?>