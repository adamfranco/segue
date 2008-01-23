<?php
/**
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomImportSiteVisitor.class.php,v 1.1 2008/01/23 15:27:15 adamfranco Exp $
 */ 

/**
 * This importer will traverse an XML document that defines a site and will create
 * the corresponding site components in the Segue instance.
 * 
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomImportSiteVisitor.class.php,v 1.1 2008/01/23 15:27:15 adamfranco Exp $
 */
class DomImportSiteVisitor
	implements SiteVisitor
{
		
	/**
	 * Constructor
	 * 
	 * @param object DOMDocument $sourceDoc
	 * @param string $mediaPath
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 1/22/08
	 */
	public function __construct (DOMDocument $sourceDoc, $mediaPath, SiteDirector $director) {
		if (!is_dir($mediaPath))
			throw new Exception("'$mediaPath' does not exist for import.");
		if (!is_readable($mediaPath))
			throw new Exception("'$mediaPath' is not readable for import.");
		
		$this->doc = $sourceDoc;
		$this->xpath = new DOMXPath($this->doc);
		$this->mediaPath = $mediaPath;
		$this->director = $director;
	}
	
	/**
	 * Start the import as a new site for the slot given.
	 * 
	 * @param string $slotShortname
	 * @return void
	 * @access public
	 * @since 1/22/08
	 */
	public function importAtSlot ($slotShortname) {
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname($slotShortname);
		if ($slot->siteExists())
			throw new Exception("A site already exists for the '$slotShortname' slot. Cannot import.");
		
		$site = $this->importSite();
		$idMgr = Services::getService('Id');
		$slot->setSiteId($idMgr->getId($site->getId()));
	}
	
	/**
	 * Create a new Site and import the source data into it.
	 * 
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 1/22/08
	 */
	public function importSite () {
		$elements = $this->xpath->evaluate('/Segue2/SiteNavBlock');
		if (!$elements->length === 1)
			throw new Exception("Import source has ".$elements->length." SiteNavBlock elements. There must be one and only one for importSite().");
		$siteElement = $elements->item(0);
		
		$site = $this->createComponent($siteElement, null);
		$this->importComponent($siteElement, $site);
		$this->updateMenuTargets();
		return $site;
	}
	
	/**
	 * Import a SiteComponent defined by an XML element
	 * 
	 * @param object DOMElement $element
	 * @param optional object SiteComponent $parentComponent This should only be NULL for sites
	 * @return object SiteComponent
	 * @access protected
	 * @since 1/22/08
	 */
	protected function createComponent (DOMElement $element, $parentComponent = null) {
		ArgumentValidator::validate($parentComponent, 
			OptionalRule::getRule(ExtendsValidatorRule::getRule('SiteComponent')));
		if (is_null($parentComponent) && $element->nodeName != 'SiteNavBlock')
			throw new Exception('Only SiteNavBlocks can have no parentComponent passed');		
		
		if ($element->nodeName == 'Block')
			$component = $this->director->createSiteComponent(
						new Type(
							$this->getSingleElement("./type/domain/text()", $element)->nodeValue,
							$this->getSingleElement("./type/authority/text()", $element)->nodeValue,
							$this->getSingleElement("./type/keyword/text()", $element)->nodeValue),
						$parentComponent);
		else
			$component = $this->director->createSiteComponent(
						new Type('segue', 'edu.middlebury', $element->nodeName),
						$parentComponent);
				
		return $component;
	}
	
	/**
	 * Import data into an existing component.
	 * 
	 * @param object DOMElement $element
	 * @param object SiteComponent $siteComponent
	 * @return object SiteComponent
	 * @access protected
	 * @since 1/22/08
	 */
	protected function importComponent (DOMElement $element, SiteComponent $siteComponent) {
		if ($element->hasAttribute('new_id'))
			throw new Exception("The ".$element->nodeName." element with id '".$element->getAttribute('id')."' already has a new_id set.");
		
		$element->setAttribute('new_id', $siteComponent->getId());
		
		// Pass ourselves off to the component to traverse the hierarchy and set 
		// data from the source document.
		$siteComponent->acceptVisitor($this);
		
		return $siteComponent;
	}
	
/*********************************************************
 * Visiting methods
 *********************************************************/

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$element = $this->getElementForNewId($siteComponent->getId());
		$this->applyDisplayName($siteComponent, $element);
		$this->applyDescription($siteComponent, $element);
		$this->applyCommonProperties($siteComponent, $element);
		
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($siteComponent->getAsset());
		if ($plugin->supportsVersioning()) {
			$versionElement = $this->getSingleElement('./currentVersion/node()', $element);
			$doc = new DOMDocument;
			$doc->loadXML($versionElement->ownerDocument->saveXML($versionElement));
			$plugin->applyVersion($doc);
		} else {
			$plugin->setContent($this->getStringValue($this->getSingleElement('./currentContent/content', $element)));
		}
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$element = $this->getElementForNewId($siteComponent->getId());
		$this->applyDisplayName($siteComponent, $element);
		$this->applyDescription($siteComponent, $element);
		$this->applyCommonProperties($siteComponent, $element);
		
		$this->importComponent($this->getSingleChild('NavOrganizer', $element), $siteComponent->getOrganizer());
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->visitNavBlock($siteComponent);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		$element = $this->getElementForNewId($siteComponent->getId());
		$this->applyCommonProperties($siteComponent, $element);
		$this->applyOrganizerProperties($siteComponent, $element);
		$this->importOrganizerChildren($siteComponent, $element);
		
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		$this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		$element = $this->getElementForNewId($siteComponent->getId());
		$this->applyCommonProperties($siteComponent, $element);
		$this->applyOrganizerProperties($siteComponent, $element);
		
		if ($element->hasAttribute('overflowStyle'))
			$siteComponent->updateOverflowStyle($element->getAttribute('overflowStyle'));
		
		$this->importOrganizerChildren($siteComponent, $element);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		$this->visitFlowOrganizer($siteComponent);
		
		// Queue up the menu for target updating. This must happen after the rest of the
		// site is imported so that all new_ids are set.
		$this->menusForUpdate[] = $siteComponent;
	}
	
	
