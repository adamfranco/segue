<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteComponent.class.php,v 1.4 2006/04/10 19:51:20 adamfranco Exp $
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
 * @version $Id: XmlSiteComponent.class.php,v 1.4 2006/04/10 19:51:20 adamfranco Exp $
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
	function XmlSiteComponent ( &$director, &$element) {
		$this->_director =& $director;
		$this->_element =& $element;
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
	 * Answer the DOMIT_Element associated with this SiteComponent
	 * 
	 * @return object DOMIT_Element
	 * @access public
	 * @since 4/5/06
	 */
	function &getElement () {
		return $this->_element;
	}
	
	/**
	 * Answer the parent component
	 * 
	 * @return object SiteComponent
	 * @access public
	 * @since 4/10/06
	 */
	function &getParentComponent () {
		return $this->_director->getSiteComponent(
					$this->_director->_getParentWithId(
						$this->getElement()));
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
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay"));
	}
}

?>