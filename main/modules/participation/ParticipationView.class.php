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
require_once(MYDIR."/main/modules/participation/Participant.class.php");
require_once(MYDIR."/main/modules/participation/Participation_Action.interface.php");
require_once(MYDIR."/main/modules/participation/Participation_CreateAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(MYDIR."/main/modules/participation/ParticipationSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * get information about agent participation in a given site
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_View {
		

	/**
	 * Constructor
	 * 
	 * @param SiteComponent $node
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (SiteComponent $node) {
		$this->_node = $node;
		$this->_participants = array();
	}
	
	/**
	 * @var SiteComponent $_node
	 * @access private
	 * @since 1/23/09
	 */
	private $_node;
	
	/**
	 * @var  array $_participants
	 * @access private
	 * @since 1/28/09
	 */
	private $_participants;
	
	/**
	 * get all participants for node
	 * 
	 * @return array of Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipants () {
		$actions = $this->getActions();
		$participants = array();
		
		foreach ($actions as $action) {
			$participant = $action->getParticipant();				
			if (!isset($participants[$participant->getId()->getIdString()])) {
				$participants[$participant->getId()->getIdString()] = $participant;			
			}
		}
		return $participants;
	}

	/**
	 * get all actions in the node
	 * 
	 * @return array of Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getActions () {
		$visitor = new ParticipationSiteVisitor();
		$this->_node->acceptVisitor($visitor);
		return $visitor->getActions();
	}
	
	/**
	 * get a participant in the site
	 * 
	 * @param string $Id
	 * @return Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipant ($id) {
		ArgumentValidator::validate($id, StringValidatorRule::getRule());
		
		if (!isset($this->_participants[$id])) {
			$idMgr = Services::getService('Id');
			$this->_participants[$id] = new Participation_Participant($this, $idMgr->getId($id));
		}
		return $this->_participants[$id];
	}
	
	/**
	 * get an action in the site
	 * 
	 * @param string $Id
	 * @return Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getAction ($id) {
		if (preg_match('/^create::.+/', $id))
			return new Participation_CreateAction($this, $this->_node);
		else if (preg_match('/^history::.+/', $id))
			return new Participation_HistoryAction($this, $this->_node);
		else if (preg_match('/^comment::.+/', $id))
			return new Participation_HistoryAction($this, $this->_node);
		
		throw new UnknownIdException("Could not retrieve an action for id $id");
	}

}

?>