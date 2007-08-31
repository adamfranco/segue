<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavBlockSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/BlockSiteComponent.abstract.php");

/**
 * The NavBlock component is a hierarchal node that provides a gateway to a 
 * sub-level of the hierarchy.
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavBlockSiteComponent.abstract.php,v 1.2 2007/08/31 16:03:45 achapin Exp $
 */
interface NavBlockSiteComponent
	extends BlockSiteComponent
{
		
	/**
	 * Answer the organizer that arranges the decendents of this node (in the
	 * target cell).
	 * 
	 * @return object Organizer
	 * @access public
	 * @since 3/31/06
	 */
	public function getOrganizer () ;
	
	/**
	 * Set the organizer for this NavBlock
	 * 
	 * @param object Organizer $organizer
	 * @return voiid
	 * @access public
	 * @since 3/31/06
	 */
	public function setOrganizer ( FixedOrganizerSiteComponent $organizer ) ;
	
	/**
	 * Answer the target Id
	 * 
	 * @return object Id
	 * @access public
	 * @since 3/31/06
	 */
	public function getTargetId () ;
}

?>