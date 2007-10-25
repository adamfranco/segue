<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteComponent.class.php,v 1.10 2007/10/25 17:44:12 adamfranco Exp $
 */ 

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
 * @version $Id: XmlSiteComponent.class.php,v 1.10 2007/10/25 17:44:12 adamfranco Exp $
 */
class XmlSiteComponent 
	// implements SiteComponent
{

	/**
	 * Constructor
	 * 
	 * @param object XmlSiteDirector $director
	 * @param object Domit_Node $element
	 * @return object XmlSiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function XmlSiteComponent ( $director, $element) {
		$this->_director = $director;
		$this->_element = $element;
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
	 * Answer the Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getId () {
		if ($this->_element->hasAttribute('id'))
			return $this->_element->getAttribute('id');
		else
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
	 * Answer the parent component
	 * 
	 * @return object SiteComponent
	 * @access public
	 * @since 4/10/06
	 */
	function getParentComponent () {
		$parentElement = $this->_director->_getParentWithId($this->getElement());
		if ($parentElement)
			return $this->_director->getSiteComponent($parentElement);
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
		if (!$this->_element->hasAttribute('showDisplayNames'))
			return 'default';
		
		if ($this->_element->getAttribute('showDisplayNames') == 'true')
			return true;
		else if ($this->_element->getAttribute('showDisplayNames') == 'false')
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
		if ($showDisplayNames === true || $showDisplayNames === 'true')
			$this->_element->setAttribute('showDisplayNames', 'true');
		else if ($showDisplayNames === false || $showDisplayNames === 'false')
			$this->_element->setAttribute('showDisplayNames', 'false');
		else
			$this->_element->setAttribute('showDisplayNames', 'default');
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
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function acceptVisitor ( SiteVisitor $visitor ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay"));
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function getVisibleDestinationsForPossibleAddition () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay"));
	}
}

?>