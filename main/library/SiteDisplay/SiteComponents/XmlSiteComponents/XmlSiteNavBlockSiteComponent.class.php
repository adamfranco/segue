<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlSiteNavBlockSiteComponent.class.php,v 1.2 2006/04/12 15:22:17 cws-midd Exp $
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
 * @version $Id: XmlSiteNavBlockSiteComponent.class.php,v 1.2 2006/04/12 15:22:17 cws-midd Exp $
 */
class XmlSiteNavBlockSiteComponent
	extends XmlNavBlockSiteComponent
	// implements SiteNavBlockSiteComponent
{	

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
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitSiteNavBlock($this);
	}
}

?>