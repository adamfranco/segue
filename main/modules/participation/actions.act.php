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
		$actionRows->add(new Heading(_("Participation for: ").$node->getDisplayName(), 2));
				
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
	private function getActionsList () {
		ob_start();
		
		$node = SiteDispatcher::getCurrentNode();
		print "\n<table class='history_list'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"._("Time")."</th>";
		print "\n\t\t\t<th>"._("Contributor")."</th>";
		print "\n\t\t\t<th>"._("Contribution")."</th>";
		print "\n\t\t\t<th>"._("Role")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		//get list of participants in site
		$view = new Participation_View($node);
		$actions = $view->getActions();		
		
		// build a sort array
		$direction = SORT_DESC;
		$sortField = 'timestamp';
		$sortKeys = array();
		
		if ($sortField == 'timestamp') {
			foreach ($actions as $action) {
				$sortKeys[] = $action->getTimeStamp()->asString();
			
			}
		} else {
			throw new InvalidArguementException("Unknown sort field $sortField");
		}
		
		
		array_multisort($sortKeys, $direction, array_keys($actions), SORT_ASC, $actions);
		
		// print out list of participants
		foreach ($actions as $action) {
			print "\n\t\t<tr>";
			print "\n\t\t\t<td>".$action->getTimeStamp()->format("Y-m-d g:i a")."\n\t\t\t</td>";
			print "\n\t\t\t<td>".$action->getParticipant()->getDisplayName()."\n\t\t\t</td>";
			print "\n\t\t\t<td>";
			print "<a href='".$action->getTargetUrl()."'>";
			print $action->getTargetDisplayName();
			print "</a>";
			print "\n\t\t\t</td>";
			print "\n\t\t\t<td>".$action->getCategoryDisplayName()."\n\t\t\t</td>";
			print "\n\t\t</tr>";
			
		}
		
		print "\n\t</tbody>";
		print "\n</table>";
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