/*********************************************************
 * Data setting Methods
 *********************************************************/
	/**
	 * Apply a display name to an siteComponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function applyDisplayName (SiteComponent $siteComponent, DOMElement $element) {
		$siteComponent->updateDisplayName(
			$this->getStringValue(
				$this->getSingleChild('displayName', $element)));
		
	}
	
	/**
	 * Apply a description to an siteComponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function applyDescription (SiteComponent $siteComponent, DOMElement $element) {
		$siteComponent->updateDescription(
			$this->getStringValue(
				$this->getSingleChild('description', $element)));
		
	}
	
	/**
	 * Apply common properties a siteComponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function applyCommonProperties (SiteComponent $siteComponent, DOMElement $element) {
		if ($element->hasAttribute('showDisplayNames'))
			$siteComponent->updateShowDisplayNames($element->getAttribute('showDisplayNames'));
		if ($element->hasAttribute('showHistory'))
			$siteComponent->updateShowHistorySetting($element->getAttribute('showHistory'));
		if ($element->hasAttribute('sortMethod'))
			$siteComponent->updateSortMethodSetting($element->getAttribute('sortMethod'));
		if ($element->hasAttribute('commentsEnabled'))
			$siteComponent->updateCommentsEnabled($element->getAttribute('commentsEnabled'));
		if ($element->hasAttribute('width'))
			$siteComponent->updateWidth($element->getAttribute('width'));
	}
	
	/**
	 * Apply organizer properties a siteComponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function applyOrganizerProperties (OrganizerSiteComponent $siteComponent, DOMElement $element) {
		if ($element->hasAttribute('rows'))
			$siteComponent->updateNumRows($element->getAttribute('rows'));
		if ($element->hasAttribute('cols'))
			$siteComponent->updateNumColumns($element->getAttribute('cols'));
		if ($element->hasAttribute('direction'))
			$siteComponent->updateDirection($element->getAttribute('direction'));
	}
	
	/**
	 * Import organizer children
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function importOrganizerChildren (OrganizerSiteComponent $siteComponent, DOMElement $element) {
		$cells = $this->xpath->evaluate("./cell", $element);
		$i = 0;
		foreach ($cells as $cell) {
			$childElements = $this->xpath->evaluate("./Block | ./NavBlock | ./FixedOrganizer | ./FlowOrganizer | ./MenuOrganizer", $cell);
			if ($childElements->length > 1)
				throw new Exception("Found more than one child of an organizer cell.");
			
			if ($childElements->length == 1) {
				$child = $this->createComponent($childElements->item(0), $siteComponent);
				$siteComponent->putSubcomponentInCell($child, $i);
				$this->importComponent($childElements->item(0), $child);
			}
			
			$i++;
		}
	}
	
	/**
	 * Update all of the menu targets. This should be done once all components have
	 * been imported and their new_ids set.
	 * 
	 * @return void
	 * @access protected
	 * @since 1/22/08
	 */
	protected function updateMenuTargets () {
		while (count($this->menusForUpdate)) {
			$menu = array_pop($this->menusForUpdate);
			$menuElement = $this->getElementForNewId($menu->getId());
			$target = $menuElement->getAttribute('target_id');
			preg_match('/(.+)_cell:([0-9]+)/', $target, $matches);
			$targetElement = $this->getElementForId($matches[1]);
			$menu->updateTargetId($targetElement->getAttribute('new_id')."_cell:".$matches[2]);
		}
	}
	
