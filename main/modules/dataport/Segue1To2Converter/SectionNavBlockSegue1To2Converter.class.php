<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SectionNavBlockSegue1To2Converter.class.php,v 1.7 2008/03/21 15:24:24 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavBlockSegue1To2Converter.abstract.php");
require_once(dirname(__FILE__)."/NavLinkBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/PageNavBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/LinkBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/HeadingBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/DividerBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/ParticipantListBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/CategoryListBlockSegue1To2Converter.class.php");

/**
 * A converter for Section-level NavBlock
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SectionNavBlockSegue1To2Converter.class.php,v 1.7 2008/03/21 15:24:24 adamfranco Exp $
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
	 * Add the comments enabled attribute if needed
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 3/19/08
	 */
	protected function setCommentsEnabled (DOMElement $element) {
		if ($this->sectionCommentsEnabled() && !$this->siteCommentsEnabled()) {
			$element->setAttribute('commentsEnabled', 'true');
		}
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
		$storyNodes = $this->sourceXPath->query('./page/story | ./page/file | ./page/link | ./page/rss | ./page/image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('./page/*/discussion', $this->sourceElement);
		if ($discussionNodes->length > 0 && $storyNodes->length == $discussionNodes->length)
			return true;
		else
			return false;
	}
	
	/**
	 * Answer true if all blocks in the site have comments enabled and that
	 * the commentsEnabled setting should be made on the site level or higher.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 3/19/08
	 */
	protected function siteCommentsEnabled () {
		$storyNodes = $this->sourceXPath->query('../section/page/story | ../section/page/file | ../section/page/link | ../section/page/rss | ../section/page/image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('../section/page/*/discussion', $this->sourceElement);
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
		
		// For nested menus, we have already gotten the left content and only need
		// to get remaining content in the right.
		if ($this->useSideSections()) {
			$leftContent = null;
			$rightContent = $this->getPageContent('right');
			if (!is_null($rightContent))
				$this->setNavigationWidth($rightContent);
			$menu = $this->getNestedMenu();
		} 
		// If the menu is on the right, separate all menu items and other right content
		// from other content on the left.
		else  if ($this->getPageMenuSide($this->sourceElement) == 'right') {
			$leftContent = $this->getPageContent('left');
			if (!is_null($leftContent))
				$this->setNavigationWidth($leftContent);
			$rightContent = $this->getPageMenu('right');
			if (!is_null($rightContent))
				$this->setNavigationWidth($rightContent);
			$menu = $rightContent;
		}
		// The default is for the menu to be on the left, separate all menu items
		// and other left content from other content on the right.
		else {
			$leftContent = $this->getPageMenu('left');
			if (!is_null($leftContent))
				$this->setNavigationWidth($leftContent);
			$rightContent = $this->getPageContent('right');
			if (!is_null($rightContent))
				$this->setNavigationWidth($rightContent);
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
			$pages = $this->sourceXPath->query('./page | ./pageContent | ./navlink | ./heading  | ./divider | ./pageRSS | ./participantList | ./categoryList', $this->sourceElement);
		else
			$pages = $this->sourceXPath->query("./page[@location = '$side'] | ./pageContent[@location = '$side'] | ./navlink[@location = '$side'] | ./heading[@location = '$side'] | ./divider[@location = '$side'] | ./pageRSS[@location = '$side'] | ./participantList[@location = '$side'] | ./categoryList[@location = '$side']", $this->sourceElement);
		
		foreach ($pages as $page) {
			$cell = $menuOrg->appendChild($this->doc->createElement('cell'));
			$turnOffComments = true;
			switch ($page->nodeName) {
				case 'page':
					$converter = new PageNavBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					$turnOffComments = false;
					break;
				case 'pageContent':
					$converter = new TextBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'navlink':
					$converter = new NavLinkBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'heading':
					$converter = new HeadingBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'divider':
					$converter = new DividerBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'pageRSS':
					$converter = new RssBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'participantList':
					$converter = new ParticipantListBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'categoryList':
					$converter = new CategoryListBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				default:
					throw new Exception("Unknown page type '".$page->nodeName."'.");
			}
			
			$childElement = $cell->appendChild($converter->convert());
			if ($turnOffComments)
				$childElement->setAttribute('commentsEnabled', 'false');
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
			$pages = $this->sourceXPath->query('./pageContent | ./navlink | ./heading | ./divider | ./participantList | ./categoryList', $this->sourceElement);
		else
			$pages = $this->sourceXPath->query("./pageContent[@location = '$side'] | ./navlink[@location = '$side'] | ./heading[@location = '$side'] | ./divider[@location = '$side'] | ./participantList[@location = '$side'] | ./categoryList[@location = '$side']", $this->sourceElement);
		
		foreach ($pages as $page) {
			$cell = $org->appendChild($this->doc->createElement('cell'));
			switch ($page->nodeName) {
				case 'pageContent':
					$converter = new TextBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'navlink':
					$converter = new NavLinkBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'heading':
					$converter = new HeadingBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'divider':
					$converter = new DividerBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'pageRSS':
					$converter = new RssBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'participantList':
					$converter = new ParticipantListBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				case 'categoryList':
					$converter = new CategoryListBlockSegue1To2Converter($page, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				default:
					throw new Exception("Unknown page type '".$page->nodeName."'.");
			}
			$childElement = $cell->appendChild($converter->convert());
			$childElement->setAttribute('commentsEnabled', 'false');
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