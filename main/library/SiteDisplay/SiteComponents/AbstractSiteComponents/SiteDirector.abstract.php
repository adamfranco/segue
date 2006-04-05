<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteDirector.abstract.php,v 1.2 2006/04/05 18:18:16 adamfranco Exp $
 */ 

/**
 * The SiteDirector handles the selection of active nodes and acts in the 
 * "Abstract Factor" pattern to create and provide-access to SiteComponents.
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteDirector.abstract.php,v 1.2 2006/04/05 18:18:16 adamfranco Exp $
 */
class SiteDirector {
		
	/**
	 * Answer the RootSiteComponent for the site
	 * 
	 * @param string $id
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function &getRootSiteComponent ( $id ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}

	/**
	 * Answer true if the node of id $id is active
	 * 
	 * @param string $id
	 * @return boolean
	 * @access public
	 * @since 4/4/06
	 */
	function isActive ( $id ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the component that has a particular Id
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access public
	 * @since 4/5/06
	 */
	function &getSiteComponentById ( $id ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
}

?>