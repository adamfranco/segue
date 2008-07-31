<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteDirector.class.php,v 1.23 2008/02/27 21:51:10 adamfranco Exp $
 */

require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");

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
 * @version $Id: AssetSiteDirector.class.php,v 1.23 2008/02/27 21:51:10 adamfranco Exp $
 */
class AssetSiteDirector
	implements SiteDirector 
{

	/**
	 * Create an instance of the Director for a particular Asset
	 * 
	 * @param object Asset $asset
	 * @return object AssetSiteDirector
	 * @access public
	 * @since 10/25/07
	 * @static
	 */
	public static function forAsset (Asset $asset) {
		return new AssetSiteDirector($asset->getRepository());
	}
		
	/**
	 * Answer a new Asset Site Director
	 * 
	 * @param object Repository $repository The repository that contains the site nodes.
	 * @return object AssetSiteDirector
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct ( Repository $repository ) {
		$this->_repository = $repository;
		$this->_activeNodes = array();
		$this->_createdSiteComponents = array();
		$this->_xmlDocuments = array();
		
		// Asset Types
		$this->siteDisplayTypes = array();
		
		$this->SiteNavBlockType = new Type('segue', 'edu.middlebury', 'SiteNavBlock');
		$this->siteDisplayTypes[] = $this->SiteNavBlockType;
		
		$this->NavBlockType = new Type('segue', 'edu.middlebury', 'NavBlock');
		$this->siteDisplayTypes[] = $this->NavBlockType;
		
		$this->BlockType = new Type('segue', 'edu.middlebury', 'Block');
		$this->siteDisplayTypes[] = $this->BlockType;
		
		$this->organizerTypes = array();
		
		$this->FixedOrganizerType = new Type('segue', 'edu.middlebury', 'FixedOrganizer');
		$this->siteDisplayTypes[] = $this->FixedOrganizerType;
		$this->organizerTypes[] = $this->FixedOrganizerType;
		
		$this->NavOrganizerType = new Type('segue', 'edu.middlebury', 'NavOrganizer');
		$this->siteDisplayTypes[] = $this->NavOrganizerType;
		$this->organizerTypes[] = $this->NavOrganizerType;
		
		$this->FlowOrganizerType = new Type('segue', 'edu.middlebury', 'FlowOrganizer');
		$this->siteDisplayTypes[] = $this->FlowOrganizerType;
		$this->organizerTypes[] = $this->FlowOrganizerType;
		
		$this->MenuOrganizerType = new Type('segue', 'edu.middlebury', 'MenuOrganizer');
		$this->siteDisplayTypes[] = $this->MenuOrganizerType;
		$this->organizerTypes[] = $this->MenuOrganizerType;
	}
	
	/**
	 * Clear the DOM cache. This needs to be done when moving around components and then
	 * making subsequent calls to director methods that re-fetch the dom cache
	 * 
	 * @return void
	 * @access public
	 * @since 5/22/07
	 */
	function clearDomCache () {
		$this->_activeNodes = array();
		$this->_visibleComponents = array();
		$this->_filledTargetIds = array();
	}
	
	/**
	 * Answer the RootSiteComponent for the site
	 * 
	 * @param string $id
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function getRootSiteComponent ( $id ) {
		if (!isset($this->_rootSiteComponent)) {
			ArgumentValidator::validate($id, StringValidatorRule::getRule());
			
			$idManager = Services::getService("Id");
			
			if (preg_match('/^(\w+)----(\w+)$/', $id, $matches))
				$currentAsset = $this->_repository->getAsset(
						$idManager->getId($matches[1]));
			else
				$currentAsset = $this->_repository->getAsset($idManager->getId($id));
			
			$this->activateDefaultsDownAsset($currentAsset);
			$this->_rootSiteComponent = $this->traverseUpToRootSiteComponent($currentAsset);
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
	function traverseUpToRootSiteComponent ( $currentAsset ) {
		ArgumentValidator::validate($currentAsset, ExtendsValidatorRule::getRule("Asset"));
		
		$id = $currentAsset->getId();
		
		if (!in_array($id->getIdString(), $this->_activeNodes))
			$this->_activeNodes[] = $id->getIdString();
		
		// Traverse Active Up
		$siteNavType = $this->SiteNavBlockType;
		if ($siteNavType->isEqual($currentAsset->getAssetType())) {
			$xmlDocument = $this->getXmlDocumentFromAsset($currentAsset);
			$component = new AssetSiteNavBlockSiteComponent($this, $currentAsset, $xmlDocument->documentElement);
			return $component;
		} else {
			$parentAssets = $currentAsset->getParents();
			while ($parentAssets->hasNext()) {
				$parent = $parentAssets->next();
				$parentType = $parent->getAssetType();
				if (preg_match('/segue/i', $parentType->getDomain()))
					return $this->traverseUpToRootSiteComponent($parent);
			}
			
			throw new OperationFailedException("No valid parents found in SiteComponent traversal for Asset id: '".$id->getIdString()."' type: '".$currentAsset->getAssetType()->asString()."'. This Asset not a site component or is not properly attached to the Hierarchy.");
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
		// Escape on lack of view authorization anywhere below this node
		// Since view AZs cascade up, just check at the node.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$currentAsset->getId()))
		{
			return false;
		}
		
		// If this element is a NavBlock, record its Id as active and traverse
		// its children
		if ($this->NavBlockType->isEqual($currentAsset->getAssetType())
			|| $this->SiteNavBlockType->isEqual($currentAsset->getAssetType()))
		{
			$id = $currentAsset->getId();
			if (!in_array($id->getIdString(), $this->_activeNodes))
				$this->_activeNodes[] = $id->getIdString();
			
			// Get the Nav's XML to traverse
			$xmlDocument = $this->getXmlDocumentFromAsset($currentAsset);
			
			$child = $xmlDocument->documentElement->firstChild;
			$navFound = FALSE;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDownXml($child);
				$child = $child->nextSibling;
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
			if (!$currentElement->getAttribute('id'))
				throwError(new Error("No id attribute in: ".$currentElement->saveXML()."\nWithin document: ".$currentElement->ownerDocument->saveXML()));
			
			$idManager = Services::getService('Id');
			try {
				$asset = $this->_repository->getAsset($idManager->getId(
							$currentElement->getAttribute('id')));
				return $this->activateDefaultsDownAsset($asset);
			} catch (UnknownIdException $e) {
				return null;
			}
		}
		
		// If this element isn't a NavBlock, traverse its children in case any of them
		// is a NavBlock
		else if ($currentElement->nodeType == 1) {
			$navFound = FALSE;
			$child = $currentElement->firstChild;
			while ($child && !$navFound) {
				$navFound = $this->activateDefaultsDownXml($child);
				$child = $child->nextSibling;
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
	 * Answer a DOMDocument from a text string
	 * 
	 * @param object Asset $asset
	 * @return object DOMDocument
	 * @access public
	 * @since 9/25/06
	 */
	function getXmlDocumentFromAsset ( Asset $asset ) {		
		$id = $asset->getId();
		$assetIdString = $id->getIdString();
		if (!$this->NavBlockType->isEqual($asset->getAssetType())
			&& !$this->SiteNavBlockType->isEqual($asset->getAssetType()))
		{
			throw new NonNavException ('Wrong Asset Type to retrieve XML document from.');
		}
		
		if (!isset($this->_xmlDocuments[$assetIdString])) {
			$this->_xmlDocuments[$assetIdString] = new Harmoni_DOMDocument();
			$this->_xmlDocuments[$assetIdString]->preserveWhiteSpace = false; // Remove whitespace when loading document
// 			$this->_xmlDocuments[$assetIdString]->setNamespaceAwareness(true); // From DOMIT implementation
			$assetContent = $asset->getContent();
			
			// if we have asset content, parse it
			if (strlen($assetContent->asString()))
				$success = $this->_xmlDocuments[$assetIdString]->loadXML($assetContent->asString());
			// otherwise, just use the empty document.
			else
				$success = true;
	
			if ($success !== true) {
				throwError(new Error("DOM error: ".$this->_xmlDocuments[$assetIdString]->getErrorCode().
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
	function getSiteComponentById ( $id ) {
		$idManager = Services::getService('Id');
		if (preg_match('/^(\w+)----(\w+)$/', $id, $matches)) {
			$asset = $this->_repository->getAsset(
						$idManager->getId($matches[1]));
			$xmlDoc = $this->getXmlDocumentFromAsset($asset);
			$element = $xmlDoc->getElementByIdAttribute($matches[2]);
			return $this->getSiteComponentFromXml($asset, $element);
		} else {
			return $this->getSiteComponentFromAsset(
						$this->_repository->getAsset(
							$idManager->getId($id)));
		}		
	}
	
	/**
	 * Create and/or return the component for an asset and register it for later fetching
	 * 
	 * @param object Asset $asset
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function getSiteComponentFromAsset ( Asset $asset ) {		
		$id = $asset->getId();
		$idString = $id->getIdString();
		if (!isset($this->_createdSiteComponents[$idString])) {
			$type = $asset->getAssetType();
			foreach ($this->siteDisplayTypes as $siteDisplayType) {
				if ($type->isEqual($siteDisplayType)) {
					$typeKey = ucfirst($type->getKeyword());
					break;
				}
			}
			if (!isset($typeKey))
				$typeKey = 'Block';
			$class = "Asset".$typeKey."SiteComponent";
			
			try {
			$xmlDocument = $this->getXmlDocumentFromAsset($asset);
				$documentElement = $xmlDocument->documentElement;
			} catch (NonNavException $e) {
				$documentElement = null;
			}
			
			
			$this->_createdSiteComponents[$idString] = new $class($this, $asset, $documentElement);
		}
		return $this->_createdSiteComponents[$idString];
	}
	
	/**
	 * Create and/or return the component for an element and register it for later fetching
	 * 
	 * @param object DOMElement $element
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function getSiteComponentFromXml (Asset $asset, DOMElement $element ) {		
		$id = $element->getAttribute('id');
		if (!isset($this->_createdSiteComponents[$id])) {
			$class = "Asset".ucfirst($element->nodeName)."SiteComponent";
			$this->_createdSiteComponents[$id] = new $class($this, $asset, $element);
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
	function getVisibleComponents ($id = null) {
		if (!isset($this->_visibleComponents)) {
			$visibilityVisitor = new VisibilitySiteVisitor;
			$rootSiteComponent = $this->getRootSiteComponent($id);
			$visiblityArray = $rootSiteComponent->acceptVisitor($visibilityVisitor);
			$this->_visibleComponents = $visiblityArray['VisibleComponents'];
			$this->_filledTargetIds = $visiblityArray['FilledTargetIds'];
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
	 * @param object DOMElement
	 * @return object DOMElement
	 * @access public
	 * @since 4/3/06
	 */
	function _getParentWithId ( DOMElement $element ) {		
		$extendsRule = ExtendsValidatorRule::getRule("DOMElement");
		
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
	 * @return object SiteComponent
	 * @access public
	 * @since 4/6/06
	 */
	function createSiteComponent ( $componentType, $parentComponent ) {
// 		throwError(new Error("Should we be getting here?"));

		foreach ($this->siteDisplayTypes as $siteDisplayType) {
			if ($componentType->isEqual($siteDisplayType)) {
				$typeKey = ucfirst($componentType->getKeyword());
				break;
			}
		}
		if (!isset($typeKey))
			$typeKey = 'Block';
		$class = "Asset".$typeKey."SiteComponent";
		
		
		// For blocks, create an asset for them
		if (preg_match('/^.*BlockSiteComponent$/', $class)) {
			$asset = $this->_repository->createAsset("Untitled", "",
						$componentType);
			$assetId = $asset->getId();
			$element = null;
			$newId = $assetId->getIdString();
			
			$this->_createdSiteComponents[$newId] = new $class($this, $asset, $element);
			$this->_createdSiteComponents[$newId]->populateWithDefaults();
			
			if (!$componentType->isEqual($this->SiteNavBlockType)) {
				$parentComponent->addSubcomponent($this->_createdSiteComponents[$newId]);
			}
		} 
		// For Organizers, use the parent's asset.
		else {
			$asset = $parentComponent->_asset;
			
			$parentElement = $parentComponent->getElement();
			$element = $parentElement->ownerDocument->createElement($typeKey);
			$idManager = Services::getService('Id');
			$newIdObj = $idManager->createId();
			$newId = $newIdObj->getIdString();
			$element->setAttribute('id', $newId);
			
			$this->_createdSiteComponents[$newId] = new $class($this, $asset, $element);
			$this->_createdSiteComponents[$newId]->populateWithDefaults();
		}
		
		/*********************************************************
		 * Log the event
		 *********************************************************/
		$siteComponent = $this->_createdSiteComponents[$newId];
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Create Component", $siteComponent->getComponentClass()." created.");
			
			$item->addNodeId($siteComponent->getQualifierId());
			try {
				$site = $this->getRootSiteComponent($siteComponent->getId());
				if (!$siteComponent->getQualifierId()->isEqual($site->getQualifierId()))
					$item->addNodeId($site->getQualifierId());
			} catch (Exception $e) {
				// If creating nested components, sometimes traversal won't be possible.
			}
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		
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
	function deleteSiteComponent ( $siteComponent ) {
		/*********************************************************
		 * Log the event
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Delete Component", $siteComponent->getComponentClass()." deleted: ". $siteComponent->getDisplayName());
			
			$item->addNodeId($siteComponent->getQualifierId());
			$site = $this->getRootSiteComponent($siteComponent->getId());
			if (!$siteComponent->getQualifierId()->isEqual($site->getQualifierId()))
				$item->addNodeId($site->getQualifierId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		
		$id = $siteComponent->getId();
		$siteComponent->deleteAndCleanUpData();
		
		unset($this->_createdSiteComponents[$id], $siteComponent);
	}
}

/**
 * An exception to throw when trying to get nav information from a non-nav asset.
 * 
 * @since 8/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteDirector.class.php,v 1.23 2008/02/27 21:51:10 adamfranco Exp $
 */
class NonNavException
	extends Exception
{
	
}


?>