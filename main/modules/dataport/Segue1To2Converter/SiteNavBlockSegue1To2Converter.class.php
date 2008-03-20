<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSegue1To2Converter.class.php,v 1.5 2008/03/20 15:44:51 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavBlockSegue1To2Converter.abstract.php");
require_once(dirname(__FILE__)."/NavLinkBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/SectionNavBlockSegue1To2Converter.class.php");

/**
 * A nav Block for the overall site
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSegue1To2Converter.class.php,v 1.5 2008/03/20 15:44:51 adamfranco Exp $
 */
class SiteNavBlockSegue1To2Converter
	extends NavBlockSegue1To2Converter
{
	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 2/12/08
	 */
	public function convert () {
		$element = parent::convert();
		$element->setAttribute('slot_name', $this->sourceElement->getAttribute('id'));
		// Reset the id to encode it as a slot
		$element->setAttribute('id', 'site_'.$this->sourceElement->getAttribute('id'));
		
		$this->setSiteWidth($element);
		
		// Convert links of the form [[localurl:site=xxxx&amp;section=yyyy&amp;page=zzzz]] to [[nodeurl:xxxx]]
		$this->updateAllLocalUrls();
		
		return $element;
	}
		
	/**
	 * Answer the appropriate nodeName for this item
	 * 
	 * @return string
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getNodeName () {
		return 'SiteNavBlock';
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
		if ($this->siteCommentsEnabled()) {
			$element->setAttribute('commentsEnabled', 'true');
		}
	}
	
	/**
	 * Answer true if all blocks in the site have comments enabled and that
	 * the commentsEnabled setting should be made here.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 3/19/08
	 */
	protected function siteCommentsEnabled () {
		$storyNodes = $this->sourceXPath->query('./section/page/story | ./section/page/file | ./section/page/link | ./section/page/rss | ./section/page/image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('./section/page/*/discussion', $this->sourceElement);
		if ($discussionNodes->length > 0 && $storyNodes->length == $discussionNodes->length)
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
		$navOrgElement = $navBlockElement->appendChild($this->doc->createElement("NavOrganizer"));
		$navOrgElement->setAttribute('id', $this->createId());
		$navOrgElement->setAttribute('rows', 3);
		$navOrgElement->setAttribute('cols', 1);
		
		// Add the header
		$cell = $navOrgElement->appendChild($this->doc->createElement('cell'));
		try {
			$html = $this->getStringValue($this->getSingleSourceElement('./header', $this->sourceElement));
		} catch (MissingNodeException $e) {
			$html = '';
		}
		$org = $cell->appendChild($this->doc->createElement('FlowOrganizer'));
		$org->setAttribute('id', $this->createId());
		$org->setAttribute('commentsEnabled', 'false');
		$org->setAttribute('rows', 0);
		$org->setAttribute('cols', 1);
		$org->setAttribute('showDisplayNames', 'false');
		$cell = $org->appendChild($this->doc->createElement('cell'));
		$cell->appendChild($this->createTextBlockForHtml($html, 'header'));
		
		// Central content area
		$cell = $navOrgElement->appendChild($this->doc->createElement('cell'));
		$cell->appendChild($this->getMainContentArea($this->sourceElement));
		
		// Add the footer
		$cell = $navOrgElement->appendChild($this->doc->createElement('cell'));
		try {
			$html = $this->getStringValue($this->getSingleSourceElement('./footer', $this->sourceElement));
		} catch (MissingNodeException $e) {
			$html = '';
		}
		$org = $cell->appendChild($this->doc->createElement('FlowOrganizer'));
		$org->setAttribute('id', $this->createId());
		$org->setAttribute('commentsEnabled', 'false');
		$org->setAttribute('rows', 0);
		$org->setAttribute('cols', 1);
		$org->setAttribute('showDisplayNames', 'false');
		$cell = $org->appendChild($this->doc->createElement('cell'));
		$cell->appendChild($this->createTextBlockForHtml($html, 'footer'));
	}
	
	/**
	 * Answer the main content area, a Fixed organizer with the section menu and target.
	 * 
	 * @param object DOMElement $sourceElement
	 * @return object DOMElement The resulting organizer element.
	 * @access private
	 * @since 2/6/08
	 */
	private function getMainContentArea (DOMElement $sourceElement) {
		$organizer = $this->doc->createElement("FixedOrganizer");
		$organizer->setAttribute('id', $this->createId());
		
		// Add the Section Navigation
		$cell = $organizer->appendChild($this->doc->createElement('cell'));
		$sectionMenu = $cell->appendChild($this->getSectionMenu());
		$sectionMenu->setAttribute('target_id', $organizer->getAttribute('id')."_cell:1");
		
		// Add the Content Target
		$cell = $organizer->appendChild($this->doc->createElement('cell'));
		
		if ($this->useSideSections()) {
			$organizer->setAttribute('rows', '1');
			$organizer->setAttribute('cols', '2');
			$sectionMenu->setAttribute('direction', 'Top-Bottom/Left-Right');
			$this->setNavigationWidth($sectionMenu);
		} else {
			$organizer->setAttribute('rows', '2');
			$organizer->setAttribute('cols', '1');
			$sectionMenu->setAttribute('direction', 'Left-Right/Top-Bottom');
		}
		
		return $organizer;
	}
	
	/**
	 * Answer the Section-level menu
	 * 
	 * @return object DOMElement
	 * @access private
	 * @since 2/6/08
	 */
	private function getSectionMenu () {
		$organizer = $this->doc->createElement("MenuOrganizer");
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($organizer);
		
		$organizer->setAttribute('id', $this->createId());
		
		$sections = $this->sourceXPath->query('/site/section | /site/navlink');
		foreach ($sections as $section) {
			$cell = $organizer->appendChild($this->doc->createElement('cell'));
			switch ($section->nodeName) {
				case 'navlink':
					$converter = new NavLinkBlockSegue1To2Converter($section, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
					break;
				default:
					$converter = new SectionNavBlockSegue1To2Converter($section, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
			}
			$cell->appendChild($converter->convert());
		}
		
		return $organizer;
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
	
	/**
	 * Add the roles from an element
	 * 
	 * @param DOMElement $destElement
	 * @return void
	 * @access protected
	 * @since 2/4/08
	 */
	protected function addRoles (DOMElement $destElement) {
		parent::addRoles($destElement);
		
		$this->addRoleForAgent($this->sourceElement->getAttribute('owner'), 'admin', $destElement);
	}
	
	/**
	 * Convert links of the form [[localurl:site=xxxx&amp;section=yyyy&amp;page=zzzz]] to [[nodeurl:xxxx]]
	 * 
	 * @return void
	 * @access protected
	 * @since 2/14/08
	 */
	protected function updateAllLocalUrls () {
		$textNodes = $this->xpath->query('//text()');
		foreach ($textNodes as $textNode) {
			$textNode->nodeValue = $this->updateAllLocalUrlsInHtml($textNode->nodeValue);
		}
	}
	
	/**
	 * Convert links of the form [[localurl:site=xxxx&amp;section=yyyy&amp;page=zzzz]] to [[nodeurl:xxxx]]
	 *
	 * At this point, the implmentation only is able to match nodes in the current site. Urls
	 * that do not match nodes in the current site are left unchanged.
	 * 
	 * @param string $html
	 * @return string
	 * @access protected
	 * @since 2/14/08
	 */
	protected function updateAllLocalUrlsInHtml ($html) {
		$pattern = '/\[\[localurl:([^\]]+)\]\]/i';
		preg_match_all($pattern, $html, $matches);
		for ($i = 0; $i < count($matches[0]); $i++) {
			try {
				$nodeId = $this->getNodeIdForParamString($matches[1][$i]);
// 				printpre($nodeId);
				$html = str_replace($matches[0][$i], '[[nodeurl:'.$nodeId.']]', $html);
			} catch (UnknownIdException $e) {
// 				printpre($e->getMessage());
			};
		}
		
		return $html;
	}
	
	/**
	 * Given a Segue1 parameter id string, return a matching node id.
	 *
	 * Throws an UnknownIdException if not found
	 * 
	 * @param string $params
	 * @return string
	 * @access protected
	 * @since 2/14/08
	 */
	protected function getNodeIdForParamString ($params) {
		$pattern = '/
		
		# 1. site
		(?:
			(?: &amp;|& )?	# leading ampersand
			site=([^&]+)
		)?
		
		# 2. section
		(?:
			(?: &amp;|& )?	# leading ampersand
			section=([^&]+)
		)?
		
		# 3. page
		(?:
			(?: &amp;|& )?	# leading ampersand
			page=([^&]+)
		)?
		
		# 4. story
		(?:
			(?: &amp;|& )?	# leading ampersand
			story=([^&]+)
		)?
		
		# 5. story detail
		(?:
			(?: &amp;|& )?	# leading ampersand
			detail=([^&]+)
		)?
		
		# 6. discussion post
		(?:
			(?: &amp;|& )?	# leading ampersand
			expand=([^&]+)
		)?
		
		/xi';
		
		if (!preg_match($pattern, $params, $matches)) {
			throw new UnknownIdException("Cannot find a node id in '$params'.");
		}
				
		for ($i = 6; $i > 0; $i--) {
			if (isset($matches[$i]) && $matches[$i]) {
				switch ($i) {
					case 6:
						$type = 'comment_';
						break;
					case 5:
					case 4:
						$type = 'story_';
						break;
					case 3:
						$type = 'page_';
						break;
					case 2:
						$type = 'section_';
						break;
					case 1:
						$type = '';
						break;
					default:
						throw new Exception("unknown url part.");
				}
				
				$id = $type.$matches[$i];
				
				// Verify that the new Id exists in the document
				$nodesWithId = $this->xpath->query('//Block[@id = "'.$id.'"] | //NavBlock[@id = "'.$id.'"] | //SiteNavBlock[@id = "'.$id.'"] | //Comment[@id = "'.$id.'"]');
				if ($nodesWithId->length < 1)
					throw new UnknownIdException("Cannot find a node in the document with Id, '$id' to match '$params'.");
				if ($nodesWithId->length > 1)
					throw new Exception("More than one node found with Id, '$id'.");
				
				return $id;
			}
		}
		
	}
}

?>