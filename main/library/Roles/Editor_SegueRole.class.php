<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Editor_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
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
 * @version $Id: Editor_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */
class Editor_SegueRole
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
		
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.add_children"));
		
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.modify"));
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.delete"));
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.remove_children"));
	}
	
	/**
	 * Answer an IdString that identifies this role.
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getIdString () {
		return 'editor';
	}
	
	/**
	 * Answer the display name of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDisplayName () {
		return _("Editor");
	}
	
	/**
	 * Answer a description of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDescription () {
		return _("In addition to viewing, this role allows users to add, modify, and delete items.");
	}
}

?>