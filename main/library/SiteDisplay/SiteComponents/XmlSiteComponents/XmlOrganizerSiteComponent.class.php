<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlOrganizerSiteComponent.class.php,v 1.2 2006/04/05 18:03:35 adamfranco Exp $
 */ 

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlOrganizerSiteComponent.class.php,v 1.2 2006/04/05 18:03:35 adamfranco Exp $
 */
class XmlOrganizerSiteComponent
	extends XmlSiteComponent
	// implements OrganizersiteComponent
{
	
	/**
	 * @var array $_childComponents;  
	 * @access private
	 * @since 4/4/06
	 */
	var $_childComponents = null;
	
	/**
	 * Answer the number of rows.
	 * 
	 * @return integer
	 * @access public
	 * @since 4/3/06
	 */
	function getNumRows () {
		if ($this->_element->hasAttribute('rows'))
			return $this->_element->getAttribute('rows');
		return 1;
	}
	
	/**
	 * Update the number of rows. The contents of this organizer may limit the
	 * ability to reduce the number of rows.
	 * 
	 * @param integer $newRows
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumRows ( $newRows ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}

	/**
	 * Answer the number of columns.
	 * 
	 * @return integer
	 * @access public
	 * @since 4/3/06
	 */
	function getNumColumns () {
		if ($this->_element->hasAttribute('cols'))
			return $this->_element->getAttribute('cols');
		return 1;
	}
	
	/**
	 * Update the number of columns. The contents of this organizer may limit the
	 * ability to reduce the number of columns.
	 * 
	 * @param integer $newColumns
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumColumns ( $newColumns ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the total number of cells in this organizer. (Some may be empty)
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getTotalNumberOfCells () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the number of cells in this organizer that are visible (some may
	 * be empty).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumberOfVisibleCells () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDirection () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @param string $direction
	 * @access public
	 * @since 3/31/06
	 */
	function updateDirection ( $direction ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the subcomponent located in organizer cell $i
	 * 
	 * @param integer $i
	 * @return object SiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function &getSubcomponentForCell ( $i ) {
		$childComponents =& $this->_getChildComponents();

		// return the subcomponent or null
		if (isset($childComponents[$i])) {
			return $childComponents[$i];
		} else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Load the child elements into an array from the data source
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/4/06
	 */
	function &_getChildComponents () {
		// load the data array
		if (!is_array($this->_childComponents)) {
			$this->_childComponents = array();
			
			$child =& $this->_element->firstChild;
			while ($child) {
				if ($child->nodeName == 'cell') {
					if ($child->firstChild) {
						$this->_childComponents[] =& $this->_director->getSiteComponent($child->firstChild);
					} else {
						$this->_childComponents[] = null;
					}
				}
				$child =& $child->nextSibling;
			}
		}
		return $this->_childComponents;
	}
}

?>