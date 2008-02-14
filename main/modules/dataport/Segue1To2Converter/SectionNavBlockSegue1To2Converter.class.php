<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SectionNavBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavBlockSegue1To2Converter.abstract.php");
require_once(dirname(__FILE__)."/PageNavBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/LinkBlockSegue1To2Converter.class.php");

/**
 * A converter for Section-level NavBlock
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SectionNavBlockSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class SectionNavBlockSegue1To2Converter
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
		return 'section_'.$idString;
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
		
		// For nested menus, we have already gotten the left content and only need
		// to get remaining content in the right.
		if ($this->useSideSections()) {
			$leftContent = null;
			$rightContent = $this->getPageContent('right');
			$menu = $this->getNestedMenu();
		} 
		// If the menu is on the right, separate all menu items and other right content
		// from other content on the left.
		else  if ($this->getPageMenuSide($this->sourceElement) == 'right') {
			$leftContent = $this->getPageContent('left');
			$rightContent = $this->getPageMenu('right');
			$menu = $rightContent;
		}
		// The default is for the menu to be on the left, separate all menu items
		// and other left content from other content on the right.
		else {
			$leftContent = $this->getPageMenu('left');
			$rightContent = $this->getPageContent('right');
			$menu = $leftContent;
		}
		
		
		// Create the cells
		$numCells = 0;
		if (!is_null($leftContent)) {
			$cell = $navOrg->appendChild($this->doc->createElement('cell'));
			$cell->appendChild($leftContent);
			$numCells++;
		}
		
		// Add a cell for the page target
		$cell = $navOrg->appendChild($this->doc->createElement('cell'));
		$numCells++;
		// Set the menu target
		$menu->setAttribute('target_id', $navOrg->getAttribute('id').'_cell:'.($numCells - 1));
		
		
		if (!is_null($rightContent)) {
			$cell = $navOrg->appendChild($this->doc->createElement('cell'));
			$cell->appendChild($rightContent);
			$numCells++;
		}
		
		$navOrg->setAttribute('rows', 1);
		$navOrg->setAttribute('cols', $numCells);
		
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
		// if the menu is nested, add the nested menu organizer
		if ($this->useSideSections()) {
			// Return a reference to the nested menu so that we can access it
			// multiple times as needed.
			if (!isset($this->nestedMenu))
				$this->nestedMenu = $this->getPageMenu('left');
			
			return $this->nestedMenu;
		} else
			return null;
	}
	
	/**
	 * Answer a page menu that would live on the left or right side of the page.
	 * 
	 * @param string $side 'left', 'right', or 'all_items'
	 * @return DOMElement
	 * @access private
	 * @since 2/6/08
	 */
	private function getPageMenu ($side = 'all_items') {
		if (!in_array($side, array('left', 'right', 'all_items')))
			throw new Exception("invalid side, '$side'.");
		
		$menuOrg = $this->doc->createElement("MenuOrganizer");
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($menuOrg);
		
		$menuOrg->setAttribute('id', $this->createId());
		$menuOrg->setAttribute('direction', 'Top-Bottom/Left-Right');
		
		// Note: Ignoring dividers for now.
		if ($side == 'all_items')
			$pages = $this->sourceXPath->query('./page | ./pageContent | ./navlink | ./heading | ./pageRSS', $this->sourceElement);
		else
			$pages = $this->sourceXPath->query("./page[@location = '$side'] | ./pageContent[@location = '$side'] | ./navlink[@location = '$side'] | ./heading[@location = '$side'] | ./pageRSS[@location = '$side']", $this->sourceElement);
		
		foreach ($pages as $page) {
			$cell = $menuOrg->appendChild($this->doc->createElement('cell'));
			switch ($page->nodeName) {
				case 'page':
					$converter = new PageNavBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'pageContent':
					$converter = new TextBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'navlink':
					$converter = new LinkBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'heading':
					$converter = new HeadingBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'pageRSS':
					$converter = new RssBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				default:
					throw new Exception("Unknown page type '".$page->nodeName."'.");
			}
			
			$cell->appendChild($converter->convert());
		}
		
		return $menuOrg;
	}
	
	/**
	 * Answer a page flow organizer with non-menu items that would live on the left or right side of the page.
	 * 
	 * @param string $side 'left', 'right', or 'all_items'
	 * @return mixed DOMElement or null if no items
	 * @access private
	 * @since 2/6/08
	 */
	private function getPageContent ($side = 'all_items') {
		$org = $this->doc->createElement("FlowOrganizer");
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($org);
		
		$org->setAttribute('id', $this->createId());
		$org->setAttribute('direction', 'Top-Bottom/Left-Right');
		$org->setAttribute('cols', '1');
		$org->setAttribute('rows', '0');
		
		// Note: Ignoring dividers for now.
		if ($side == 'all_items')
			$pages = $this->sourceXPath->query('./pageContent | ./navlink | ./heading', $this->sourceElement);
		else
			$pages = $this->sourceXPath->query("./pageContent[@location = '$side'] | ./navlink[@location = '$side'] | ./heading[@location = '$side']", $this->sourceElement);
		
		foreach ($pages as $page) {
			$cell = $org->appendChild($this->doc->createElement('cell'));
			switch ($page->nodeName) {
				case 'pageContent':
					$converter = new TextBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				default:
					throw new Exception("Unknown page type '".$page->nodeName."'.");
			}
			$cell->appendChild($converter->convert());
		}
		
		if ($this->xpath->query('./cell', $org)->length)
			return $org;
		else {
			$this->doc->documentElement->removeChild($org);
			return null;
		}
	}
	
}

?>