<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetNavBlockSiteComponent.class.php,v 1.16 2007/12/18 20:09:54 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/NavBlockSiteComponent.abstract.php");

/**
 * The NavBlock component is a hierarchal node that provides a gateway to a 
 * sub-level of the hierarchy.
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetNavBlockSiteComponent.class.php,v 1.16 2007/12/18 20:09:54 adamfranco Exp $
 */
class AssetNavBlockSiteComponent
	extends AssetBlockSiteComponent
	implements NavBlockSiteComponent
{

	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		parent::populateWithDefaults();
		
		$xmlDocument = $this->_director->getXmlDocumentFromAsset($this->_asset);
		$this->_element = $xmlDocument->createElement($this->getComponentClass());
		$xmlDocument->appendChild($this->_element);
				
		$this->setOrganizer($this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "NavOrganizer"), $this));
	}
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 10/6/06
	 */
	function getComponentClass () {
		return 'NavBlock';
	}
	
	/**
	 * Answer the DOMIT_Element associated with this SiteComponent
	 * 
	 * @return object DOMIT_Element
	 * @access public
	 * @since 4/5/06
	 */
	function getElement () {
		return $this->_element;
	}
		
	/**
	 * Answers the organizer for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	public function getOrganizer () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$navOrg = $child;
				$navOrgObj = $this->_director->getSiteComponentFromXml($this->_asset, $navOrg);
			}
			$child = $child->nextSibling;
		}
		if (!isset($navOrgObj)) {
			$navOrgObj = $this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "NavOrganizer"), $this);
			$navOrgObj->updateNumRows('1');
			$navOrgObj->updateNumColumns('1');
		}
		return $navOrgObj;
		
		//throwError( new Error("Organizer not found", "XmlSiteComponents"));
	}
	
	/**
	 * Answers the nested menu for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 9/20/06
	 */
	function getNestedMenuOrganizer () {
		$menuOrgObj = null;
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'MenuOrganizer') {
				$menuOrg = $child;
				$menuOrgObj = $this->_director->getSiteComponentFromXml(
									$this->_asset, $menuOrg);
			}
			$child = $child->nextSibling;
		}
// 		if (!isset($menuOrgObj)) {
// 			$menuOrgObj = $this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "MenuOrganizer"));
// 		}
		return $menuOrgObj;
		
		//throwError( new Error("Organizer not found", "XmlSiteComponents"));
	}
	
	/**
	 * Set the organizer for this NavBlock
	 * 
	 * @param object Organizer $organizer
	 * @return voiid
	 * @access public
	 * @since 3/31/06
	 */
	public function setOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		$orgElement = $organizer->getElement();
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$this->_element->replaceChild($orgElement, $child);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// organizer not found... create it
		$this->_element->appendChild($orgElement);
		$this->_saveXml();
	}
	
	/**
	 * Answers the target Id for all NavBlocks in the menu
	 * 
	 * @return string the target id
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		$menuOrg = $this->getParentComponent();
		return $menuOrg->getTargetId();
	}

	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
		return $visitor->visitNavBlock($this);
	}
	
	/**
	 * Make a child Menu nested
	 * 
	 * @param object MenuOrganizerSiteComponent $menuOrganizer
	 * @return string The Id of the original cell
	 * @access public
	 * @since 9/22/06
	 */
	function makeNested ( $menuOrganizer ) {
		// A cell will have no old parent if it is newly created.
		if ($oldParent = $menuOrganizer->getParentComponent()) {
			if (method_exists($oldParent, 'getCellForSubcomponent'))
				$oldCellId = $oldParent->getId()."_cell:".$oldParent->getCellForSubcomponent($menuOrganizer);
			else 
				$oldCellId = null;
			
			// If the siteComponent reports a parent, but really has not been
			// added as an xml child node of the parent, continue
			try {
				$oldParent->detatchSubcomponent($menuOrganizer);
			} catch (DOMIT_DOMException $e) {
				$oldCellId = null;
			}
		} else {
			$oldCellId = null;
		}
		
		$this->_element->appendChild($menuOrganizer->getElement());
		
		$this->_saveXml();
		
		
		// Ensure that any assets referenced in the XML are added to our asset.
		$childAssetIdsBelowSubcomponent = $menuOrganizer->_getAssetIdsBelowElement(
			$menuOrganizer->getElement());
		$idManager = Services::getService('Id');
		foreach ($childAssetIdsBelowSubcomponent as $idString) {
			$this->_asset->addAsset($idManager->getId($idString));
		}
		
		
		return $oldCellId;
	}
	
	/**
	 * Remove a subcomponent, but don't delete it from the director completely.
	 * This is used to remove nested menus.
	 * 
	 * @param object SiteComponent $subcomponent
	 * @return void
	 * @access public
	 * @since 9/22/06
	 */
	function detatchSubcomponent ( SiteComponent $subcomponent ) {
		$this->_element->removeChild($subcomponent->getElement());
		$this->_saveXml();
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
		foreach ($possibleDestinations as $id => $possibleDestination) {
			// Can only send to menus.
			if (preg_match('/^.*MenuOrganizerSiteComponent$/i', 
				get_class($possibleDestination))) 
			{
				// Filter out decendent menus as this would cause a cycle
				$parent = $possibleDestination->getParentComponent();
				$isDescendent = false;
				while ($parent) {
					if ($parent->getId() == $this->getId()) {
						$isDescendent = true;
						break;
					}
					$parent = $parent->getParentComponent();
				}
				
				if (!$isDescendent)
					$results[$id] = $possibleDestination;
			}
		}
		
		return $results;
	}
	
	/**
	 * Answer the NavOrganizer above this nav block.
	 * 
	 * @return object NavOrganizerSiteComponent
	 * @access public
	 * @since 7/27/06
	 */
	function getParentNavOrganizer () {
		$parent = $this->getParentComponent();
		if ($parent)
			return $parent->getParentNavOrganizer();
		else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer the menu for this component
	 * 
	 * @return object MenuOrganizerSiteComponent
	 * @access public
	 * @since 7/28/06
	 */
	function getMenuOrganizer () {
		return $this->getParentComponent();
	}
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		if (!is_null($this->getNestedMenuOrganizer()))
			return TRUE;
		else {
			$organizer = $this->getOrganizer();
			return $organizer->subMenuExists();
		}
	}
	
/*********************************************************
 * Private methods
 *********************************************************/
	
	/**
	 * Store changes to our asset's XML document
	 * We need to redefine this as the basic implementation was
	 * overridden by AssetBlockSiteComponent
	 * 
	 * @return void
	 * @access private
	 * @since 10/5/06
	 */
	function _saveXml () {
		printpre("<hr/><h2>Saving AssetXML for ".get_class($this)." ".$this->getId().": </h2>");
		print("<h3>Previous XML</h3>");
		$oldContent = $this->_asset->getContent();
		printpre(htmlentities($oldContent->asString()));
		print("<h3>New XML</h3>");
		$element = $this->getElement();
		printpre($element->ownerDocument->toNormalizedString(true));
// 		exit;
		
		$this->_asset->updateContent(
			Blob::fromString(
				$element->ownerDocument->toNormalizedString()));
	}
}

?>