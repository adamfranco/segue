<?php
/**
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadcrumbsBlockSegue1To2Converter.class.php,v 1.1 2008/03/21 20:28:37 adamfranco Exp $
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
 * @version $Id: BreadcrumbsBlockSegue1To2Converter.class.php,v 1.1 2008/03/21 20:28:37 adamfranco Exp $
 */
class BreadcrumbsBlockSegue1To2Converter
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
		return $this->createPluginType('Breadcrumbs');
	}
	
	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 3/19/08
	 */
	public function convert () {
		$element = parent::convert();
		
		$element->setAttribute('commentsEnabled', 'false');
		$element->setAttribute('showDisplayNames', 'false');
		$element->setAttribute('showHistory', 'false');
		$element->setAttribute('showDates', 'none');
		$element->setAttribute('showAttribution', 'none');
		
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
		return $this->createCDATAElement('description', '');
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
		$currentContent->appendChild($this->doc->createElement('content'));
		$currentContent->appendChild($this->doc->createElement('rawDescription'));
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