<?php
/**
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Participation_ModAction.abstract.php");
 
 
/**
 * get info about last edited modification action
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_LastEditAction
	extends Participation_ModAction
{
		
	/**
	 * get id prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 1/23/09
	 */
	protected function getIdPrefix () {
		return "last_edit";
	}
	
	/**
	 * get timestamp of action (e.g. create, edit, comment...)
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/23/09
	 */
	public function getTimeStamp ()  {
		return $this->_node->getModificationDate();
	}
	
	/**
	 * get last editor of action
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipant ()  {
		throw new UnimplementedException();
	}

	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getCategory () {
		
		return "Editor";
	
	}
	
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getDescription ()  {
		
		return "last editor of this content.";
	
	}

	
}

?>