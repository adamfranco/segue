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
	 * @param SiteNavBlockSiteComponent $site
	 * @param Id $id
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (SiteNavBlockSiteComponent $site, Id $participantId) {
		$this->_site = $site;
		$this->_id = $participantId;
	
	}
	
	/**
	 * @var  SiteNavBlockSiteComponent $_site
	 * @access private
	 * @since 1/23/09
	 */
	private $_site;
	
	/**
	 * @var  string $_id 
	 * @access private
	 * @since 1/23/09
	 */
	private $_id;

	/**
	 * get the id of a participant in the site
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getId () {		
		return $this->_id;
	}

	/**
	 * get the display name of a participant in the site
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getDisplayName () {	
		return $this->getAgent()->getDisplayName();
	}

	/**
	 * get all actions of a participant in a site
	 * 
	 * @return array of Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getActions () {
		$site_actions = new Participation_View($this->_site);
		$all_actions = $site_actions->getActions();
		$participantActions = array();
		
		foreach ($all_actions as $action) {
			try {
				$ActionParticipant = $action->getParticipant();	
			} catch (Exception $e) {
			
			}
			
			if ($ActionParticipant == $this) {
				$participantActions[] = $action;			
			}
		}		
		return $participantActions;
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