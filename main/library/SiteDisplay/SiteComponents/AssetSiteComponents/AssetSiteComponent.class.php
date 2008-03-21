<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteComponent.class.php,v 1.22 2008/03/21 21:10:05 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteComponent.abstract.php");

/**
 * The site component is the root abstract class that all site components inherit
 * from.
 * 
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteComponent.class.php,v 1.22 2008/03/21 21:10:05 adamfranco Exp $
 */
abstract class AssetSiteComponent 
	implements SiteComponent
{

	/**
	 * Constructor
	 * 
	 * @param object XmlSiteDirector $director
	 * @param object DOMElement $element
	 * @return object XmlSiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function __construct ( AssetSiteDirector $director, Asset $asset, $element) {
		ArgumentValidator::validate($element, OptionalRule::getRule(
			ExtendsValidatorRule::getRule('DOMElement')));
		
		$this->_director = $director;
		$this->_asset = $asset;
		$this->_element = $element;
	}
	
	/**
	 * Destructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/9/07
	 */
	public function __destruct () {
		
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
		if (isset($this->_childComponents))
			unset($this->_childComponents);
		
		$this->_director->clearDomCache();
	}
	
	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		
	}
	
	/**
	 * Delete any stored data needed as part of the delete process
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function deleteAndCleanUpData () {
		
	}
	
	/**
	 * Answer the asset that corresponds to this block
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/12/07
	 */
	function getAsset () {
		return $this->_asset;
	}
	
	/**
	 * Answer the id of authorization qualifier that corresponds to this component.
	 * 
	 * @return object Id
	 * @access public
	 * @since 2/28/07
	 */
	function getQualifierId () {
		$asset = $this->getAsset();
		return $asset->getId();
	}
		
	/**
	 * Answer the Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getId () {
		if ($this->_element->hasAttribute('id')) {
			$assetId = $this->_asset->getId();
			return $assetId->getIdString()."----".$this->_element->getAttribute('id');
		} else
			throwError( new Error("No id available", "XmlSiteComponents"));
	}
	
	/**
	 * Answer this component's director
	 * 
	 * @return ref object SiteDirector
	 * @access public
	 * @since 4/18/06
	 */
	function getDirector () {
		return $this->_director;
	}
	
	/**
	 * Answer the DOMElement associated with this SiteComponent
	 * 
	 * @return object DOMElement
	 * @access public
	 * @since 4/5/06
	 */
	function getElement () {
		return $this->_element;
	}
	
	/**
	 * Answer the parent component
	 * 
	 * @return object SiteComponent
	 * @access public
	 * @since 4/10/06
	 */
	function getParentComponent () {
		$parentElement = $this->_director->_getParentWithId($this->getElement());
		if ($parentElement)
			return $this->_director->getSiteComponentFromXml($this->_asset, $parentElement);
		else if ($this->_asset)
			return $this->_director->getSiteComponentFromAsset($this->_asset);
		else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer true if this component is active
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/31/06
	 */
	function isActive () {
		return $this->_director->isActive($this->getId());
	}
	
	/**
	 * Answer the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 1/17/07
	 */
	function showDisplayNames () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('showDisplayNames'))
			return 'default';
		
		if ($element->getAttribute('showDisplayNames') == 'true')
			return true;
		else if ($element->getAttribute('showDisplayNames') == 'false')
			return false;
		else
			return 'default';
	}
	
	/**
	 * change the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $showDisplayNames true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	function updateShowDisplayNames ( $showDisplayNames ) {
		$element = $this->getElement();
		
		if ($showDisplayNames === true || $showDisplayNames === 'true')
			$element->setAttribute('showDisplayNames', 'true');
		else if ($showDisplayNames === false || $showDisplayNames === 'false')
			$element->setAttribute('showDisplayNames', 'false');
		else
			$element->setAttribute('showDisplayNames', 'default');
		
		$this->_saveXml();
	}
	
	/**
	 * Answer true if the display name should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	function showDisplayName () {
		if ($this->showDisplayNames() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->showDisplayName();
			// Base case if none is specified anywhere in the hierarchy
			else
				return true;
		} else {
			return $this->showDisplayNames();
		}
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
		$element = $this->getElement();
		
		if (!$element->hasAttribute('showHistory'))
			return 'default';
		
		if ($element->getAttribute('showHistory') == 'true')
			return true;
		else if ($element->getAttribute('showHistory') == 'false')
			return false;
		else
			return 'default';
	}
	
	/**
	 * change the setting of 'showHistory' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $showHistory true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	function updateShowHistorySetting ( $showHistory ) {
		$element = $this->getElement();
		
		if ($showHistory === true || $showHistory === 'true')
			$element->setAttribute('showHistory', 'true');
		else if ($showHistory === false || $showHistory === 'false')
			$element->setAttribute('showHistory', 'false');
		else
			$element->setAttribute('showHistory', 'default');
		
		$this->_saveXml();
	}
	
	/**
	 * Answer true if the history should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	function showHistory () {
		if ($this->showHistorySetting() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->showHistory();
			// Base case if none is specified anywhere in the hierarchy
			else
				return false;
		} else {
			return $this->showHistorySetting();
		}
	}
	
	/**
	 * Answer the setting of 'sortMethod' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used.
	 *
	 * Sort methods are 'custom', 'title_asc', 'title_desc',
	 * 'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc'
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/07
	 */
	function sortMethodSetting () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('sortMethod'))
			return 'default';
		
		return $element->getAttribute('sortMethod');
	}
	
	/**
	 * change the setting of 'sortMethod' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param string  $sortMethod 'default', 'custom', 'title_asc', 'title_desc',
	 *		'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	function updateSortMethodSetting ( $sortMethod ) {
		$methods = array('default', 'custom', 'title_asc', 'title_desc', 'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc');
		if (!in_array($sortMethod, $methods))
			throw new InvalidArgumentException("Invalid sort method, '$sortMethod', not one of '".implode("', ", $methods)."'.");
		$element = $this->getElement();
		
		$element->setAttribute('sortMethod', $sortMethod);
		
		$this->_saveXml();
	}
	
	/**
	 * Answer the sort method for flow organizers within for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	function sortMethod () {
		if ($this->sortMethodSetting() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->sortMethod();
			// Base case if none is specified anywhere in the hierarchy
			else
				return 'custom';
		} else {
			return $this->sortMethodSetting();
		}
	}
	
	/**
	 * change the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $commentsEnabled true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 7/20/07
	 */
	function updateCommentsEnabled ( $commentsEnabled ) {
		$element = $this->getElement();
		
		if ($commentsEnabled === true || $commentsEnabled === 'true')
			$element->setAttribute('commentsEnabled', 'true');
		else if ($commentsEnabled === false || $commentsEnabled === 'false')
			$element->setAttribute('commentsEnabled', 'false');
		else
			$element->setAttribute('commentsEnabled', 'default');
		
		$this->_saveXml();
	}

	/**
	 * Answer the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 7/20/07
	 */
	function commentsEnabled () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('commentsEnabled'))
			return 'default';
		
		if ($element->getAttribute('commentsEnabled') === 'true')
			return true;
		else if ($element->getAttribute('commentsEnabled') === 'false')
			return false;
		else
			return 'default';
	}
	
	/**
	 * Answer true if the comments should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	function showComments () {
		if ($this->commentsEnabled() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->showComments();
			// Base case if none is specified anywhere in the hierarchy
			else
				return false;
		} else {
			return $this->commentsEnabled();
		}
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
		$element = $this->getElement();
		
		if (!$element->hasAttribute('showDates'))
			return 'default';
		
		return $element->getAttribute('showDates');
	}

	/**
	 * change the setting of 'showDates' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param string  $showDates 'default', 'none', 'creation_date', 'modification_date',
	 *	'both'
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	function updateShowDatesSetting ( $showDates ) {
		$element = $this->getElement();
		
		$dates = array('default', 'none', 'creation_date', 'modification_date', 'both');
		if (!in_array($showDates, $dates))
			throw new InvalidArgumentException("Invalid date setting, '$showDates', not one of '".implode("', ", $dates)."'.");
		$element = $this->getElement();
		
		$element->setAttribute('showDates', $showDates);
		
		$this->_saveXml();
	}

	/**
	 * Answer the date setting to be shown for this component.
	 * taking into account its settings and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	public function showDates () {
		if ($this->showDatesSetting() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->showDates();
			// Base case if none is specified anywhere in the hierarchy
			else
				return 'none';
		} else {
			return $this->showDatesSetting();
		}		
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
		$element = $this->getElement();
		
		if (!$element->hasAttribute('showAttribution'))
			return 'default';
		
		return $element->getAttribute('showAttribution');
	}
	
	/**
	 * change the setting of 'showAttribution' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param string  $showAttribution 'none' 'default', 'creator', 'both'\
	 * 'last_editor', 'all_editors'
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	function updateShowAttributionSetting ( $showAttribution ) {
		$attributions = array('default', 'none', 'creator', 'last_editor', 'both', 'all_editors');
		if (!in_array($showAttribution, $attributions))
			throw new InvalidArgumentException("Invalid attribution, '$showAttribution', not one of '".implode("', ", $attributions)."'.");
		$element = $this->getElement();
		
		$element->setAttribute('showAttribution', $showAttribution);
		
		$this->_saveXml();
	}
	
	/**
	 * Answer the attribution to show for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	function showAttribution () {
		if ($this->showAttributionSetting() === 'default') {
			$parent = $this->getParentComponent();
			
			if ($parent)
				return $parent->showAttribution();
			// Base case if none is specified anywhere in the hierarchy
			else
				return 'none';
		} else {
			return $this->showAttributionSetting();
		}
	}
	
	/**
	 * Answer the width of the component. The default is an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/07
	 */
	function getWidth () {
		$element = $this->getElement();
		if ($element->hasAttribute('width'))
			return $element->getAttribute('width');
		else
			return '';
	}
	
	/**
	 * Set the width of the component. If an invalid value
	 * 
	 * @param string $width '100px', '50px', '100%', etc, OR and empty string
	 * @return void
	 * @access public
	 * @since 1/19/07
	 */
	function updateWidth ($width) {
		$element = $this->getElement();
		if (preg_match('/^[0-9]+(px|%)$/i', $width))
			$element->setAttribute('width', $width);
		else
			$element->removeAttribute('width');
		
		$this->_saveXml();
	}
	
/*********************************************************
 * Private methods
 *********************************************************/
	
	/**
	 * Store changes to our asset's XML document
	 * 
	 * @return void
	 * @access private
	 * @since 10/5/06
	 */
	function _saveXml () {
// 		printpre("<hr/><h2>Saving AssetXML for ".get_class($this)." ".$this->getId().": </h2>");
// 		HarmoniErrorHandler::printDebugBacktrace();
// 		print("<h3>Previous XML</h3>");
// 		$oldContent = $this->_asset->getContent();
// 		printpre(htmlentities($oldContent->asString()));
// 		print("<h3>New XML</h3>");
		$element = $this->getElement();
// 		printpre(htmlentities($element->ownerDocument->saveXMLWithWhitespace()));
// 		exit;
		
		$this->_asset->updateContent(
			Blob::fromString(
				$element->ownerDocument->saveXMLWithWhitespace()));
		
		$this->clearDomCache();
	}
}

?>