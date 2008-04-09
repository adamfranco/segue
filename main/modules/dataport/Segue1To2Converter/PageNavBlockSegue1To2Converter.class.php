<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PageNavBlockSegue1To2Converter.class.php,v 1.5 2008/04/09 16:19:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavBlockSegue1To2Converter.abstract.php");

/**
 * A converter for page nav blocks
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PageNavBlockSegue1To2Converter.class.php,v 1.5 2008/04/09 16:19:49 adamfranco Exp $
 */
class PageNavBlockSegue1To2Converter 
	extends NavBlockSegue1To2Converter
{
		
	/**
	 * Answer the appropriate nodeName for this item
	 * 
	 * @return string
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getNodeName () {
		return 'NavBlock';
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
		return 'page_'.$idString;
	}
	
	/**
	 * Add the comments enabled attribute if needed
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 3/19/08
	 */
	protected function setCommentsEnabled (DOMElement $element) {
		if ($this->pageCommentsEnabled() && !$this->sectionCommentsEnabled()) {
			$element->setAttribute('commentsEnabled', 'true');
		}
	}
	
	/**
	 * Answer true if all blocks in the page have comments enabled and that
	 * the commentsEnabled setting should be made on the page level or higher.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 3/19/08
	 */
	protected function pageCommentsEnabled () {
		$storyNodes = $this->sourceXPath->query('./story | ./file | ./link | ./rss | ./image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('./*/discussion', $this->sourceElement);
		if ($discussionNodes->length > 0 && $storyNodes->length == $discussionNodes->length)
			return true;
		else
			return false;
	}
	
	/**
	 * Answer true if all blocks in the section have comments enabled and that
	 * the commentsEnabled setting should be made on the section level or higher.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 3/19/08
	 */
	protected function sectionCommentsEnabled () {
		$storyNodes = $this->sourceXPath->query('../page/story | ../page/file | ../page/link | ../page/rss | ../page/image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('../page/*/discussion', $this->sourceElement);
		if ($storyNodes->length == $discussionNodes->length)
			return true;
		else
			return false;
	}
	
	/**
	 * Add the NavOrganizer and children to the NavBlock
	 * 
	 * @param object DOMElement $navBlockElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function addNavOrganizer (DOMElement $navBlockElement) {
		$navOrg = $navBlockElement->appendChild($this->doc->createElement("NavOrganizer"));
// 		
// 		// Temporarily append to the document element to enable searching
// 		$this->doc->documentElement->appendChild($navOrg);
		
		$navOrg->setAttribute('id', $this->createId());
		$navOrg->setAttribute('rows', 1);
		$navOrg->setAttribute('cols', 1);
		
		$cell = $navOrg->appendChild($this->doc->createElement('cell'));
		
		$flowOrg = $cell->appendChild($this->doc->createElement("FlowOrganizer"));
		$flowOrg->setAttribute('id', $this->createId());
		
		// Pagination
		if (!$this->sourceElement->hasAttribute('archiving') || $this->sourceElement->getAttribute('archiving') == 'none')
			$flowOrg->setAttribute('rows', 0);
		else {
			if (is_numeric($this->sourceElement->getAttribute('archiving')))
				$flowOrg->setAttribute('rows', intval($this->sourceElement->getAttribute('archiving')));
			else if ($this->sourceElement->getAttribute('archiving') == 'week')
				$flowOrg->setAttribute('rows', 7);
			else if ($this->sourceElement->getAttribute('archiving') == 'month')
				$flowOrg->setAttribute('rows', 30);
			else
				$flowOrg->setAttribute('rows', 0);
		}
		
		$flowOrg->setAttribute('cols', 1);
		
		// Order
		switch ($this->sourceElement->getAttribute('story_order')) {
			case 'titledesc':
				$flowOrg->setAttribute('sortMethod', 'title_desc');
				break;
			case 'titleasc':
				$flowOrg->setAttribute('sortMethod', 'title_asc');
				break;
			case 'addeddesc':
				$flowOrg->setAttribute('sortMethod', 'create_date_desc');
				break;
			case 'addedasc':
				$flowOrg->setAttribute('sortMethod', 'create_date_asc');
				break;
			case 'editeddesc':
				$flowOrg->setAttribute('sortMethod', 'mod_date_desc');
				break;
			case 'editedasc':
				$flowOrg->setAttribute('sortMethod', 'mod_date_asc');
				break;
			case 'author':
			case 'editor':
			case 'category':
			case 'custom':
// 				$flowOrg->setAttribute('sortMethod', 'custom');
// 				break;
			default:
				$flowOrg->setAttribute('sortMethod', 'default');
		}
		
		// Attribution
		if ($this->sourceElement->hasAttribute('show_creator') 
			&& $this->sourceElement->getAttribute('show_creator') == 'TRUE'
			&& $this->sourceElement->hasAttribute('show_editor') 
			&& $this->sourceElement->getAttribute('show_editor') == 'TRUE')
		{
			$attribution = 'both';
		} else if ($this->sourceElement->hasAttribute('show_creator') 
			&& $this->sourceElement->getAttribute('show_creator') == 'TRUE')
		{
			$attribution = 'creator';
		} else if ($this->sourceElement->hasAttribute('show_editor') 
			&& $this->sourceElement->getAttribute('show_editor') == 'TRUE')
		{
			$attribution = 'last_editor';
		} else {
			$attribution = 'default';
		}
			
		$flowOrg->setAttribute('showAttribution', $attribution);
		
		// Dates
		if ($this->sourceElement->hasAttribute('show_date') 
			&& $this->sourceElement->getAttribute('show_date') == 'TRUE')
		{
			$flowOrg->setAttribute('showDates', 'both');
		}
		
		// History Links
		if ($this->sourceElement->hasAttribute('show_versions') 
			&& $this->sourceElement->getAttribute('show_versions') == 'TRUE')
		{
			$flowOrg->setAttribute('showHistory', 'true');
		}
		
		// Add the cells and their stories to the flow organizer.
		$contentItems = $this->sourceXPath->query('./story | ./file | ./link | ./rss | ./image', $this->sourceElement);
		foreach ($contentItems as $item) {
			$cell = $flowOrg->appendChild($this->doc->createElement('cell'));
			$cell->appendChild($this->getContentBlock($item));
		}
		
		return $navOrg;
	}
	
	/**
	 * Answer a element that represents Any nested menues of this nav item. return
	 * null if none exits.
	 * 
	 * @return mixed object DOMElement or NULL
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getNestedMenu () {
		return null;
	}
	
}

?>