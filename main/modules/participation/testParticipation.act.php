<?php
/**
 * @since 1/23/09
 * @package segue.modules.partipation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

 require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
 require_once('ParticipationView.class.php');

/**
 * <##>
 * 
 * @since 1/23/09
 * @package segue.modules.partipation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
 
class testParticipationAction
	extends Action
{

	/**
	 * execute
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 1/23/09
	 */
	public function execute () {
		print "Testing participation view class...";
		$site = SiteDispatcher::getCurrentRootNode();
		
		// make sure url contains node id
		$currentNode = SiteDispatcher::getCurrentNode();
	//	printpre($currentNode);
		$participantId = 606;
		$nodeId = 511205;
				
		$view = new Participation_View($site);		
		
		//get participant
		$participant = $view->getParticipant($participantId);		
		$participantName = $participant->getDisplayName();
		$participantId = $participant->getId();
		
		printpre(get_class($participant));
		printpre($participantName);
		printpre($participantId);
		
		
		//get an action id for a site
		$action = $view->getAction("create::".$nodeId);
		printpre(get_class($action));
		printpre($action->getId());
	
		//get array of all actions on the site
		$all_actions = $view->getActions();
		printpre(get_class($all_actions));
		
		
		printpre("done");
		
		
		exit;
	}

	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/24/07
	 */
	public function isAuthorizedToExecute () {

		return true;
	}


}

?>