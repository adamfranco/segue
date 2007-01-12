<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteDirector.class.php,v 1.15 2007/01/12 21:59:18 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteDirector.abstract.php");
require_once(dirname(__FILE__)."/XmlSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlSiteNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlFixedOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlNavOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlFlowOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/XmlMenuOrganizerSiteComponent.class.php");

require_once(dirname(__FILE__)."/../../Rendering/VisibilitySiteVisitor.class.php");


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
 * @version $Id: XmlSiteDirector.class.php,v 1.15 2007/01/12 21:59:18 adamfranco Exp $
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
	function &getRootSiteComponent ( $id = null ) {
		if (!isset($this->_rootSiteComponent)) {
			ArgumentValidator::validate($id, StringValidatorRule::getRule());
			$currentElement =& $this->_document->getElementByID($id, false);
			
			$this->activateDefaultsDown($currentElement);
			$this->_rootSiteComponent =& $this->traverseUpToRootSiteComponent($currentElement);
		}
		return $this->_rootSiteComponent;
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
		ArgumentValidator::validate($currentElement, ExtendsValidatorRule::getRule("DOMIT_Element"));
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
	 * Answer an array of the visible site components
	 * 
	 * @param string $id
	 * @return ref array
	 * @access public
	 * @since 4/10/06
	 */
	function &getVisibleComponents ($id = null) {
		if (!isset($this->_visibleComponents)) {
			$visibilityVisitor =& new VisibilitySiteVisitor;
			$rootSiteComponent =& $this->getRootSiteComponent($id);
			$visiblityArray =& $rootSiteComponent->acceptVisitor($visibilityVisitor);
			$this->_visibleComponents =& $visiblityArray['VisibleComponents'];
			$this->_filledTargetIds =& $visiblityArray['FilledTargetIds'];
		}
		return $this->_visibleComponents;
	}
	
	/**
	 * Answer an array of the ids of the cells that are filled/used targets.
	 * the keys of this array are the ids of the menus that use them.
	 * 
	 * @param string $id
	 * @return ref array
	 * @access public
	 * @since 4/10/06
	 */
	function getFilledTargetIds ($id = null) {
		if (!isset($this->_filledTargetIds)) {
			$this->getVisibleComponents($id);
		}
		return $this->_filledTargetIds;
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
		ArgumentValidator::validate($element, ExtendsValidatorRule::getRule("DOMIT_Element"));
		
		$extendsRule =& ExtendsValidatorRule::getRule("DOMIT_Element");
		
		if ($extendsRule->check($element->parentNode)) {
			if ($element->parentNode->hasAttribute('id'))
				return $element->parentNode;
			else
				return $this->_getParentWithId($element->parentNode);
		
		// If this is a newly created node that hasn't been attached yet...
		} else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer a new Instance of the passed SiteComponent
	 *
	 * Note: parameter should have capital first letters of words
	 * @param object Type $componentType E.g. new Type('segue', 'edu.middlebury', 'FlowOrganizer') etc.
	 * @param object $parentComponent The component under which this component will be created.
	 * @return object SiteComponent
	 * @access public
	 * @since 4/6/06
	 */
	function &createSiteComponent ( $componentType, &$parentComponent ) {
		// $parentComponent is unused in this implementation, but is used
		// by other storage implementations.
		
		$class = 'Xml'.$componentType->getKeyword.'SiteComponent';
		$element =& $this->_document->createElement($componentClass);
		$idManager =& Services::getService('Id');
		$newId =& $idManager->createId();
		$newId = $newId->getIdString();
		$element->setAttribute('id', $newId);
		$this->_createdSiteComponents[$newId] =& new $class($this, $element);
		$this->_createdSiteComponents[$newId]->populateWithDefaults();
		
		// @todo Log SiteComponent creation here
		
		return $this->_createdSiteComponents[$newId];
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