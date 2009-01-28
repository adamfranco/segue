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
		$participantId = "606";
		$nodeId = "511205";
				
		$view = new Participation_View($site);		

		//get an action id for a site
		print "<hr/>Action ID<hr/>";
		$action = $view->getAction("create::".$nodeId);
		printpre(get_class($action));
		printpre($action->getId());
		
		//get participant
		print "<hr/>Participant<hr/>";
		$participant = $view->getParticipant($participantId);		
		$participantName = $participant->getDisplayName();
		$participantId = $participant->getId();
		
		printpre(get_class($participant));
		printpre($participantName);
		printpre($participantId);
		

		//get all actions of a given participant		
		$participantId = 20217;
		print "<hr/>Participant (".$participantId.") Actions<hr/>";
		$idMgr = Services::getService('Id');			
		$agent = $idMgr->getId($participantId);		
// 		printpre($agent);
		
		$participant = new Participation_Participant($view, $agent);
		$participant2actions = $participant->getActions();
		printpre($participant->getDisplayName());	
		
		foreach ($participant2actions as $action) {
			printpre($action->getTargetDisplayName());
 			//printpre($action->getTimeStamp());
 			//printpre($action->getCategory());			
		}

		
		//get all participants in a site		
		print "<hr/>Participants<hr/>";
		$participants = $view->getParticipants();		
		foreach ($participants as $participant) {
			printpre($participant->getDisplayName());
		}
	
		//get array of all actions on the site
		print "<hr/>Actions<hr/>";
		$all_actions = $view->getActions();
		
		foreach ($all_actions as $action) {
			//if (get_class($action) == 'Participation_HistoryAction') {
				printpre(get_class($action));
				printpre($action->getTargetDisplayName());
				printpre($action->getId());
				printpre($action->getTimeStamp());
				printpre($action->getCategoryDisplayName());
				printpre($action->getCategoryId());
				printpre($action->getDescription());
				printpre($action->getTargetUrl());
				
				try {
					printpre($action->getParticipant()->getDisplayName());
				} catch (Exception $e) {
					printpre('Unknown');
				}
				
				try {
					printpre($action->getParticipant()->getId());
				} catch (Exception $e) {
					printpre('Unknown');
				}
				print "<hr/>";
			//}
		}
		
		
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