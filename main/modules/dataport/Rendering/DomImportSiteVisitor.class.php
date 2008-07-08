<?php
/**
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomImportSiteVisitor.class.php,v 1.20 2008/04/18 20:39:15 achapin Exp $
 */ 

require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");
require_once(MYDIR."/main/modules/media/MediaAsset.class.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
require_once(MYDIR."/main/library/Roles/SegueRoleManager.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(dirname(__FILE__)."/DomAgentImporter.class.php");

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
 * @version $Id: DomImportSiteVisitor.class.php,v 1.20 2008/04/18 20:39:15 achapin Exp $
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
		if (!is_null($mediaPath)) {
			if (!is_dir($mediaPath))
				throw new Exception("'$mediaPath' does not exist for import.");
			if (!is_readable($mediaPath))
				throw new Exception("'$mediaPath' is not readable for import.");
			$this->mediaPath = $mediaPath;
		}
		
		$this->doc = $sourceDoc;
		$this->xpath = new DOMXPath($this->doc);
		$this->director = $director;
		$this->menusForUpdate = array();
		$this->pluginsForUpdate = array();
		$this->admins = array();
		$this->importRoles = false;
		$this->makeUserAdmin = false;
		$this->importComments = true;
		
		$this->agentImporter = new DomAgentImporter($this->doc);
	}
	
	/**
	 * @var int $mediaQuota;  
	 * @access private
	 * @since 3/21/08
	 */
	private $mediaQuota;
	
	/**
	 * Add an agent who should be a an administer of the new site. This method
	 * should be called before importing a site for it to have an effect.
	 * 
	 * @param object Id $agentId
	 * @return void
	 * @access public
	 * @since 1/25/08
	 */
	public function addSiteAdministrator (Id $agentId) {
		$this->admins[] = $agentId;
	}
	
	/**
	 * Make the current user a site administrator. This is needed if the current 
	 * user isn't a system-wide admin and they didn't add themselves as an administrator.
	 *
	 * @return void
	 * @access public
	 * @since 1/28/08
	 */
	public function makeUserSiteAdministrator () {
		$this->makeUserAdmin = true;
	}
	
	/**
	 * Disable importing of comments
	 *
	 * @return void
	 * @access public
	 * @since 1/28/08
	 */
	public function disableCommentImport () {
		$this->importComments = false;
	}
	
	/**
	 * Enable importing of permissions.
	 *
	 * @return void
	 * @access public
	 * @since 1/25/08
	 */
	public function enableRoleImport () {
		$this->importRoles = true;
	}
	
	/**
	 * Enable usage of a status indicator.
	 * 
	 * @return void
	 * @access public
	 * @since 3/24/08
	 */
	public function enableStatusOutput () {
		$this->status = new StatusStars(_("Importing Site"));
		$elements = $this->xpath->query('//SiteNavBlock | //NavBlock | //Block | //FixedOrganizer | //FlowOrganizer | //MenuOrganizer | //Comment');
		$this->status->initializeStatistics($elements->length);
	}
	
	/**
	 * Start the import as a new site for the slot given.
	 * 
	 * @param string $slotShortname
	 * @return object SiteNavBlockSiteComponent
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
		
		return $site;
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
		
		// Store the media quota if it exists
		if ($siteElement->hasAttribute('mediaQuota'))
			$this->mediaQuota = $siteElement->getAttribute('mediaQuota');
		else
			$this->mediaQuota = null;
		
		$site = $this->createComponent($siteElement, null);
		
		try {		
			// Apply the theme
			$this->applyTheme($site);
			
			$roleMgr = SegueRoleManager::instance();
			$adminRole = $roleMgr->getRole('admin');
			
			if ($this->makeUserAdmin)
				$adminRole->applyToUser($site->getQualifierId(), true);
			
			// Give the admin role to others specified
			foreach ($this->admins as $agentId)
				$adminRole->apply($agentId, $site->getQualifierId(), true);
			
			$this->importComponent($siteElement, $site);
			$this->updateMenuTargets();
			$this->updateStoredIds();
		} catch (Exception $e) {
			// Ensure that we don't have a partially created site floating out there.
			$this->director->deleteSiteComponent($site);
			throw $e;
		}
		return $site;
	}
	
	/**
	 * Answer the media quota from the import
	 *
	 * @return int
	 * @access public
	 * @since 3/21/08
	 */
	public function getMediaQuota () {
		if (!isset($this->mediaQuota)) {
			$elements = $this->xpath->evaluate('/Segue2/SiteNavBlock');
			if (!$elements->length === 1)
				throw new Exception("Import source has ".$elements->length." SiteNavBlock elements. There must be one and only one for importSite().");
			$siteElement = $elements->item(0);
			
			// Store the media quota if it exists
			if ($siteElement->hasAttribute('mediaQuota'))
				$this->mediaQuota = $siteElement->getAttribute('mediaQuota');
			else
				$this->mediaQuota = null;
		}
		
		return $this->mediaQuota;
	}
	
	/**
	 * Apply the theme if one is defined.
	 * 
	 * @param object SiteNavBlockSiteComponent $site
	 * @return void
	 * @access protected
	 * @since 6/6/08
	 */
	protected function applyTheme (SiteNavBlockSiteComponent $site) {
		// Get the theme specified in the source
		$themeElements = $this->xpath->evaluate('/Segue2/SiteNavBlock/theme');
		if ($themeElements->length) {
			$themeElement = $themeElements->item(0);
			
			$themeMgr = Services::getService("GUIManager");
				
			try {
				$theme = $themeMgr->getTheme($themeElement->getAttribute("id"));
			} catch (UnknownIdException $e) {
				return null; // Give up and use default theme.
			}
			
			if ($theme->supportsOptions()) {
				$optSession = $theme->getOptionsSession();
				
				$optionChoices = $this->xpath->evaluate('./theme_option_choice', $themeElement);
				foreach ($optionChoices as $choiceElement) {
					$this->applyThemeOption($optSession, $choiceElement); 
				}
			}
											
			// update theme with options from source
			$site->updateTheme($theme);	
		}
	}
	
	/**
	 * Apply a theme option to an option Session
	 * 
	 * @param object Harmoni_Gui2_ThemeOptionsInterface $optSession
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 6/6/08
	 */
	protected function applyThemeOption (Harmoni_Gui2_ThemeOptionsInterface $optSession, DOMElement $element) {
		try {
			$option = $optSession->getOption($element->getAttribute("id"));
		} catch (UnknownIdException $e) {
			return; // just skip
		}
		
		try {
			$option->setValue($element->nodeValue);
		} catch (OperationFailedException $e) {
			return; // just skip
 		}
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
							$this->getSingleNode("./type/domain/text()", $element)->nodeValue,
							$this->getSingleNode("./type/authority/text()", $element)->nodeValue,
							$this->getSingleNode("./type/keyword/text()", $element)->nodeValue),
						$parentComponent);
		else
			$component = $this->director->createSiteComponent(
						new Type('segue', 'edu.middlebury', $element->nodeName),
						$parentComponent);
			
		if (isset($this->status))
			$this->status->updateStatistics();
		
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
		$this->applyMedia($siteComponent->getAsset(), $element);
		$this->applyPluginContent($siteComponent->getAsset(), $element);
		if ($this->importComments)
			$this->applyComments($siteComponent, $element);
		$this->applyTags($siteComponent, $element);
		$this->setAssetAuthorship($siteComponent->getAsset(), $element);
		$this->setAssetDates($siteComponent->getAsset(), $element);
		
		$this->applyRoles($siteComponent, $element);
		
		$this->applyBlockDisplayTypes($siteComponent, $element);
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
		
		try {
			$nestedMenuElement = $this->getSingleChild('MenuOrganizer', $element);
		} catch (MissingNodeException $e) {
		}
		if (isset($nestedMenuElement)) {
			$menu = $this->createComponent($nestedMenuElement, $siteComponent);
			$siteComponent->makeNested($menu);
			$this->importComponent($nestedMenuElement, $menu);
		}
		
		$this->setAssetAuthorship($siteComponent->getAsset(), $element);
		$this->setAssetDates($siteComponent->getAsset(), $element);
		
		$this->applyRoles($siteComponent, $element);
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
		
		$element = $this->getElementForNewId($siteComponent->getId());
		$this->applyMenuDisplayType($siteComponent, $element);
		
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
		if ($element->hasAttribute('showDates'))
			$siteComponent->updateShowDatesSetting($element->getAttribute('showDates'));
		if ($element->hasAttribute('showAttribution'))
			$siteComponent->updateShowAttributionSetting($element->getAttribute('showAttribution'));
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
	
	/**
	 * Update all Ids that may be stored in the plugins of the site in links or
	 * content to the new ids imported.
	 * 
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function updateStoredIds () {
		$idMap = $this->getIdMap();
		while (count($this->pluginsForUpdate)) {
			$plugin = array_pop($this->pluginsForUpdate);
			try {
				$plugin->replaceIds($idMap);
			} catch (UnimplementedException $e) {
			}
			if ($plugin->supportsVersioning()) {
				try {
					foreach ($plugin->getVersions() as $version) {
						$version->replaceIds($idMap);
					}
				} catch (UnimplementedException $e) {
				}
			}
		}
	}
	
	/**
	 * Apply the plugin content and history where applicable
	 * 
	 * @param Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function applyPluginContent (Asset $asset, DOMElement $element) {
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($asset);
		if ($plugin->supportsVersioning()) {
			$this->applyPluginHistory($plugin, $element);
			$this->applyCurrentPluginVersion($plugin, $element);			
		} else {
			$this->applyUnversionedPluginContent($plugin, $element);
		}
		$this->pluginsForUpdate[] = $plugin;
	}
	
	/**
	 * Directly set the plugin's content if it does not support versioning.
	 * 
	 * @param object SeguePluginsAPI $plugin
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function applyUnversionedPluginContent (SeguePluginsAPI $plugin, DOMElement $element) {
		$plugin->setContent(
				$this->getStringValue(
					$this->getSingleElement('./currentContent/content', $element)));
		$plugin->setRawDescription(
				$this->getStringValue(
					$this->getSingleElement('./currentContent/rawDescription', $element)));
	}
	
	/**
	 * Apply the current version element data to the plugin as the current version.
	 * 
	 * @param object SeguePluginsAPI $plugin
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function applyCurrentPluginVersion (SeguePluginsAPI $plugin, DOMElement $element) {
		$versionElement = $this->getSingleElement('./currentVersion/child::node()', $element);
		$doc = new Harmoni_DOMDocument;
		$doc->appendChild($doc->importNode($versionElement, true));
		try {
			$plugin->applyVersion($doc);
		} catch (InvalidVersionException $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
			printpre($e->getMessage());
		}
	}
	
	/**
	 * Apply the historical versions to the plugin.
	 * 
	 * @param object SeguePluginsAPI $plugin
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function applyPluginHistory (SeguePluginsAPI $plugin, DOMElement $element) {
		$entries = $this->xpath->query('./history/entry', $element);
		foreach ($entries as $entry) {
			$this->addPluginHistoryEntry($plugin, $entry);
		}
	}
	
	/**
	 * Apply a single history entry to the plugin's history. 
	 * 
	 * @param SeguePluginsAPI $plugin
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function addPluginHistoryEntry (SeguePluginsAPI $plugin, DOMElement $element) {
		foreach ($element->childNodes as $child) {
			if ($child->nodeType == XML_ELEMENT_NODE && $child->nodeName != 'comment') {
				$doc = new Harmoni_DOMDocument;
				$doc->appendChild($doc->importNode($child, true));
				break;
			}
		}
		if (!isset($doc))
			throw new Exception("No version found.");
		
		$comment = $this->getPluginHistoryComment($element);
		$timestamp = $this->getPluginHistoryTimestamp($element);
		$agentId = $this->getPluginHistoryAgentId($element);
		
		$plugin->importVersion($doc, $agentId, $timestamp, $comment);
	}
	
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return string
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryComment (DOMElement $element) {
		return $this->getStringValue($this->getSingleElement('./comment', $element));
	}
	
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return object DateAndTime
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryTimestamp (DOMElement $element) {
		return DateAndTime::fromString($element->getAttribute('time_stamp'));
	}
	
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return object Id
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryAgentId (DOMElement $element) {
		return $this->getAgentId($element->getAttribute("agent_id"));
	}
	
	/**
	 * Apply the files to a block
	 * 
	 * @param Asset $contentAsset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function applyMedia (Asset $contentAsset, DOMElement $element) {
		$mediaElements = $this->xpath->query("./attachedMedia/mediaAsset", $element);
		foreach ($mediaElements as $mediaElement)
			$this->addMedia($contentAsset, $mediaElement);
	}
	
	/**
	 * Add a Media File
	 * 
	 * @param Asset $contentAsset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function addMedia (Asset $contentAsset, DOMElement $element) {
		$asset = MediaAsset::createForContentAsset($contentAsset);
		$element->setAttribute('new_id', $asset->getId()->getIdString());
		$asset->updateDisplayName($this->getStringValue($this->getSingleChild('displayName', $element)));
		$asset->updateDescription($this->getStringValue($this->getSingleChild('description', $element)));
		
		$fileElements = $this->xpath->query("./file", $element);
		foreach ($fileElements as $fileElement)
			$this->addFileRecord($asset, $fileElement);
		
		$dcElements = $this->xpath->query("./dublinCore", $element);
		foreach ($dcElements as $dcElement)
			$this->addDublinCoreRecord($asset, $dcElement);
		
		$this->setAssetAuthorship($asset, $element);
		$this->setAssetDates($asset, $element);
	}
	
	/**
	 * Add a File Record to a media Asset.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function addFileRecord (Asset $asset, DOMElement $element) {
		if (!isset($this->mediaPath))
			throw new OperationFailedException("Trying to import a file, but no media path is specified.");
			
		$idManager = Services::getService("Id");
		$record = $asset->createRecord($idManager->getId("FILE"));
		$element->setAttribute('new_id', $record->getId()->getIdString());
		
		$name = $this->getStringValue($this->getSingleElement('./name', $element));
		$filePath = $this->mediaPath."/".$this->getStringValue($this->getSingleElement('./path', $element));
		if ($element->hasAttribute('mimetype'))
			$mimeType = $element->getAttribute('mimetype');
		
		// If we weren't passed a mime type or were passed the generic
		// application/octet-stream type, see if we can figure out the
		// type.
		if (!isset($mimeType) || !$mimeType || $mimeType == 'application/octet-stream') {
			$mime = Services::getService("MIME");
			$mimeType = $mime->getMimeTypeForFileName($name);
		}
		
		$parts = $record->getPartsByPartStructure($idManager->getId("FILE_DATA"));
		$part = $parts->next();
		$part->updateValue(file_get_contents($filePath));
		
		$parts = $record->getPartsByPartStructure($idManager->getId("FILE_NAME"));
		$part = $parts->next();
		$part->updateValue($name);
		
		$parts = $record->getPartsByPartStructure($idManager->getId("MIME_TYPE"));
		$part = $parts->next();
		$part->updateValue($mimeType);
		
		/*********************************************************
		 * Thumbnail Generation
		 *********************************************************/
		$imageProcessor = Services::getService("ImageProcessor");
					
		// If our image format is supported by the image processor,
		// generate a thumbnail.
		if ($imageProcessor->isFormatSupported($mimeType)) {
			$sourceData = file_get_contents($filePath);
			try {
				$thumbnailData = $imageProcessor->generateThumbnailData($mimeType, $sourceData);
			} catch (ImageProcessingFailedException $e) {
			}
		}
		
		$parts = $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_DATA"));
		$thumbDataPart = $parts->next();
		$parts = $record->getPartsByPartStructure($idManager->getId("THUMBNAIL_MIME_TYPE"));
		$thumbMimeTypePart = $parts->next();
		
		if (isset($thumbnailData) && $thumbnailData) {
			$thumbDataPart->updateValue($thumbnailData);
			$thumbMimeTypePart->updateValue($imageProcessor->getThumbnailFormat());
		}
		// just make our thumbnail results empty. Default icons will display
		// instead.
		else {
			$thumbDataPart->updateValue("");
			$thumbMimeTypePart->updateValue("NULL");
		}
	}
	
	/**
	 * Add a Dublin Core Record to a media Asset.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function addDublinCoreRecord (Asset $asset, DOMElement $element) {
		$idManager = Services::getService("Id");
		$record = $asset->createRecord($idManager->getId("dc"));
		$element->setAttribute('new_id', $record->getId()->getIdString());
		
		try {
			$value = String::fromString(HtmlString::getSafeHtml($asset->getDisplayName()));
			$id = $idManager->getId("dc.title");
			$this->updateSingleValuedPart($record, $id, $value);
		} catch (MissingNodeException $e) {}
		
		try {
			$value = String::fromString(HtmlString::getSafeHtml($asset->getDescription()));
			$id = $idManager->getId("dc.description");
			$this->updateSingleValuedPart($record, $id, $value);
		} catch (MissingNodeException $e) {}
		
		try {
			$valueElement = $this->getSingleElement('./creator', $element);
			if ($valueElement) {
				$value = String::fromString(HtmlString::getSafeHtml(
					$this->getStringValue($valueElement)));
				$id = $idManager->getId("dc.creator");
				$this->updateSingleValuedPart($record, $id, $value);
			}
		} catch (MissingNodeException $e) {}
		
		try {
			$valueElement = $this->getSingleElement('./source', $element);
			if ($valueElement) {
				$value = String::fromString(HtmlString::getSafeHtml(
					$this->getStringValue($valueElement)));
				$id = $idManager->getId("dc.source");
				$this->updateSingleValuedPart($record, $id, $value);
			}
		} catch (MissingNodeException $e) {}
		
		try {		
			$valueElement = $this->getSingleElement('./publisher', $element);
			if ($valueElement) {
				$value = String::fromString(HtmlString::getSafeHtml(
					$this->getStringValue($valueElement)));
				$id = $idManager->getId("dc.publisher");
				$this->updateSingleValuedPart($record, $id, $value);
			}
		} catch (MissingNodeException $e) {}
		
		try {
			$valueElement = $this->getSingleElement('./date', $element);
			if ($valueElement) {
				$value = DateAndTime::fromString($this->getStringValue($valueElement));
				$id = $idManager->getId("dc.date");
				$this->updateSingleValuedPart($record, $id, $value);
			}
		} catch (MissingNodeException $e) {}
	}
	
	/**
	 * Apply the comments to a block
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function applyComments (BlockSiteComponent $siteComponent, DOMElement $element) {
		$asset = $siteComponent->getAsset();
		$commentMgr = CommentManager::instance();
		$commentElements = $this->xpath->query('./comments/Comment', $element);
		foreach ($commentElements as $commentElement) {
			$comment = $commentMgr->createRootComment($asset, 
				new Type(
					$this->getSingleNode("./type/domain/text()", $commentElement)->nodeValue,
					$this->getSingleNode("./type/authority/text()", $commentElement)->nodeValue,
					$this->getSingleNode("./type/keyword/text()", $commentElement)->nodeValue));
			$this->applyCommentData($comment, $commentElement);
		}
	}
	
	/**
	 * Apply data to a comment
	 * 
	 * @param object CommentNode $comment
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/24/08
	 */
	protected function applyCommentData (CommentNode $comment, DOMElement $element) {
		$element->setAttribute('new_id', $comment->getId()->getIdString());
		
		$comment->updateSubject(
			$this->getStringValue(
				$this->getSingleChild('subject', $element)));
		
		$this->applyPluginContent($comment->getAsset(), $element);
		$this->applyMedia($comment->getAsset(), $element);
		
		$this->setAssetAuthorship($comment->getAsset(), $element);
		$this->setAssetDates($comment->getAsset(), $element);
		
		if (isset($this->status))
			$this->status->updateStatistics();
		
		// Replies
		$commentMgr = CommentManager::instance();
		$replyElements = $this->xpath->query('./replies/Comment', $element);
		foreach ($replyElements as $replyElement) {
			$reply = $commentMgr->createReply($comment->getId(), 
				new Type(
					$this->getSingleNode("./type/domain/text()", $replyElement)->nodeValue,
					$this->getSingleNode("./type/authority/text()", $replyElement)->nodeValue,
					$this->getSingleNode("./type/keyword/text()", $replyElement)->nodeValue));
			$this->applyCommentData($reply, $replyElement);
		}
	}

	/**
	 * Apply tags to a block
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 4/18/08
	 */
	protected function applyTags (BlockSiteComponent $siteComponent, DOMElement $element) {
		//$asset = $siteComponent->getAsset();
		$tagManager = Services::getService("Tagging");
		$idManager = Services::getService("Id");
		$item = HarmoniNodeTaggedItem::forId($siteComponent->getId(), 'segue');
		$tagElements = $this->xpath->query('./tags/tag', $element);
		
		foreach ($tagElements as $tagElement) {			
			$tag = new Tag ($tagElement->nodeValue);

			$date = DateAndTime::fromString($tagElement->getAttribute('create_date'));
			$agentId = $this->getAgentId($tagElement->getAttribute('agent_id'));
					
			$tag->tagItemForAgent($item, $agentId, $date);	
		}
	}
	
	
	/**
	 * Answer an Agent Id in the receiving system that corresponds to an id in the
	 * source system.
	 * 
	 * @param string $idString
	 * @return object Id
	 * @access protected
	 * @since 1/23/08
	 */
	protected function getAgentId ($idString) {
		ArgumentValidator::validate($idString, NonZeroLengthStringValidatorRule::getRule());
				
		return $this->agentImporter->getAgentId($idString);
	}
	
	
	
	/**
	 * Set the Authorship of an asset based on the agent id listed in its corresponding
	 * element. Extensions of this class may wish to override this method to do nothing,
	 * there-by making the authorship that of the user doing the import.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function setAssetAuthorship (Asset $asset, DOMElement $element) {
		if ($element->hasAttribute('create_agent')) {
			$createAgentId = $this->getAgentId($element->getAttribute('create_agent'));
			$asset->forceSetCreator($createAgentId);
		}
	}
	
	/**
	 * Set the creation and modification dates of an asset based on the dates listed 
	 * in its corresponding element. Extensions of this class may wish to override 
	 * this method to do nothing, there-by leaving the dates to be the time of the import.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function setAssetDates (Asset $asset, DOMElement $element) {
		if ($element->hasAttribute('create_date')) {
			$date = DateAndTime::fromString($element->getAttribute('create_date'));
			$asset->forceSetCreationDate($date);
		}
		
		if ($element->hasAttribute('modify_date')) {
			$date = DateAndTime::fromString($element->getAttribute('modify_date'));
			$asset->forceSetModificationDate($date);
		}
	}
	
	/**
	 * Apply any roles defined at this level.
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function applyRoles (BlockSiteComponent $siteComponent, DOMElement $element) {
		if ($this->importRoles) {
			$roleElements = $this->xpath->query('./roles/entry', $element);
			foreach ($roleElements as $roleElement)
				$this->applyRole($siteComponent, $roleElement);
		}
	}
	
	/**
	 * Apply a Role to a site component
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function applyRole (BlockSiteComponent $siteComponent, DOMElement $element) {
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getRole($element->getAttribute('role'));
		$role->apply(
			$this->getAgentId($element->getAttribute('agent_id')),
			$siteComponent->getQualifierId(), true);
	}
	
/*********************************************************
 * Utility Methods
 *********************************************************/
	
	/**
	 * Update a single-valued part
	 * 
	 * @param object Record $record
	 * @param object Id $partStructureId
	 * @param object $value
	 * @return void
	 * @access public
	 * @since 1/30/07
	 */
	function updateSingleValuedPart ( Record $record, Id $partStructureId, $value ) {
		if (is_object($value) && $value->asString()) {
			$parts = $record->getPartsByPartStructure($partStructureId);
			if ($parts->hasNext()) {
				$part = $parts->next();
				$part->updateValue($value);
			} else {
				$record->createPart($partStructureId, $value);
			}
		}
		
		// Remove existing parts
		else {
			$parts = $record->getPartsByPartStructure($partStructureId);
			while ($parts->hasNext()) {
				$part = $parts->next();
				$record->deletePart($part->getId());
			}
		}
	}
	
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
			throw new MissingNodeException("".$elements->length." elements found with nodeName '$nodeName'. Expecting one and only one.");
		
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
		$nodes = $this->xpath->evaluate($xpath, $element);
		for ($i = 0; $i < $nodes->length; $i++) {
			$node = $nodes->item($i);
			if ($node->nodeType == XML_ELEMENT_NODE) {
				if (isset($resElement))
					throw new Exception("2 elements (".get_class($resElement)." '".$resElement->nodeName."', ".get_class($node)." '".$node->nodeName."') found for xpath '$xpath'. Expecting one and only one.");
				$resElement = $node;
			}
		}
		
		if (!isset($resElement))
			throw new MissingNodeException("0 elements found for xpath '$xpath'. Expecting one and only one.");
		
		return $resElement;
	}
	
	/**
	 * Answer a single node of any type with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/30/08
	 */
	protected function getSingleNode ($xpath, DOMElement $element) {
		$nodes = $this->xpath->evaluate($xpath, $element);
		if ($nodes->length != 1)
			throw new Exception("".$nodes->length." nodes found for XPATH '$xpath'. Expecting one and only one.");
		
		return $nodes->item(0);
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
	
	/**
	 * Answer a map (associative array) in which the keys are old Ids and the values
	 * are the new ids.
	 * 
	 * @return array
	 * @access protected
	 * @since 1/24/08
	 */
	protected function getIdMap () {
		$idMap = array();
		$elements = $this->xpath->query("//*[@id and @new_id]");
		foreach ($elements as $element)
			$idMap[$element->getAttribute('id')] = $element->getAttribute('new_id');
		
		return $idMap;
	}
	
	/**
	 * Add the block display type and heading display type to a block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 6/9/08
	 */
	protected function applyBlockDisplayTypes (BlockSiteComponent $siteComponent, DOMElement $element) {
		if ($element->hasAttribute('blockDisplayType'))
			$siteComponent->setDisplayType($element->getAttribute('blockDisplayType'));
		if ($element->hasAttribute('headingDisplayType'))
			$siteComponent->setHeadingDisplayType($element->getAttribute('headingDisplayType'));
	}
	
	/**
	 * Add the menu display type to a menu
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 6/9/08
	 */
	protected function applyMenuDisplayType (MenuOrganizerSiteComponent $siteComponent, DOMElement $element) {
		if ($element->hasAttribute('menuDisplayType'))
			$siteComponent->setDisplayType($element->getAttribute('menuDisplayType'));
	}
}


/**
 * An exception for missing nodes
 * 
 * @since 1/30/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomImportSiteVisitor.class.php,v 1.20 2008/04/18 20:39:15 achapin Exp $
 */
class MissingNodeException
	extends Exception
{}

?>