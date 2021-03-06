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
			$idMgr->getId('edu.middlebury.authorization.view'),
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
		$this->_rowColor = "#FFFFFF";
		$node = SiteDispatcher::getCurrentNode();
		$actionRows = $this->getActionRows();		
		
		// print out related links
		$actionRows->add (new Block($this->getRelatedLinks($node), STANDARD_BLOCK));
		
		// print out breadcrumbs to current node
		$breadcrumbs = $node->acceptVisitor(new ParticipationBreadCrumbsVisitor($node, TRUE));
		$actionRows->add(new Heading(_("Participation in: ").$breadcrumbs, 2));
		
		// get getActionDisplayOptions
		$actionRows->add ( new Block($this->getActionDisplayOptions(), STANDARD_BLOCK));
		
		// get list of actions
		$actionRows->add ( new Block($this->getActionsList(), STANDARD_BLOCK));

		// get getActionDisplayOptions
		$actionRows->add ( new Block($this->getActionDisplayOptions(), STANDARD_BLOCK));
	}


	/**
	 * get links to map and roles
	 * 
	 * @return string XHTML markup
	 * @access public
	 * @since 2/5/09
	 */
	public function getRelatedLinks ($node) {

		ob_start();

		print "<div style='float: left; clear: none'>";
		print "<a href='".SiteDispatcher::quickURL("view", "map", array('node' => $node->getId()));	
		print "'>"._("map")."</a>";
		print " | "._("track");
		print "</div>";

		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		// get site participation profile if user is a site editor
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			SiteDispatcher::getCurrentRootNode()->getQualifierId()))
		{
			$rootNode = SiteDispatcher::getCurrentRootNode();
			$rootView = new Participation_View($rootNode);				
			$rootActions = $rootView->getActions();

			$createActions = 0;
			$commentActions = 0;
			$versionActions = 0;

			foreach ($rootActions as $action) {
				if ($action->getIdPrefix() == 'create') {
					$createActions ++;
				} else if ($action->getIdPrefix() == 'comment') {
					$commentActions ++;
				} else if ($action->getIdPrefix() == 'version') {
					$versionActions ++;
				}	
			}

			print "<div style='float: right; clear: none'>";
			print "<table cellpadding='0' cellspacing='0'>";
			print "<tr>";
			print "<td></td>";
			print "<td class='participation_profile'>&nbsp;creates&nbsp;</td>";
			print "<td class='participation_profile'>&nbsp;edits&nbsp;</td>";
			print "<td class='participation_profile'>&nbsp;comments&nbsp;</td>";
			print "</tr>";

			print "<tr>";
			print "<td><span style='font-size: 10px;'></span></td>";
			print "<td class='participation_profile'>".$createActions."</td>";
			print "<td class='participation_profile'>".$versionActions."</td>";
			print "<td class='participation_profile'>".$commentActions."</td>";
			print "</tr>";
			print "</table>";
			print "</div>";
		}

		return ob_get_clean();
	}


	/**
	 * answer an action filter form
	 * 
	 * @return string XHTML markup
	 * @access public
	 * @since 2/5/09
	 */
	public function getActionDisplayOptions () {
		$node = SiteDispatcher::getCurrentNode();
		$view = new Participation_View($node);	

		if (RequestContext::value('sort'))
			$this->_sort = RequestContext::value('sort');
		else
			$this->_sort = 'timestamp';

		if (RequestContext::value('participant'))
			$this->_participant = RequestContext::value('participant');
		else
			$this->_participant = 'all';

		if (RequestContext::value('role'))
			$this->_role = RequestContext::value('role');
		else
			$this->_role = 'all';

		if (RequestContext::value('display'))
			$this->_display = RequestContext::value('display');
		else
			$this->_display = '20';


		if (RequestContext::value('direction')) {
			if (RequestContext::value('direction') == 'DESC') {
				$this->_direction = SORT_DESC;
				$this->_reorder = 'ASC';
				$this->_reorderFlag = '&#94;';
			} else {
				$this->_direction = SORT_ASC;
				$this->_reorder = 'DESC';
				$this->_reorderFlag = 'v';
			}
		} else {
			$this->_direction = SORT_DESC;
			$this->_reorder = 'ASC';
			$this->_reorderFlag = '&#94;';
 		}
 		
 		$participants = $view->getParticipants();
 		
		ob_start();
		print "<form action='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $node->getId(), 'sort' => 'timestamp', 'direction' => 'DESC'));
		print "' method='post'>";
		
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		$rootNode = SiteDispatcher::getCurrentRootNode();
		if ($azMgr->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.modify'),
			$rootNode->getQualifierId()) == TRUE) {
			print "<a href = '";
			print SiteDispatcher::quickURL('participation','summary', array('node' => $node->getId(), 'sort' => 'name', 'direction' => 'ASC'));
			print "'>"._("Summary")."</a> | ";
		}
		
		
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
		print "\n\t</form>";
				
		return ob_get_clean();
		
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
		$participants = $view->getParticipants();		

		// create an array of reorder urls
		$sortValues = array('timestamp', 'contributor', 'contribution', 'role');
		
		$reorderUrl = array();
		foreach ($sortValues as $sortValue) {
			$reorderUrl[$sortValue] = SiteDispatcher::quickURL('participation','actions', array('node' => $node->getId(),
				'sort' => $sortValue, 'direction' => $this->_reorder, 'participant' => $this->_participant, 'role' => $this->_role));
		}

		// header row for list of actions
		ob_start();
		print "\n\t<thead>";
		print "\n\t\t<tr>";	
		
		// time
		print "\n\t\t\t<th style='border-bottom: 1px dotted'><a href='";
		print $reorderUrl['timestamp'];
		print "'>"._("Time")." ".(($this->_sort == 'timestamp')?$this->_reorderFlag:"")."</a></th>";
		
		// contributor
		print "\n\t\t\t<th style='border-bottom: 1px dotted'><a href='";
		print $reorderUrl['contributor'];
		print "'>"._("Contributor")." ".(($this->_sort == 'contributor')?$this->_reorderFlag:"")."</a></th>";

		// role
		print "\n\t\t\t<th style='border-bottom: 1px dotted'><a href='";
		print $reorderUrl['role'];
		print "'>"._("Role")." ".(($this->_sort == 'role')?$this->_reorderFlag:"")."</a>";
		print "</th>";
		
		// contribution
		print "\n\t\t\t<th style='border-bottom: 1px dotted'><a href='";
		print $reorderUrl['contribution'];
		print "'>"._("Contribution")." ".(($this->_sort == 'contribution')?$this->_reorderFlag:"")."</a></th>";
		
		// pushdown link
		print "\n\t\t\t<th style='border-bottom: 1px dotted'></th>";
		
		print "\n\t</thead>";
		
		$headRow = ob_get_clean();
		
		// if participant specified get their actions
		if (isset($this->_participant) && $this->_participant != 'all') {		
			$idMgr = Services::getService('Id');
			$participantId = $idMgr->getId($this->_participant);
			$participantView = new Participation_Participant($view, $participantId);
			$actions = $participantView->getActions();
			
		//get list of all actions in site	
		} else {			
			$actions = $view->getActions();	
		}
				
		// sort actions by sort key
		$sortKeys = array();
		if ($this->_sort == 'timestamp') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getTimeStamp()->asString();			
			}
		} else if ($this->_sort == 'contributor') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getParticipant()->getDisplayName();			
			}
		} else if ($this->_sort == 'contribution') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getTargetDisplayName();			
			}
		} else if ($this->_sort == 'role') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getCategoryDisplayName();			
			}
		
		} else {
			throw new InvalidArguementException("Unknown sort field $sort");
		}
		
		array_multisort($sortKeys, $this->_direction, array_keys($actions), SORT_ASC, $actions);
		
		// if role action specified then filter actions
		$selectedActions = array();
		if ($this->_role != 'all') {
			foreach ($actions as $action) {
				if ($action->getCategoryId() == $this->_role)
					$selectedActions[] = $action;			
			}
		} else {
			$selectedActions = $actions;
		}
				
		$this->_node = $node;
		$this->_sortValue = $sortValue;

		
		$printer = new ParticipationResultPrinter($selectedActions, $headRow, $this->_display, array($this, 'printAction'));
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
	
		// determine color of current row
		if ($this->_rowColor == "#F6F6F6") {
			$rowColor = "#FFFFFF";
			$this->_rowColor = "#FFFFFF";
		} else {
			$rowColor = "#F6F6F6";
			$this->_rowColor = "#F6F6F6";
		}
		
		ob_start();
		
		// time
		print  "\n\t\t\t<td valign='top' class='participation_row' style='white-space: nowrap; background-color: ".$rowColor.";'>".$action->getTimeStamp()->format("Y-m-d g:i a")."\n\t\t\t</td>";
		
		// contributor
		$participant = $action->getParticipant()->getId()->getIdString();
		
		print "\n\t\t\t<td valign='top'  class='participation_row' style='white-space: nowrap; background-color: ".$rowColor.";'><a href='";				
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => $this->_sortValue, 'direction' => $this->_reorder, 'participant' => $participant, 'role' => $this->_role))."'>";
		print $action->getParticipant()->getDisplayName();
		print "</a>";
		print "\n\t\t\t</td>";

		// role
		print "\n\t\t\t<td valign='top' class='participation_row' style='white-space: nowrap; background-color: ".$rowColor.";'>".$action->getCategoryDisplayName()."</td>";
		
		// contribution
		print "\n\t\t\t<td valign='top' class = 'participation_row' style='background-color: ".$rowColor.";'>";
		print  $action->getTargetDisplayName();
		print "\n\t\t\t</td>";
			
		
		// pushdown icon link
		$pushDownUrl = "<div class='pushdown'><a href='".$action->getTargetUrl()."'";
		$pushDownUrl .= " onclick=\"if (window.opener) { window.opener.location = this.href;";
		$pushDownUrl .=	"return false; }\" title='"._("review this in site")."'>";
		$pushDownUrl .= "\n\t\t\t<img src='".MYPATH."/images/pushdown.png' alt='site link' style='border: 0; vertical-align: middle;'/></a></div>";
		
		print "\n\t\t\t<td valign='top' class = 'participation_row' style='white-space: nowrap; background-color: ".$rowColor.";'>";
		print  $pushDownUrl;
		print "\n\t\t\t</td>";

	
		return ob_get_clean();
	}

	
}

?>