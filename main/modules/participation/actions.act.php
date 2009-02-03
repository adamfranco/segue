<?php
/**
 * @since 1/27/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR."/main/modules/view/html.act.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsHeaderFooterSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(dirname(__FILE__)."/ParticipationView.class.php");
require_once(dirname(__FILE__)."/ParticipationResultPrinter.class.php");
require_once(dirname(__FILE__)."/ParticipationBreadCrumbsVisitor.class.php");
//require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");
//require_once(MYDIR."harmoni/core/Primitives/Chronology/DateAndTime.class.php")

/**
 * View the participation of a participant
 * 
 * @since 1/27/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class actionsAction 
	extends MainWindowAction
{

	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.modify'),
			SiteDispatcher::getCurrentNode()->getQualifierId());
	}
	
	/**
	 * Answer a message in the case of no authorization
	 * 
	 * @return string
	 * @access public
	 * @since 3/14/08
	 */
	public function getUnauthorizedMessage () {
		$message = _("You are not authorized to view the requested node.");
		$message .= "\n<br/>";
		$authNMgr = Services::getService("AuthN");
		if (!$authNMgr->isUserAuthenticatedWithAnyType())
			$message .= _("Please log in or use your browser's 'Back' Button.");
		else
			$message .= _("Please use your browser's 'Back' Button.");
		
		return $message;
	}

		/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 3/14/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();				
				
		$node = SiteDispatcher::getCurrentNode();
		$actionRows = $this->getActionRows();
		$breadcrumbs = $node->acceptVisitor(new BreadCrumbsVisitor($node));
		
		$actionRows->add(new Heading(_("Participation: ").$breadcrumbs, 2));
		
		
		//$actionRows->add ( new Block($this->getViewOptions(), STANDARD_BLOCK));
				
		$actionRows->add ( new Block($this->getActionsList(), STANDARD_BLOCK));		
	}


	/**
	 * Display a list of participants with summary of their contributions
	 * 
	 * @param string $id
	 * @return string XHTML markup
	 * @access private
	 * @since 1/28/09
	 */
	private function getViewOptions () {

	}
	
	/**
	 * Display a list of participants with summary of their contributions
	 * 
	 * @param string $id
	 * @return string XHTML markup
	 * @access private
	 * @since 1/28/09
	 */
	private function getActionsList () {
		$node = SiteDispatcher::getCurrentNode();
		$view = new Participation_View($node);	

		if (RequestContext::value('sort'))
			$sort = RequestContext::value('sort');
		else
			$sort = 'timestamp';

		if (RequestContext::value('participant'))
			$participant = RequestContext::value('participant');
		else
			$participant = 'all';

		if (RequestContext::value('role'))
			$role = RequestContext::value('role');
		else
			$role = 'all';

		if (RequestContext::value('display'))
			$display = RequestContext::value('display');
		else
			$display = '20';


		if (RequestContext::value('direction')) {
			if (RequestContext::value('direction') == 'DESC') {
				$direction = SORT_DESC;
				$reorder = 'ASC';
				$reorderFlag = '&#94;';
			} else {
				$direction = SORT_ASC;
				$reorder = 'DESC';
				$reorderFlag = 'v';
			}
		} else {
			$direction = SORT_DESC;
			$reorder = 'ASC';
			$reorderFlag = '&#94;';
		}

		$participants = $view->getParticipants();
		
		ob_start();

		print "<form action='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $node->getId(), 'sort' => 'timestamp', 'direction' => 'DESC'));
		print "' method='post'>";
		print "<a href = '";
		print SiteDispatcher::quickURL('participation','summary', array('node' => $node->getId(), 'sort' => 'name', 'direction' => 'ASC'));
		print "'>"._("Summary")."</a> | ";		
		// get a list of all participants
		print "\n\t<select name='participant'";
		print " onchange='this.form.submit();'";
		print ">";
	
		print "\n\t\t<option value='all'>"._("All Participants")."</option>";	
		foreach ($participants as $aParticipant) {
			print "\n\t\t<option value='".$aParticipant->getId()."'";
			if (RequestContext::value('participant') == $aParticipant->getId()) print " selected='selected'";
			print ">";
			print $aParticipant->getDisplayName();
			print "</option>";		
		}
		print "\n\t</select> ";				
				
		// get list of actions
		print "\n\t<select name='role'";
		print " onchange='this.form.submit();'";	
		print ">";	
		print "\n\t\t<option value='all'>"._("All Roles")."</option>";	
		
		$roleValues = array('commenter', 'author', 'editor');
		
		foreach ($roleValues as $roleValue) {
			print "\n\t\t<option value='".$roleValue."'";
			if (RequestContext::value('role') == $roleValue) print " selected='selected'";
			print ">";
			print $roleValue;
			print "</option>";		
		}
		print "\n\t</select>";		

		// print out number of rows to display
		print "\n\tDisplay: <select name='display'";
		print " onchange='this.form.submit();'";	
		print ">";	
		
		$displayValues = array(20, 5, 10, 25, 30);
		
		foreach ($displayValues as $displayValue) {
			print "\n\t\t<option value='".$displayValue."'";
			if (RequestContext::value('display') == $displayValue) print " selected='selected'";
			print ">";
			print $displayValue;
			print "</option>";		
		}
		print "\n\t</select>";		


		
		// print out summary of participant's actions
		if (isset($participant) && $participant != 'all') {		
			$idMgr = Services::getService('Id');
			$participantId = $idMgr->getId($participant);
			$participantView = new Participation_Participant($view, $participantId);
			$commenterActions = $participantView->getNumActionsByCategory("commenter");
			$authorActions = $participantView->getNumActionsByCategory("author");
			$editorActions = $participantView->getNumActionsByCategory("editor");
			print " (".$commenterActions."-comments, ".$authorActions."-new, ".$editorActions."-edits)";
		}
		print "\n\t</form>";
		
		// create an array of reorder urls
		$sortValues = array('timestamp', 'contributor', 'contribution', 'role');
		
		$reorderUrl = array();
		foreach ($sortValues as $sortValue) {
			$reorderUrl[$sortValue] = SiteDispatcher::quickURL('participation','actions', array('node' => $node->getId(),
				'sort' => $sortValue, 'direction' => $reorder, 'participant' => $participant, 'role' => $role));
		}

		
		ob_start();
		print "\n\t<thead>";
		print "\n\t\t<tr>";		
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['timestamp'];
		print "'>"._("Time")." ".(($sort == 'timestamp')?$reorderFlag:"")."</a></th>";
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['contributor'];
		print "'>"._("Contributor")." ".(($sort == 'contributor')?$reorderFlag:"")."</a></th>";
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['contribution'];
		print "'>"._("Contribution")." ".(($sort == 'contribution')?$reorderFlag:"")."</a></th>";		
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['role'];
		print "'>"._("Role")." ".(($sort == 'role')?$reorderFlag:"")."</a></th>";
		print "\n\t</thead>";
		$headRow = ob_get_clean();
		
		// if participant specified get their actions
		if (isset($participant) && $participant != 'all') {		
			$idMgr = Services::getService('Id');
			$participantId = $idMgr->getId($participant);
			$participantView = new Participation_Participant($view, $participantId);
			$actions = $participantView->getActions();
			
		//get list of all actions in site	
		} else {			
			$actions = $view->getActions();	
		}
				
		// sort actions by sort key
		$sortKeys = array();
		if ($sort == 'timestamp') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getTimeStamp()->asString();			
			}
		} else if ($sort == 'contributor') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getParticipant()->getDisplayName();			
			}
		} else if ($sort == 'contribution') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getTargetDisplayName();			
			}
		} else if ($sort == 'role') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getCategoryDisplayName();			
			}
		
		} else {
			throw new InvalidArguementException("Unknown sort field $sort");
		}
		
		array_multisort($sortKeys, $direction, array_keys($actions), SORT_ASC, $actions);
		
		// if role action specified then filter actions
		$selectedActions = array();
		if ($role != 'all') {
			foreach ($actions as $action) {
				if ($action->getCategoryId() == $role)
					$selectedActions[] = $action;			
			}
		} else {
			$selectedActions = $actions;
		}
		
		
		$this->_node = $node;
		$this->_sortValue = $sortValue;
		$this->_reorder = $reorder;
		$this->_participant = $participant;
		$this->_role = $role;
		
		$printer = new ParticipationResultPrinter($selectedActions, $headRow, $display, array($this, 'printAction'));
		print $printer->getMarkup();
		return ob_get_clean();
	}
	
	/**
	 * Print out a row.
	 * 
	 * @param Participation_Action $action
	 * @return string
	 * @access public
	 * @since 1/30/09
	 */
	public function printAction (Participation_Action $action) {
		ob_start();
		print  "\n\t\t\t<td valign='top' class='participation_row' style='white-space: nowrap'>".$action->getTimeStamp()->format("Y-m-d g:i a")."\n\t\t\t</td>";
		print "\n\t\t\t<td valign='top'  class='participation_row' style='white-space: nowrap'><a href='";
		$participant = $action->getParticipant()->getId()->getIdString();
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => $this->_sortValue, 'direction' => $this->_reorder, 'participant' => $participant, 'role' => $this->_role))."'>";
		print $action->getParticipant()->getDisplayName();
		print "</a>";
		print "\n\t\t\t</td>";
		print "\n\t\t\t<td class = 'participation_row'>";
		print  $action->getTargetDisplayName();
// 		
// 		$node = $action->getNode();
// 		print $node->acceptVisitor(new ParticipationBreadCrumbsVisitor($action->getNode()));
// 		if ($action->getCategoryId() == "editor") {
// 			print " (<a href='".$action->getTargetUrl()."'>Version: ".$action->getVersionNumber()."</a>)";
// 		}		
		print "\n\t\t\t</td>";
		print "\n\t\t\t<td class='participation_row'>".$action->getCategoryDisplayName()."</td>";
	
		return ob_get_clean();
	}

	/**
	 * Display contributions of a given participant
	 * 
	 * @return string XHTML markup
	 * @access private
	 * @since 1/28/09
	 */
	private function getParticipantActions () {
	
	}

	
}

?>