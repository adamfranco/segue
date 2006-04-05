<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
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
 * @version $Id: BlockSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */
class BlockSiteComponent
	extends SiteComponent
{
		
	/**
	 * Answer the displayName
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDisplayName () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDescription () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the contentMarkup
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getContentMarkup () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		return $visitor->visitBlock($this);
	}

}

?>