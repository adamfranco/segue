<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteDirector.class.php,v 1.6 2006/04/07 14:24:27 cws-midd Exp $
 */

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteDirector.abstract.php");
require_once(dirname(__FILE__)."/XmlSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlSiteNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlFixedOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlFlowOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlMenuOrganizerSiteComponent.class.php");

/**
 * The XMLSiteDirector handles the selection of active nodes and acts in the 
 * "Abstract Factor" pattern to create and provide-access to SiteComponents.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteDirector.class.php,v 1.6 2006/04/07 14:24:27 cws-midd Exp $
 */
class XmlSiteDirector
	// implements SiteDirector 
{
		
	/**
	 * Answer a new XML Site Director
	 * 
	 * @param <##>
	 * @return object XmlSiteDirector
	 * @access public
	 * @since 4/3/06
	 */
	function XmlSiteDirector ( $xmlDocument ) {
		$this->_document =& $xmlDocument;
		$this->_activeNodes = array();
		$this->_createdSiteComponents = array();
	}
	
	/**
	 * Answer the RootSiteComponent for the site
	 * 
	 * @param string $id
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function &getRootSiteComponent ( $id ) {
		$currentElement =& $this->_document->getElementByID($id, false);
		
		$this->activateDefaultsDown($currentElement);
		return $this->traverseUpToRootSiteComponent($currentElement);
	}
	
	/**
	 * Answer the RootSiteComponent by traversing up
	 * 
	 * @param DOMIT_Node $currentElement
	 * @return SiteNavBlockSiteComponent
	 * @access public
	 * @since 4/4/06
	 */
	function &traverseUpToRootSiteComponent ( $currentElement ) {
		if (!in_array($currentElement->getAttribute('id'), $this->_activeNodes))
			$this->_activeNodes[] = $currentElement->getAttribute('id');
		
		// Traverse Active Up
		if ($currentElement->nodeName == 'SiteNavBlock') {
			$component =& new XmlSiteNavBlockSiteComponent($this, $currentElement);
			return $component;
		} else
			return $this->traverseUpToRootSiteComponent(
								$this->_getParentWithId($currentElement));
	}
	
	/**
	 * Activate the default nodes going down the hierarchy.
	 *
	 * We will select the first NavBlock and activate down from that.
	 * 
	 * @param string $id
	 * @return boolean True if a NavBlock is found, false otherwise
	 * @access public
	 * @since 4/4/06
	 */
	function activateDefaultsDown ( $currentElement ) {
		// If this element is a NavBlock, record its Id as active and traverse
		// its children
		if ($currentElement->nodeType == 1 
			&& preg_match('/^.*NavBlock$/i', $currentElement->nodeName))
		{
			if (!in_array($currentElement->getAttribute('id'), $this->_activeNodes))
				$this->_activeNodes[] = $currentElement->getAttribute('id');
			
			$navFound = FALSE;
			$child =& $currentElement->firstChild;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDown($child);
				$child =& $child->nextSibling;
			}
			
			return TRUE;
		}
		
		// If this element isn't a NavBlock, traverse its children in case any of them
		// is a NavBlock
		else if ($currentElement->nodeType == 1) {
			$navFound = FALSE;
			$child =& $currentElement->firstChild;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDown($child);
				$child =& $child->nextSibling;
			}
			
			if ($navFound && $currentElement->hasAttribute('id')
				&& !in_array($currentElement->getAttribute('id'), $this->_activeNodes))
			{
				$this->_activeNodes[] = $currentElement->getAttribute('id');
			}
			
			return $navFound;
		}
	}
	
	/**
	 * Answer true if the node of id $id is active
	 * 
	 * @param string $id
	 * @return boolean
	 * @access public
	 * @since 4/4/06
	 */
	function isActive ( $id ) {
		return in_array($id, $this->_activeNodes);
	}
	
	/**
	 * Answer the component that has a particular Id
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function &getSiteComponentById ( $id ) {
		$element =& $this->_document->getElementByID($id, false);
		return $this->getSiteComponent($element);
	}
	
	/**
	 * Create and/or return the component for an element and register it for later fetching
	 * 
	 * @param object DOMIT_Node $element
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function &getSiteComponent ( &$element ) {
		$id = $element->getAttribute('id');
		if (!isset($this->_createdSiteComponents[$id])) {
			$class = "Xml".ucfirst($element->nodeName)."SiteComponent";
			$this->_createdSiteComponents[$id] =& new $class($this, $element);
		}
		return $this->_createdSiteComponents[$id];
	}
	
	/**
	 * Answer the id of the parent object
	 * 
	 * @param object DOMIT_Node
	 * @return object DOMIT_Node
	 * @access public
	 * @since 4/3/06
	 */
	function &_getParentWithId ( &$element ) {
		if ($element->parentNode->hasAttribute('id'))
			return $element->parentNode;
		else
			return $this->_getParentWithId($element->parentNode);
	}
	
	/**
	 * Answer a new Instance of the passed SiteComponent
	 *
	 * Note: parameter should have capital first letters of words
	 * @param string $componentClass just the unique 'FlowOrganizer' etc.
	 * @return object SiteComponent
	 * @access public
	 * @since 4/6/06
	 */
	function &createSiteComponent ( $componentClass ) {
		$class = 'Xml'.$componentClass.'SiteComponent';
		$element =& $this->_document->createElement($componentClass);
		$idManager =& Services::getService('Id');
		$newId =& $idManager->createId();
		$element->setAttribute('Id', $newId->getIdString());
		$this->_newSiteComponent =& new $class($this, $element);
		
		// @todo Log SiteComponent creation here
		
		return $this->_newSiteComponent;
	}
	
	/**
	 * Deletes the passed SiteComponent
	 * 
	 * @param object SiteComponent
	 * @return void
	 * @access public
	 * @since 4/6/06
	 */
	function deleteSiteComponent ( &$siteComponent ) {
		// @todo log SiteComponent deletion here
		
		unset($this->_createdSiteComponents[$siteComponent->getId()],
			$siteComponent);
	}
}

?>