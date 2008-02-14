<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavBlockSegue1To2Converter.abstract.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Segue1To2Converter.abstract.php");
require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");

/**
 * An abstract converter for content blocks.
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavBlockSegue1To2Converter.abstract.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
abstract class NavBlockSegue1To2Converter
	extends Segue1To2Converter
{

	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 2/12/08
	 */
	public function convert () {
		$element = $this->doc->createElement($this->getNodeName());
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($element);
		
		$this->addId($element);
		
		$element->appendChild($this->getDisplayNameElement());
		$element->appendChild($this->createCDATAElement('description', ''));
		
		$this->addRoles($element);
		
		$this->addCreationInfo($element);
		
		$this->addNavOrganizer($element);
		
		$nestedMenu = $this->getNestedMenu();
		if ($nestedMenu)
			$element->appendChild($nestedMenu);
		
		return $element;
	}
	
	/**
	 * Answer the appropriate nodeName for this item
	 * 
	 * @return string
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function getNodeName ();
	
	/**
	 * Add the NavOrganizer and children to the NavBlock
	 * 
	 * @param object DOMElement $navBlockElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function addNavOrganizer (DOMElement $navBlockElement);
	
	/**
	 * Answer a element that represents Any nested menues of this nav item. return
	 * null if none exits.
	 * 
	 * @return mixed object DOMElement or NULL
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function getNestedMenu ();
	
	/**
	 * Answer a text block element for an html string
	 * 
	 * @param string $html
	 * @param string $id
	 * @return DOMElement
	 * @access protected
	 * @since 2/5/08
	 */
	protected function createTextBlockForHtml ($html, $id = null, $displayName = 'Untitled') {
		// Create a placeholder element with the text to pass off to the TextBlock converter
		$sourceDoc = $this->sourceElement->ownerDocument;
		$sourceElement = $sourceDoc->createElement('story');
		
		$title = $sourceElement->appendChild($sourceDoc->createElement('title'));
		$title->appendChild($sourceDoc->createCDATASection($displayName));
		
		$text = $sourceElement->appendChild($sourceDoc->createElement('shorttext'));
		$text->appendChild($sourceDoc->createCDATASection($html));
		$text->setAttribute('text_type', 'html');
		
		$converter = new TextBlockSegue1To2Converter($sourceElement, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
				
		return $converter->convert();
	}
	
	/**
	 * Answer true if side-sections should be used, false otherwise.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 2/6/08
	 */
	protected function useSideSections () {
		return true;
	}
}

?>