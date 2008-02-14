<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PageNavBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
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
 * @version $Id: PageNavBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
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
		else
			$flowOrg->setAttribute('rows', $this->sourceElement->getAttribute('archiving'));
		
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