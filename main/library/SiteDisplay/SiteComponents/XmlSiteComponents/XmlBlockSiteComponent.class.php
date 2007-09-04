<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlBlockSiteComponent.class.php,v 1.10 2007/09/04 15:05:33 adamfranco Exp $
 */ 

/**
 * The Block is a non-organizational site component. Blocks make up content
 * and nodes in the site hierarchy
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlBlockSiteComponent.class.php,v 1.10 2007/09/04 15:05:33 adamfranco Exp $
 */
class XmlBlockSiteComponent
	extends XmlSiteComponent
	// implements BlockSiteComponent
{

	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		$this->updateDisplayName('');
		$this->updateDescription('');
		$this->updateContentMarkup('');
	}
		
	/**
	 * Answer the displayName
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDisplayName () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'displayName')
				return $child->getText();
			$child = $child->nextSibling;
		}
		
		return _('Default Name');
	}
	
	/**
	 * Update the displayName
	 * 
	 * @param string $displayName
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateDisplayName ( $displayName ) {
		$child = $this->_element->firstChild;
		$cdata = $this->_element->ownerDocument->createCDATASection($displayName);
		while ($child) {
			if ($child->nodeName == 'displayName') {
				$child->replaceChild($cdata, $child->firstChild);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// displayName not found... create it
		$newElement = $this->_element->ownerDocument->createElement('displayName');
		$newElement->appendChild($cdata);
		$this->_element->appendChild($newElement);
	}
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDescription () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'description')
				return $child->getText();
			$child = $child->nextSibling;
		}
		
		return _('');
	}
	
	/**
	 * Update the description
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateDescription ( $description ) {
		$child = $this->_element->firstChild;
		$cdata = $this->_element->ownerDocument->createCDATASection($description);
		while ($child) {
			if ($child->nodeName == 'description') {
				$child->replaceChild($cdata, $child->firstChild);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// description not found... create it
		$newElement = $this->_element->ownerDocument->createElement('description');
		$newElement->appendChild($cdata);
		$this->_element->appendChild($newElement);
	}
	
	/**
	 * Answer the HTML markup that represents the title of the block. This may
	 * be the displayName alone, the displayName with additional HTML, or some
	 * other HTML representation of the title.
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getTitleMarkup () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'titleMarkup')
				return $child->getText();
			$child = $child->nextSibling;
		}
		
		// default case
		return $this->getDisplayName();
	}
	
	/**
	 * Update the titleMarkup
	 * 
	 * @param string $titleMarkup
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateTitleMarkup ( $titleMarkup ) {
		$child = $this->_element->firstChild;
		$cdata = $this->_element->ownerDocument->createCDATASection($titleMarkup);
		while ($child) {
			if ($child->nodeName == 'titleMarkup') {
				$child->replaceChild($cdata, $child->firstChild);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// titleMarkup not found... create it
		$newElement = $this->_element->ownerDocument->createElement('titleMarkup');
		$newElement->appendChild($cdata);
		$this->_element->appendChild($newElement);
	}

	
	/**
	 * Answer the contentMarkup
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getContentMarkup () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'contentMarkup')
				return $child->getText();
			$child = $child->nextSibling;
		}
		
		return _('');
	}
	
	/**
	 * Update the contentMarkup
	 * 
	 * @param string $contentMarkup
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateContentMarkup ( $contentMarkup ) {
		$child = $this->_element->firstChild;
		$cdata = $this->_element->ownerDocument->createCDATASection($contentMarkup);
		while ($child) {
			if ($child->nodeName == 'contentMarkup') {
				$child->replaceChild($cdata, $child->firstChild);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// contentMarkup not found... create it
		$newElement = $this->_element->ownerDocument->createElement('contentMarkup');
		$newElement->appendChild($cdata);
		$this->_element->appendChild($newElement);
	}
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function acceptVisitor ( $visitor, $inMenu = FALSE ) {
		if ($inMenu)
			return $visitor->visitBlockInMenu($this);
		return $visitor->visitBlock($this);
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		$possibleDestinations = $this->_director->getVisibleComponents();
		$parent = $this->getParentComponent();
		foreach (array_keys($possibleDestinations) as $id) {
			if ($id == $parent->getId())
				continue;
			
			switch (strtolower(get_class($possibleDestinations[$id]))) {
				case 'xmlblocksitecomponent':
					break;
				case 'xmlfixedorganizersitecomponent':
					break;
				case 'xmlnavorganizersitecomponent':
					break;
				default:
					$results[$id] = $possibleDestinations[$id];
					break;
			}
		}
		
		return $results;
	}

}

?>