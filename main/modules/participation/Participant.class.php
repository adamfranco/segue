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

//require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * get information about a given agent's participation in a given site
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_Participant {

	/**
	 * Constructor
	 * 
	 * @param Participation_View $view
	 * @param Id $id
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (Participation_View $view, Id $participantId) {
		$this->_view = $view;
		$this->_id = $participantId;
	
	}
	
	/**
	 * @var  Participation_View $view
	 * @access private
	 * @since 1/23/09
	 */
	private $_view;
	
	/**
	 * @var  string $_id 
	 * @access private
	 * @since 1/23/09
	 */
	private $_id;

	/**
	 * get the id of a participant in the node
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getId () {		
		return $this->_id;
	}

	/**
	 * get the display name of a participant in the node
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getDisplayName () {	
		return $this->getAgent()->getDisplayName();
	}

	/**
	 * get all actions of a participant in a node
	 * 
	 * @return array of Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getActions () {
		$all_actions = $this->_view->getActions();
		$participantActions = array();
		
		foreach ($all_actions as $action) {
			$participationAction = $action->getParticipant($this->_id)->getId();
			if ($participationAction == $this->_id) {
				$participantActions[] = $action;			
			}
		}		
		return $participantActions;
	}
	
	/**
	 * get the number of actions of participant by category
	 * 
	 * @param string $category
	 * @param optional string $nodeId
	 * @return integer
	 * @access public
	 * @since 1/28/09
	 */
	public function getNumActionsByCategory ($category) {
		$actions = $this->getActions();
		$num = 0;
		
		foreach($actions as $action) {
			if ($action->getCategoryId() == $category) {
				$num++;
			}
			
		}
		return $num;
	}
	
	
	/**
	 * Give us our agent (maybe from cache)
	 * 
	 * @return object Agent
	 * @access private
	 * @since 1/26/09
	 */
	private function getAgent () {
		if (!isset($this->_agent)) {
			$agentManager = Services::getService("Agent");		
			$this->_agent = $agentManager->getAgent ($this->_id);
		}
		
		return $this->_agent;
	}
	
	/**
	 * @var object Agent $_agent;  
	 * @access private
	 * @since 1/26/09
	 */
	private $_agent;

}

?>