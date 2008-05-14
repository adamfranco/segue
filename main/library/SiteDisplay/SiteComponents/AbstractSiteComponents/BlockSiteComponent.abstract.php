<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSiteComponent.abstract.php,v 1.4 2008/01/18 21:39:07 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SiteComponent.abstract.php");

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
 * @version $Id: BlockSiteComponent.abstract.php,v 1.4 2008/01/18 21:39:07 adamfranco Exp $
 */
interface BlockSiteComponent
	extends SiteComponent
{
	
	/**
	 * Update the displayName
	 * 
	 * @param string $displayName
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateDisplayName ( $displayName ) ;
	
	/**
	 * Answer the description
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getDescription () ;
	
	/**
	 * Update the description
	 * 
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateDescription ( $description ) ;
	
	/**
	 * Answer the date at which this Component was created.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 1/11/08
	 */
	public function getCreationDate ();
	
	/**
	 * Answer the date at which this Component was last modified.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 1/11/08
	 */
	public function getModificationDate ();
	
	/**
	 * Answer an OKI type that represents the content.
	 * 
	 * @return Type
	 * @access public
	 * @since 1/17/08
	 */
	public function getContentType ();
	
	/**
	 * Answer the HTML markup that represents the title of the block. This may
	 * be the displayName alone, the displayName with additional HTML, or some
	 * other HTML representation of the title.
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getTitleMarkup () ;
	
	/**
	 * Answer the contentMarkup
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getContentMarkup () ;
	
	/**
	 * Update the contentMarkup
	 * 
	 * @param string $contentMarkup
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateContentMarkup ( $contentMarkup ) ;
	
	
	/**
	 * Answer the kind of Gui Component to display: 
	 *		Block_Standard, Block_Sidebar, Block_Alert, Header, Footer
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getDisplayType ();
	
	/**
	 * Set the Gui Component display type for this block, one of: 
	 * 		Block_Standard, Block_Sidebar, Block_Alert, Header, Footer
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setDisplayType ($displayType);
	
	/**
	 * Answer the kind of Gui Component to display for the heading: 
	 *		Heading_1, Heading_2, Heading_3, Heading_Sidebar
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getHeadingDisplayType ();
	
	/**
	 * Set the Gui Component display type for the heading, one of: 
	 * 		Heading_1, Heading_2, Heading_3, Heading_Sidebar
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setHeadingDisplayType ($displayType);
}

?>