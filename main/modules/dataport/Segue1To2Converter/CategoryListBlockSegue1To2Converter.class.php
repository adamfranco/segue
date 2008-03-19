<?php
/**
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CategoryListBlockSegue1To2Converter.class.php,v 1.1 2008/03/19 17:02:03 adamfranco Exp $
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
 * @version $Id: CategoryListBlockSegue1To2Converter.class.php,v 1.1 2008/03/19 17:02:03 adamfranco Exp $
 */
class CategoryListBlockSegue1To2Converter
	extends TextBlockSegue1To2Converter
{
	
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
		$currentVersion = $this->doc->createElement('currentVersion');
		$version = $currentVersion->appendChild($this->doc->createElement('version'));
		
		ob_start();
		print _("The Category List is not yet supported in Segue 2. This content block is just a placeholder and can be deleted. Add a new Category List when it becomes available.");
		
		$version->appendChild($this->createCDATAElement('content', 
		ob_get_clean()));
		$version->appendChild($this->doc->createElement('abstractLength', 0));
		
		return $currentVersion;
	}
	
}

?>