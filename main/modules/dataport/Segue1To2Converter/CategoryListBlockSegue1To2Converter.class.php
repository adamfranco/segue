<?php
/**
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CategoryListBlockSegue1To2Converter.class.php,v 1.2 2008/04/17 19:39:21 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/BlockSegue1To2Converter.abstract.php");

/**
 * A converter for text blocks
 * 
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CategoryListBlockSegue1To2Converter.class.php,v 1.2 2008/04/17 19:39:21 achapin Exp $
 */
class CategoryListBlockSegue1To2Converter
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
		return $this->createPluginType('Tags');
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
		return $this->createCDATAElement('description', 'this is a tag cloud');
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 3/19/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {		
		$currentContent = $this->doc->createElement('currentContent');
		$content = $currentContent->appendChild($this->createCDATAElement('content', ''));
		$currentContent->appendChild($this->createCDATAElement('rawDescription',  ''));
		
		
		
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