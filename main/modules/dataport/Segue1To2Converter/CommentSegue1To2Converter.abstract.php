<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentSegue1To2Converter.abstract.php,v 1.3 2008/04/03 13:17:59 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Segue1To2Converter.abstract.php");

/**
 * An abstract converter for content blocks. Child classes for the various types
 * only need to implement a constructor to properly set the surrogate.
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentSegue1To2Converter.abstract.php,v 1.3 2008/04/03 13:17:59 adamfranco Exp $
 */
abstract class CommentSegue1To2Converter
	extends BlockSegue1To2Converter
{
	/**
	 * @var object BlockSegue1To2Converter $surrogate;  
	 * @access protected
	 * @since 2/12/08
	 */
	protected $surrogate;
	
	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 2/12/08
	 */
	public function convert () {
		$element = $this->doc->createElement('Comment');
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($element);
		
		$media = $this->doc->createElement('attachedMedia');
		
		$element->setAttribute('id', 'comment_'.$this->sourceElement->getAttribute('id'));
		$element->appendChild($this->createMyPluginType());
		$element->appendChild($this->createCDATAElement('subject', $this->cleanHtml($this->getDisplayName())));
// 		$element->appendChild($this->getDescriptionElement($media));
		
// 		$element->appendChild($this->doc->createElement('roles'));
		$this->addCreationInfo($element);
		
		$element->appendChild($this->getContentElement($media));
		
		$history = $this->getHistoryElement($media);
		if (!is_null($history))
			$element->appendChild($history);
			
		$element->appendChild($media);
		
		$comments = $element->appendChild($this->doc->createElement('replies'));
		
		// Comments
		$this->addComments($comments);
		
		return $element;
	}
	
	/**
	 * If necessary, re-write an Id to put it into a particular namespace, i.e. section_.
	 *
	 * Override this method in child classes as necessary.
	 * 
	 * @param string $idString
	 * @return string
	 * @access protected
	 * @since 2/13/08
	 */
	protected function getIdString ($idString) {
		return 'comment_'.$idString;
	}

	/**
	 * Add Comments to a comments element
	 * 
	 * @param object DOMElement $commentsElement
	 * @return void
	 * @access protected
	 * @since 2/11/08
	 */
	protected function addComments (DOMElement $commentsElement) {
		$comments = $this->sourceXPath->query('./discussion_node', $this->sourceElement);
		foreach ($comments as $comment) {
			$this->addComment($comment, $commentsElement);
		}
	}
	
	/**
	 * Answer a new Type DOMElement for this plugin
	 * 
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function createMyPluginType () {
		return $this->surrogate->createMyPluginType();
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
		return $this->surrogate->getDescriptionElement($mediaElement);
	}
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentElement (DOMElement $mediaElement) {
		return $this->surrogate->getContentElement($mediaElement);
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
		return $this->surrogate->getHistoryElement($mediaElement);
	}
	
}

?>