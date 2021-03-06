<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllEditablePortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/AllVisiblePortalFolder.class.php");

/**
 * The PersonalPortalFolder contains all personal sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllEditablePortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
class AllEditablePortalFolder
	extends AllVisiblePortalFolder 
{
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("All Editable");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return _("All sites that you can edit. <br/>Note: First access may take over a minute. Subsequent access will be fast.");
	}
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return "all_editable";
	}
	
	/**
	 * Answer true if the edit controls should be displayed for the sites listed.
	 * If true, this can lead to slowdowns as authorizations are checked on large
	 * lists of large sites.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function showEditControls () {
		return true;
	}
		
	/**
	 * Answer true if this site should be included
	 * 
	 * @param object Id $id
	 * @return boolean
	 * @access protected
	 * @since 4/1/08
	 */
	protected function includeSite (Id $id) {
		// If the site isn't visible, don't bother checking edit AZs
		if (!parent::includeSite($id))
			return false;
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		if ($authZ->isUserAuthorizedBelow(
			$idManager->getId("edu.middlebury.authorization.modify"), $id))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>