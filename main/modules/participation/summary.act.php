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
			$idMgr->getId('edu.middlebury.authorization.view'),
			SiteDispatcher::getCurrentRootNode()->getQualifierId());
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
		
		
		$site = SiteDispatcher::getCurrentRootNode();
		$actionRows = $this->getActionRows();
		$actionRows->add(new Heading(_("Participation for: ").$site->getDisplayName(), 2));
				
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
	private function getParticipantsList ($participant, $action='all') {
		ob_start();
		
		$site = SiteDispatcher::getCurrentRootNode();
		print "\n<table class='history_list'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th colspan='2'>"._("Participants")."</th>";
		print "\n\t\t\t<th>"._("Commenter")."</th>";
		print "\n\t\t\t<th>"._("Author")."</th>";
		print "\n\t\t\t<th>"._("Editor")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		//get list of participants in site
		$view = new Participation_View($site);
		$participants = $view->getParticipants();		

		
		// print out list of participants
		foreach ($participants as $participant) {
			print "\n\t\t<tr>";
			print "\n\t\t\t<td>";
			print $participant->getDisplayName();
			print "\n\t\t\t</td>";
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