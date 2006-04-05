<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */ 

/**
 * The SiteNavBlockSiteComponent
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNavBlockSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */
class SiteNavBlockSiteComponent 
	extends NavBlockSiteComponent 
{
		
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitSiteNavBlock($this);
	}
	
}

?>