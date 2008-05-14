<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetBlockSiteComponent.class.php,v 1.19 2008/03/20 15:43:56 adamfranco Exp $
 */ 
require_once(dirname(__FILE__)."/../AbstractSiteComponents/BlockSiteComponent.abstract.php");

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
 * @version $Id: AssetBlockSiteComponent.class.php,v 1.19 2008/03/20 15:43:56 adamfranco Exp $
 */
class AssetBlockSiteComponent
	extends AssetSiteComponent
	implements BlockSiteComponent
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
	 * Delete any stored data needed as part of the delete process
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function deleteAndCleanUpData () {
		$repository = $this->_asset->getRepository();
		$repository->deleteAsset($this->_asset->getId());
	}
	
	/**
	 * Answer the DOMElement associated with this SiteComponent
	 * 
	 * @return object DOMElement
	 * @access public
	 * @since 4/5/06
	 */
	function getElement () {
		if (!isset($this->_element)) {
			$parentComponent = $this->getParentComponent();
			$parentElement = $parentComponent->getElement();
			$this->_element = $parentElement->ownerDocument->getElementByIdAttribute($this->getId());
		}
		return $this->_element;
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
		$id = $this->_asset->getId();
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
		return HtmlString::getSafeHtml($this->_asset->getDisplayName());
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
		$this->_asset->updateDisplayName(HtmlString::getSafeHtml($displayName));
	}
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDescription () {
		return HtmlString::getSafeHtml($this->_asset->getDescription());
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
		$this->_asset->updateDescription(HtmlString::getSafeHtml($description));
	}
	
	/**
	 * Answer the date at which this Component was created.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 1/11/08
	 */
	public function getCreationDate () {
		return $this->_asset->getCreationDate();
	}
	
	/**
	 * Answer the Id of the agent that created this component
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/18/08
	 */
	public function getCreator () {
		return $this->_asset->getCreator();
	}
	
	/**
	 * Answer the date at which this Component was last modified.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 1/11/08
	 */
	public function getModificationDate () {
		return $this->_asset->getModificationDate();
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
	 * Answer an OKI type that represents the content.
	 * 
	 * @return Type
	 * @access public
	 * @since 1/17/08
	 */
	public function getContentType () {
		return $this->_asset->getAssetType();
	}

	
	/**
	 * Answer the contentMarkup
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getContentMarkup () {
		$content = $this->_asset->getContent();
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
		$content = Blob::fromString($contentMarkup);
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
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
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
	function getParentComponent () {
		$parentAssets = $this->_asset->getParents();
		while ($parentAssets->hasNext()) {
			$parentAsset = $parentAssets->next();
			$parentAssetType = $parentAsset->getAssetType();
			if ($parentAssetType->getDomain() == 'segue') {
				$parentXMLDoc = $this->_director->getXmlDocumentFromAsset($parentAsset);
				$myElement = $parentXMLDoc->getElementByIdAttribute($this->getId());
				if (is_null($myElement)) {
// 					printpre($parentXMLDoc->toString(true));
					throw new Exception("Could not find an element for Block id '".$this->getId()."'");
				}
				$parentElement = $this->_director->_getParentWithId($myElement);
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
	function getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		$possibleDestinations = $this->_director->getVisibleComponents();
		$parent = $this->getParentComponent();
		foreach (array_keys($possibleDestinations) as $id) {
			if ($id == $parent->getId())
				continue;
			
			if (preg_match('/^.*(Menu|Flow)OrganizerSiteComponent$/i', 
				get_class($possibleDestinations[$id]))) 
			{
				$results[$id] = $possibleDestinations[$id];
			}
		}
		
		return $results;
	}
	
	/**
	 * Answer the kind of Gui Component to display: 
	 *		Block_Standard, Block_Sidebar, Block_Alert, Header, Footer
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getDisplayType () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('displayType'))
			return 'Block_Standard';
		
		return $element->getAttribute('displayType');
	}
	
	/**
	 * Set the Gui Component display type for this block, one of: 
	 * 		Block_Standard, Block_Sidebar, Block_Alert, Header, Footer
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setDisplayType ($displayType) {
		if (!in_array($displayType, array('Block_Standard', 'Block_Sidebar', 'Block_Alert', 'Header', 'Footer')))
			throw new InvalidArgumentException("'$displayType' is not one of Block_Standard, Block_Sidebar, Block_Alert, Header, Footer.");
			
		$element = $this->getElement();
		
		$element->setAttribute('displayType', $displayType);
		
		$this->_saveXml();
	}
	
	/**
	 * Answer the kind of Gui Component to display for the heading: 
	 *		Heading_1, Heading_2, Heading_3, Heading_Sidebar
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getHeadingDisplayType () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('headingDisplayType'))
			return 'Heading_3';
		
		return $element->getAttribute('headingDisplayType');
	}
	
	/**
	 * Set the Gui Component display type for the heading, one of: 
	 * 		Heading_1, Heading_2, Heading_3, Heading_Sidebar
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setHeadingDisplayType ($displayType) {
		if (!in_array($displayType, array('Heading_1', 'Heading_2', 'Heading_3', 'Heading_Sidebar')))
			throw new InvalidArgumentException("'$displayType' is not one of Heading_1, Heading_2, Heading_3, Heading_Sidebar.");
			
		$element = $this->getElement();
		
		$element->setAttribute('headingDisplayType', $displayType);
		
		$this->_saveXml();
	}
	
/*********************************************************
 * Private methods
 *********************************************************/
	
	/**
	 * Store changes to our asset's XML document. This asset's 'element'
	 * is actually in its parent component, so store the parent asset's xml.
	 * 
	 * @return void
	 * @access private
	 * @since 10/5/06
	 */
	function _saveXml () {
// 		printpre("<hr/><h2>Saving Parent AssetXML for ".get_class($this)." ".$this->getId().": </h2>");
// 		print("<h3>Previous XML</h3>");
		$parentComponent = $this->getParentComponent();
		$parentAsset = $parentComponent->getAsset();
// 		$oldContent = $parentAsset->getContent();
// 		printpre(htmlentities($oldContent->asString()));
// 		print("<h3>New XML</h3>");
		$element = $this->getElement();
// 		printpre(htmlentities($element->ownerDocument->saveXMLWithWhitespace()));
// 		exit;
		
		$parentAsset->updateContent(
			Blob::fromString(
				$element->ownerDocument->saveXMLWithWhitespace()));
	}

}

?>