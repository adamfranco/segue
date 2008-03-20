<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavLinkBlockSegue1To2Converter.class.php,v 1.1 2008/03/20 14:14:25 adamfranco Exp $
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
 * @version $Id: NavLinkBlockSegue1To2Converter.class.php,v 1.1 2008/03/20 14:14:25 adamfranco Exp $
 */
class NavLinkBlockSegue1To2Converter
	extends LinkBlockSegue1To2Converter
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
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {
		// Content/Description
		$title = $this->getDisplayName();
		$url = $this->rewriteLocalLinks(
			$this->getStringValue($this->getSingleSourceElement('./url', $this->sourceElement)));
		
		// Content
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		$version->appendChild($this->createCDATAElement('content', 
			"<strong><a href='".$url."'>".$title."</a></strong>"));
		$version->appendChild($this->doc->createElement('abstractLength', '0'));
		
		return $currentVersion;
	}
	
}

?>