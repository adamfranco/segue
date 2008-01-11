<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSiteComponent.abstract.php,v 1.3 2008/01/11 21:24:40 adamfranco Exp $
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
 * @version $Id: BlockSiteComponent.abstract.php,v 1.3 2008/01/11 21:24:40 adamfranco Exp $
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

}

?>