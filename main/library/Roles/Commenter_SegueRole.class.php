<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Commenter_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueRole.abstract.php");

/**
 * The Comment_SegueRole also allows the adding of comments.
 * 
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Commenter_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */
class Commenter_SegueRole
	extends SegueRole
{
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function __construct () {
		parent::__construct();
		
		$idMgr = Services::getService("Id");
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.view"));
		
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.comment"));
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.view_comments"));
	}
	
	/**
	 * Answer an IdString that identifies this role.
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getIdString () {
		return 'commenter';
	}
	
	/**
	 * Answer the display name of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDisplayName () {
		return _("Commenter");
	}
	
	/**
	 * Answer a description of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDescription () {
		return _("This role allows the viewing of items as well as commenting in discussions.");
	}
}

?>