/*********************************************************
 * Utility Methods
 *********************************************************/

	/**
	 * Answer the element for a newly created site component id.
	 * 
	 * @param string $newId
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getElementForNewId ($newId) {
		$elements = $this->xpath->query("//*[@new_id ='$newId']");
		if ($elements->length !== 1)
			throw new Exception("".$elements->length." elements found with newId = '$newId'. There must be one and only one.");
		
		return $elements->item(0);
	}
	
	/**
	 * Answer the element with an old site component id.
	 * 
	 * @param string $id
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getElementForId ($id) {
		$elements = $this->xpath->query("//*[@id ='$id']");
		if ($elements->length !== 1)
			throw new Exception("".$elements->length." elements found with id = '$id'. There must be one and only one.");
		
		return $elements->item(0);
	}
	
	/**
	 * Answer a single child element with the nodeName specified.
	 * 
	 * @param string $nodeName
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getSingleChild ($nodeName, DOMElement $element) {
		$elements = $this->xpath->evaluate("./".$nodeName, $element);
		if (!$elements->length === 1)
			throw new Exception("".$elements->length." elements found with nodeName '$nodeName'. Expecting one and only one.");
		
		return $elements->item(0);
	}
	
	/**
	 * Answer a single element with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getSingleElement ($xpath, DOMElement $element) {
		$elements = $this->xpath->evaluate($xpath, $element);
		if (!$elements->length === 1)
			throw new Exception("".$elements->length." elements found with nodeName '$nodeName'. Expecting one and only one.");
		
		return $elements->item(0);
	}
	
	/**
	 * Answer the string value of an element in any text or CDATA nodes.
	 * 
	 * @param DOMElement $element
	 * @return string
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getStringValue (DOMElement $element) {
		$value = '';
		foreach ($element->childNodes as $child) {
			switch ($child->nodeType) {
				case XML_TEXT_NODE:
				case XML_CDATA_SECTION_NODE:
					$value .= $child->nodeValue;
				case XML_COMMENT_NODE:
					break;
				default:
					throw new Exception("Found ".get_class($child).", expecting a text node or CDATA Section.");
			}
		}
		
		return $value;
	}
}

?>