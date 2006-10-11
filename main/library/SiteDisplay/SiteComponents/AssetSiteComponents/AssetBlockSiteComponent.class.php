<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetBlockSiteComponent.class.php,v 1.3 2006/10/11 19:37:51 adamfranco Exp $
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
 * @version $Id: AssetBlockSiteComponent.class.php,v 1.3 2006/10/11 19:37:51 adamfranco Exp $
 */
class AssetBlockSiteComponent
	extends AssetSiteComponent
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
	 * Answer the DOMIT_Element associated with this SiteComponent
	 * 
	 * @return object DOMIT_Element
	 * @access public
	 * @since 4/5/06
	 */
	function &getElement () {
		$tmpDocument =& new DOMIT_Document();
		$element =& $tmpDocument->createElement($this->getComponentClass());
		$element->setAttribute('id', $this->getId());
		return $element;
	}
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 10/6/06
	 */
	function getComponentClass () {
		return 'Block';
	}
	
	/**
	 * Answer the Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getId () {
		$id =& $this->_asset->getId();
		return $id->getIdString();
	}
		
	/**
	 * Answer the displayName
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDisplayName () {
		return $this->_asset->getDisplayName();
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
		$this->_asset->updateDisplayName($displayName);
	}
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDescription () {
		return $this->_asset->getDescription();
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
		$this->_asset->updateDescription($description);
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
		throwError(new Error('unimplemented'));
	}

	
	/**
	 * Answer the contentMarkup
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getContentMarkup () {
		$content =& $this->_asset->getContent();
		return $content->asString();
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
		$content =& String::fromString($contentMarkup);
		$this->_asset->updateContent($content);
	}
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor, $inMenu = FALSE ) {
		if ($inMenu)
			return $visitor->visitBlockInMenu($this);
		return $visitor->visitBlock($this);
	}
	
	/**
	 * Answer the parent component
	 * 
	 * @return object SiteComponent
	 * @access public
	 * @since 4/10/06
	 */
	function &getParentComponent () {
		$parentAssets =& $this->_asset->getParents();
		while ($parentAssets->hasNext()) {
			$parentAsset =& $parentAssets->next();
			$parentAssetType =& $parentAsset->getAssetType();
			if ($parentAssetType->getDomain() == 'segue') {
				$parentXMLDoc =& $this->_director->getXmlDocumentFromAsset($parentAsset);
				$myElement =& $parentXMLDoc->getElementByID($this->getId(), false);
				$parentElement =& $this->_director->_getParentWithId($myElement);
				if ($parentElement)
					return $this->_director->getSiteComponentFromXml($parentAsset, $parentElement);
			}
		}		
		
		$null = null;
		return $null;
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		$possibleDestinations =& $this->_director->getVisibleComponents();
		$parent =& $this->getParentComponent();
		foreach (array_keys($possibleDestinations) as $id) {
			if ($id == $parent->getId())
				continue;
			
			if (preg_match('/^.*(Menu|Flow)OrganizerSiteComponent$/i', 
				get_class($possibleDestinations[$id]))) 
			{
				$results[$id] =& $possibleDestinations[$id];
			}
		}
		
		return $results;
	}

}

?>