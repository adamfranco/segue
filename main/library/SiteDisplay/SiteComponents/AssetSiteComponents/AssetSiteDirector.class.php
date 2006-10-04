<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteDirector.class.php,v 1.1 2006/10/04 20:36:19 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteDirector.abstract.php");
require_once(dirname(__FILE__)."/AssetSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetSiteNavBlockSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetFixedOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetNavOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetFlowOrganizerSiteComponent.class.php");
require_once(dirname(__FILE__)."/AssetMenuOrganizerSiteComponent.class.php");

require_once(dirname(__FILE__)."/../../Rendering/VisibilitySiteVisitor.class.php");


/**
 * The AssetSiteDirector handles the selection of active nodes and acts in the 
 * "Abstract Factor" pattern to create and provide-access to SiteComponents.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteDirector.class.php,v 1.1 2006/10/04 20:36:19 adamfranco Exp $
 */
class AssetSiteDirector
	// implements SiteDirector 
{
		
	/**
	 * Answer a new Asset Site Director
	 * 
	 * @param object Repository $repository The repository that contains the site nodes.
	 * @return object AssetSiteDirector
	 * @access public
	 * @since 4/3/06
	 */
	function AssetSiteDirector ( &$repository ) {
		$this->_repository =& $repository;
		$this->_activeNodes = array();
		$this->_createdSiteComponents = array();
		$this->_xmlDocuments = array();
		
		// Asset Types
		$this->SiteNavBlockType =& new Type('segue', 'edu.middlebury', 'SiteNavBlock');
		$this->NavBlockType =& new Type('segue', 'edu.middlebury', 'NavBlock');
		$this->BlockType =& new Type('segue', 'edu.middlebury', 'Block');
		$this->FixedOrganizerType =& new Type('segue', 'edu.middlebury', 'FixedOrganizer');
		$this->FlowOrganizerType =& new Type('segue', 'edu.middlebury', 'FlowOrganizer');
		$this->MenuOrganizerType =& new Type('segue', 'edu.middlebury', 'MenuOrganizer');
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
			
			$idManager =& Services::getService("Id");
			$currentAsset =& $this->_repository->getAsset($idManager->getId($id));
			
			$this->activateDefaultsDownAsset($currentAsset);
			$this->_rootSiteComponent =& $this->traverseUpToRootSiteComponent($currentAsset);
		}
		return $this->_rootSiteComponent;
	}
	
	/**
	 * Answer the RootSiteComponent by traversing up
	 * 
	 * @param object Asset $currentAsset
	 * @return SiteNavBlockSiteComponent
	 * @access public
	 * @since 4/4/06
	 */
	function &traverseUpToRootSiteComponent ( $currentAsset ) {
		ArgumentValidator::validate($currentAsset, ExtendsValidatorRule::getRule("Asset"));
		
		$id =& $currentAsset->getId();
		
		if (!in_array($id->getIdString(), $this->_activeNodes))
			$this->_activeNodes[] = $id->getIdString();
		
		// Traverse Active Up
		$siteNavType =& $this->SiteNavBlockType;
		if ($siteNavType->isEqual($currentAsset->getAssetType())) {
			$xmlDocument =& $this->getXmlDocumentFromAsset($currentAsset);
			$component =& new AssetSiteNavBlockSiteComponent($this, $currentAsset, $xmlDocument->documentElement);
			return $component;
		} else {
			$parentAssets =& $currentAsset->getParents();
			while ($parentAssets->hasNext()) {
				$parent =& $parentAssets->next();
				$parentType =& $parent->getAssetType();
				if ($parentType->getDomain() == 'segue')
					return $this->traverseUpToRootSiteComponent($parent);
			}
			
			throwError(new Error("No valid parents found in Site Component traversal.", "SiteDisplay"));
		}
	}
	
	/**
	 * Activate the default nodes going down the hierarchy.
	 *
	 * We will select the first NavBlock and activate down from that.
	 * 
	 * @param object Asset $currentAsset
	 * @return boolean True if a NavBlock is found, false otherwise
	 * @access public
	 * @since 4/4/06
	 */
	function activateDefaultsDownAsset ( $currentAsset ) {
		// If this element is a NavBlock, record its Id as active and traverse
		// its children
		if ($this->NavBlockType->isEqual($currentAsset->getAssetType())
			|| $this->SiteNavBlockType->isEqual($currentAsset->getAssetType()))
		{
			$id =& $currentAsset->getId();
			if (!in_array($id->getIdString(), $this->_activeNodes))
				$this->_activeNodes[] = $id->getIdString();
			
			// Get the Nav's XML to traverse
			$xmlDocument =& $this->getXmlDocumentFromAsset($currentAsset);
			
			$child =& $xmlDocument->documentElement->firstChild;
			$navFound = FALSE;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDownXml($child);
				$child =& $child->nextSibling;
			}
			
			return TRUE;
		}
		
		return FALSE;
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
	function activateDefaultsDownXml ( $currentElement ) {
		// If this element is a NavBlock, record its Id as active and traverse
		// its children
		if ($currentElement->nodeType == 1 
			&& preg_match('/^.*Block$/i', $currentElement->nodeName))
		{
			$idManager =& Services::getService('Id');
			$asset =& $this->_repository->getAsset($idManager->getId(
							$currentElement->getAttribute('id')));
			
			return $this->activateDefaultsDownAsset($asset);
		}
		
		// If this element isn't a NavBlock, traverse its children in case any of them
		// is a NavBlock
		else if ($currentElement->nodeType == 1) {
			$navFound = FALSE;
			$child =& $currentElement->firstChild;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDownXml($child);
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
	 * Answer a DOMIT_Document from a text string
	 * 
	 * @param object Asset $asset
	 * @return object DOMIT_Document
	 * @access public
	 * @since 9/25/06
	 */
	function &getXmlDocumentFromAsset ( &$asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule('Asset'));
		
		$id =& $asset->getId();
		$assetIdString = $id->getIdString();
		if (!$this->NavBlockType->isEqual($asset->getAssetType())
			&& !$this->SiteNavBlockType->isEqual($asset->getAssetType()))
		{
			$null = null;
			return $null;
		}
		
		if (!isset($this->_xmlDocuments[$assetIdString])) {
			$this->_xmlDocuments[$assetIdString] =& new DOMIT_Document();
			$this->_xmlDocuments[$assetIdString]->setNamespaceAwareness(true);
			$assetContent = $asset->getContent();
			$success = $this->_xmlDocuments[$assetIdString]->parseXML($assetContent->asString());
	
			if ($success !== true) {
				throwError(new Error("DOMIT error: ".$this->_xmlDocuments[$assetIdString]->getErrorCode().
					"<br/>\t meaning: ".$this->_xmlDocuments[$assetIdString]->getErrorString()."<br/>", "SiteDisplay"));
			}
		}
		
		return $this->_xmlDocuments[$assetIdString];
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
		$idManager =& Services::getService('Id');
		if (preg_match('/^(\w+)----(\w+)$/', $id, $matches)) {
			$asset =& $this->_repository->getAsset(
						$idManager->getId($matches[1]));
			$xmlDoc =& $this->getXmlDocumentFromAsset($asset);
			$element =& $xmlDoc->getElementByID($matches[2], false);
			return $this->getSiteComponentFromXml($element);
		} else {
			return $this->getSiteComponentFromAsset(
						$this->_repository->getAsset(
							$idManager->getId($id)));
		}		
	}
	
	/**
	 * Create and/or return the component for an asset and register it for later fetching
	 * 
	 * @param object DOMIT_Node $element
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function &getSiteComponentFromAsset ( &$asset ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule('Asset'));
		
		$id =& $asset->getId();
		$idString = $id->getIdString();
		if (!isset($this->_createdSiteComponents[$idString])) {
			$type =& $asset->getAssetType();
			$class = "Asset".ucfirst($type->getKeyword())."SiteComponent";
			
			$xmlDocument =& $this->getXmlDocumentFromAsset($asset);
			
			$this->_createdSiteComponents[$idString] =& new $class($this, $asset, $xmlDocument->documentElement);
		}
		return $this->_createdSiteComponents[$idString];
	}
	
	/**
	 * Create and/or return the component for an element and register it for later fetching
	 * 
	 * @param object DOMIT_Node $element
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function &getSiteComponentFromXml ( &$asset, &$element ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule('Asset'));
		ArgumentValidator::validate($element, ExtendsValidatorRule::getRule('DOMIT_Node'));
		
		$id = $element->getAttribute('id');
		if (!isset($this->_createdSiteComponents[$id])) {
			$class = "Asset".ucfirst($element->nodeName)."SiteComponent";
			$this->_createdSiteComponents[$id] =& new $class($this, $asset, $element);
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
	 * @param string $componentClass just the unique 'FlowOrganizer' etc.
	 * @return object SiteComponent
	 * @access public
	 * @since 4/6/06
	 */
	function &createSiteComponent ( $componentClass ) {
		throwError(new Error("Should we be getting here?"));
		
		$class = 'Asset'.$componentClass.'SiteComponent';
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