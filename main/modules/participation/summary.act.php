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
class summaryAction 
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
		
		// print out link to site map
		$siteMapUrl = SiteDispatcher::quickURL("view", "map", array('node' => $node->getId()));
		$links = "<a href='".$siteMapUrl."'>"._("Site Map")."</a> | "._("Tracking");		
		$actionRows->add(new Block($links, STANDARD_BLOCK));
		
		// print out breadcrumbs to current node
		$breadcrumbs = $node->acceptVisitor(new ParticipationBreadCrumbsVisitor($node, TRUE));
		$actionRows->add(new Heading(_("Participation: ").$breadcrumbs, 2));
		
		// get list of participants
		$actionRows->add ( new Block($this->getParticipantsList(), STANDARD_BLOCK));		
	}
	
	/**
	 * Display a list of participants with summary of their contributions
	 * 
	 * @param string $id
	 * @return string XHTML markup
	 * @access private
	 * @since 1/28/09
	 */
	private function getParticipantsList ($action='all') {
		ob_start();
		
		//$site = SiteDispatcher::getCurrentRootNode();
		$node = SiteDispatcher::getCurrentNode();
		$view = new Participation_View($node);	
		
		if (RequestContext::value('sort'))
			$sort = RequestContext::value('sort');
		else
			$sort = 'name';

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
			$direction = SORT_ASC;
			$reorder = 'ASC';
			$reorderFlag = '&#94;';
		}

		// create an array of reorder urls
		$sortValues = array('name', 'commenter', 'author', 'editor');
		
		$reorderUrl = array();
		foreach ($sortValues as $sortValue) {
			$reorderUrl[$sortValue] = SiteDispatcher::quickURL('participation','summary', array('node' => $node->getId(),
				'sort' => $sortValue, 'direction' => $reorder));
		}
		
		// print out header row for list of participants
		ob_start();		
		print "\n\t<thead>";
		print "\n\t\t<tr>";		
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['name'];
		print "'>"._("Participants")." ".(($sort == 'name')?$reorderFlag:"")."</a></th>";
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['commenter'];
		print "'>"._("Commenter")." ".(($sort == 'commenter')?$reorderFlag:"")."</a></th>";
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['author'];
		print "'>"._("Author")." ".(($sort == 'author')?$reorderFlag:"")."</a></th>";		
		print "\n\t\t\t<th><a href='";
		print $reorderUrl['editor'];
		print "'>"._("Editor")." ".(($sort == 'editor')?$reorderFlag:"")."</a></th>";
		print "\n\t</thead>";
		$headRow = ob_get_clean();
		
		//get list of participants in site
		$participants = $view->getParticipants();	


		// sort actions by sort key
		$sortKeys = array();
		if ($sort == 'name') {
			foreach ($participants as $participant) {
				$sortKeys[] = $participant->getDisplayName();			
			}
		} else if ($sort == 'commenter') {
			foreach ($participants as $participant) {
				$participantView = new Participation_Participant($view, $participant->getId());
				$sortKeys[] = $participantView->getNumActionsByCategory('commenter');			
			}
		} else if ($sort == 'author') {
			foreach ($participants as $participant) {
				$participantView = new Participation_Participant($view, $participant->getId());
				$sortKeys[] = $participantView->getNumActionsByCategory('author');			
			}
		} else if ($sort == 'editor') {
			foreach ($participants as $participant) {
				$participantView = new Participation_Participant($view, $participant->getId());
				$sortKeys[] = $participantView->getNumActionsByCategory('editor');			
			}
		
		} else {
			throw new InvalidArguementException("Unknown sort field $sort");
		}
		
		array_multisort($sortKeys, $direction, array_keys($participants), SORT_ASC, $participants);
		
 		$this->_view = $view;
 		$this->_node = $node;
 		$this->_sortValue = $sortValue;
 		$this->_reorder = $reorder;
		
		$printer = new ParticipationResultPrinter($participants, $headRow, 30, array($this, 'printAction'));
		print $printer->getMarkup();

		return ob_get_clean();
	}
	
	/**
	 * Print out a row.
	 * 
	 * @param Participation_Participant $participant
	 * @return string
	 * @access public
	 * @since 1/30/09
	 */
	public function printAction (Participation_Participant $participants) {
		$participant = $participants->getId()->getIdString();
		$participantView = new Participation_Participant($this->_view, $participants->getId());
		
		ob_start();
		print "\n\t\t<tr>";
		print "\n\t\t\t<td class='participant_row'>";
		print "<a href='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => 'timestamp', 'direction' => 'DESC', 'participant' => $participant))."'>";		
		print $participants->getDisplayName();
		print "</a>";
		print "\n\t\t\t</td>";

		print "\n\t\t\t<td class='participant_row'>";
		print "<a href='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => 'timestamp', 'direction' => 'DESC', 'participant' => $participant, 'role' => 'commenter'))."'>";		
		print $participantView->getNumActionsByCategory('commenter');
		print "</a>";
		print "\n\t\t\t</td>";

		print "\n\t\t\t<td class='participant_row'>";
		print "<a href='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => 'timestamp', 'direction' => 'DESC', 'participant' => $participant, 'role' => 'author'))."'>";		
		print $participantView->getNumActionsByCategory('author');
		print "</a>";
		print "\n\t\t\t</td>";

		print "\n\t\t\t<td class='participant_row'>";
		print "<a href='";
		print SiteDispatcher::quickURL('participation','actions', array('node' => $this->_node->getId(),
			'sort' => 'timestamp', 'direction' => 'DESC', 'participant' => $participant, 'role' => 'editor'))."'>";		
		print $participantView->getNumActionsByCategory('editor');
		print "</a>";
		print "\n\t\t\t</td>";
		print "\n\t\t</tr>";
	
		return ob_get_clean();
	}	
}

?>