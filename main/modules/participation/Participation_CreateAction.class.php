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
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/participation/Participant.class.php");
require_once(dirname(__FILE__)."/Participation_ModAction.abstract.php");
 
/**
 * get info about create modification action
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_CreateAction
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
		return "create";
	}
	
	/**
	 * get timestamp of action (e.g. create, edit, comment...)
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/23/09
	 */
	public function getTimeStamp ()  {
		return $this->_node->getCreationDate();
	}
	
	/**
	 * get creator of action
	 * 
	 * @return Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipant ()  {	
		$director = SiteDispatcher::getSiteDirector();	
		
		$participant = new Participation_Participant(
			$director->getRootSiteComponent($this->_node->getId()), 
			$this->_node->getCreator());
				
		return $participant;
	}
	
	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getCategory () {
		
		return "Author";
	
	}
	
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getDescription ()  {
		
		return "content created.";
	
	}
	
	
}



?>