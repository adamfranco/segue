<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.14 2008/03/25 21:01:03 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteNavBlockSiteComponent.abstract.php");

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.14 2008/03/25 21:01:03 adamfranco Exp $
 */
class AssetSiteNavBlockSiteComponent
	extends AssetNavBlockSiteComponent
	implements SiteNavBlockSiteComponent
{	

	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	function getComponentClass () {
		return 'SiteNavBlock';
	}
	
	/**
	 * Answers nothing because this is a top level nav
	 * 
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		// don't ask me for this... my destination is hard-coded.
		throwError( new Error("SiteNavBlocks do not have self-defined target_id's", "SiteComponents"));
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
		return $visitor->visitSiteNavBlock($this);
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
		return $results;
	}
	
	/**
	 * Answer the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 11/30/07
	 */
	function showDisplayNames () {
		$showDisplayNames = parent::showDisplayNames();
		if ($showDisplayNames === 'default')
			return true;
		else
			return $showDisplayNames;
	}
	
	/**
	 * Answer the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used.
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 11/30/07
	 */
	function commentsEnabled () {
		$commentsEnabled = parent::commentsEnabled();
		if ($commentsEnabled === 'default')
			return false;
		else
			return $commentsEnabled;
	}
	
	/**
	 * Answer the setting of 'showHistory' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 1/17/07
	 */
	function showHistorySetting () {
		$setting = parent::showHistorySetting();
		if ($setting === 'default')
			return false;
		else
			return $setting;
	}
	
	/**
	 * Answer the setting of 'showDates' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * Dates can be 'none', 'creation_date', 'modification_date', 'both'
	 * @access public
	 * @since 3/20/08
	 */
	function showDatesSetting () {
		$setting = parent::showDatesSetting();
		if ($setting === 'default')
			return 'none';
		else
			return $setting;
	}
	
	/**
	 * Answer the setting of 'showAttribution' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used.
	 *
	 * Show attributions settings are 'creator', 'last_editor', 'all_editors'
	 * 
	 * @return string
	 * @access public
	 * @since 3/20/08
	 */
	function showAttributionSetting () {
		$setting = parent::showAttributionSetting();
		if ($setting === 'default')
			return 'none';
		else
			return $setting;
	}
	
	/*********************************************************
	 * The following methods support working with slots.
	 * Slots are syntactically-meaningful user-specified 
	 * identifiers for sites. Slots are only guarenteed to be
	 * unique within the scope of a given segue installation.
	 *
	 * Only site nodes can have slots.
	 *********************************************************/
	
	/**
	 * Answer the slot for a site id.
	 * 
	 * @return object Slot
	 * @access public
	 * @since 7/25/07
	 */
	function getSlot () {
		$slotManager = SlotManager::instance();
		return $slotManager->getSlotForSiteId($this->getId());
	}
	
	/*********************************************************
	 * Themes
	 *********************************************************/
	
	/**
	 * Answer the current theme.
	 * 
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/8/08
	 */
	public function getTheme () {
		$element = $this->getElement();
		
		$xpath = new DOMXpath($element->ownerDocument);
		$themeElements = $xpath->query('./theme', $element);
		
		$themeMgr = Services::getService("GUIManager");
		
		// Return the default theme
		if (!$themeElements->length)
			return $themeMgr->getDefaultTheme();
		
		
		// Set up the theme object
		$themeElement = $themeElements->item(0);
		try {
			$theme = $themeMgr->getTheme($themeElement->getAttribute('id'));
		} catch (UnknownIdException $e) {
			return $themeMgr->getDefaultTheme();
		}
		
		$options = trim($themeElement->nodeValue);
		if ($options) {
			try {
				$theme->setOptionsValue($options);
			} catch (OperationFailedException $e) {
			}
		}
		
		return $theme;
	}
	
	/**
	 * Update the Site to use the theme passed. Any options set on the theme
	 * will be remembered.
	 * 
	 * @param object Harmoni_Gui2_ThemeInterface $theme
	 * @return null
	 * @access public
	 * @since 5/8/08
	 */
	public function updateTheme (Harmoni_Gui2_ThemeInterface $theme) {
		$element = $this->getElement();
		$doc = $element->ownerDocument;
		
		// Delete any existing theme-settings
		$this->useDefaultTheme();
		
		// Add our new theme
		$themeElement = $element->appendChild($doc->createElement('theme'));
		$themeElement->setAttribute('id', $theme->getIdString());
		
		// If the theme supports settings, save them.
		if ($theme->supportsOptions()) {
			$optionsSession = $theme->getOptionsSession();
			// Only Store options if not using defaults
			if (!$optionsSession->usesDefaults()) {
				$themeElement->appendChild($doc->createCDATASection(
					$optionsSession->getOptionsValue()));
			}
		}
		
		$this->_saveXml();
	}
	
	/**
	 * Update the Site to use the default theme.
	 * 
	 * @return null
	 * @access public
	 * @since 5/8/08
	 */
	public function useDefaultTheme () {
		$element = $this->getElement();
		$xpath = new DOMXpath($element->ownerDocument);
		$themeElements = $xpath->query('./theme', $element);
		foreach ($themeElements as $themeElement) {
			$element->removeChild($themeElement);
		}
		
		$this->_saveXml();
	}
}

?>