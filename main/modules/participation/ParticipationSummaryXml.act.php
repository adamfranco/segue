<?php
/**
 * @since 2/17/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY_DIR.'/main/library/AbstractActions/XmlAction.class.php');
require_once(MYDIR."/main/modules/participation/ParticipationView.class.php");

/**
 * gets participation summary and writes as an XML response
 * 
 * @since 2/17/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class ParticipationSummaryXmlAction		
	extends XmlAction
{
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		// get node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.modify'),
			SiteDispatcher::getCurrentNode()->getQualifierId());
	}
	
	/**
	 * Execute this action for generating an XML response with participation summary information
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->error("Permission Denied");
			
		$node = SiteDispatcher::getCurrentNode();
		$id = RequestContext::value('participant');
		
		$view = new Participation_View($node);
		$idMgr = Services::getService('Id');
		$participantView = new Participation_Participant($view, $idMgr->getId($id));
		
		$this->start();
		print "\n\t<role id='comments' number='".$participantView->getNumActionsByCategory('commenter')."'/>";
		print "\n\t<role id='author' number='".$participantView->getNumActionsByCategory('author')."'/>";
		print "\n\t<role id='editor' number='".$participantView->getNumActionsByCategory('editor')."'/>";
		$this->end();		
	}
		
}

?>