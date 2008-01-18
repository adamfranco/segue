<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteComponent.abstract.php,v 1.5 2008/01/18 21:39:07 adamfranco Exp $
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
 * @version $Id: SiteComponent.abstract.php,v 1.5 2008/01/18 21:39:07 adamfranco Exp $
 */
interface SiteComponent {
	
	/**
	 * Answer the displayName
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getDisplayName () ;
		
	/**
	 * Answer the Id
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	public function getId () ;
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	public function getComponentClass ();
	
	/**
	 * Answer true if this component is active
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/31/06
	 */
	public function isActive () ;
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @param boolean $inMenu		This should be moved to another method at some point.
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) ;
	
	/**
	 * Answer the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 1/17/07
	 */
	public function showDisplayNames ();
	
	/**
	 * change the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $showDisplayNames true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	public function updateShowDisplayNames ( $showDisplayNames );
	
	/**
	 * Answer true if the display name should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	public function showDisplayName ();
	
	/**
	 * Answer the setting of 'showHistory' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 1/17/07
	 */
	public function showHistorySetting ();
	
	/**
	 * change the setting of 'showHistory' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $showHistory true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	public function updateShowHistorySetting ( $showHistory );
	
	/**
	 * Answer true if the history should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	public function showHistory ();
	
	/**
	 * Answer the setting of 'sortMethod' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used.
	 *
	 * Sort methods are 'custom', 'title_asc', 'title_desc',
	 * 'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc'
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/07
	 */
	public function sortMethodSetting ();
	
	/**
	 * change the setting of 'sortMethod' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param string  $sortMethod 'default', 'custom', 'title_asc', 'title_desc',
	 *		'create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc'
	 * @return void
	 * @access public
	 * @since 1/17/07
	 */
	public function updateSortMethodSetting ( $sortMethod );
	
	/**
	 * Answer the sort method for flow organizers within for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	public function sortMethod ();
	
	/**
	 * change the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @param mixed  $commentsEnabled true, false, or 'default'
	 * @return void
	 * @access public
	 * @since 7/20/07
	 */
	public function updateCommentsEnabled ( $commentsEnabled );

	/**
	 * Answer the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 7/20/07
	 */
	public function commentsEnabled ();
	
	/**
	 * Answer true if the comments should be shown for this component,
	 * taking into account its setting and those in the hierarchy above it.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/17/07
	 */
	public function showComments ();
	
	/**
	 * Answer the width of the component. The default is an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/07
	 */
	public function getWidth ();
	
	/**
	 * Set the width of the component. If an invalid value
	 * 
	 * @param string $width '100px', '50px', '100%', etc, OR and empty string
	 * @return void
	 * @access public
	 * @since 1/19/07
	 */
	public function updateWidth ($width);
	
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
	public function getVisibleDestinationsForPossibleAddition ();
}

?>