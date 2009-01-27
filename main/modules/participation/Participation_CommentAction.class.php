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
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/participation/Participant.class.php");
require_once(dirname(__FILE__)."/Participation_ModAction.abstract.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
 
/**
 * get info about create modification action
 * 
 * @since 1/26/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_CommentAction
	implements Participation_Action
{

	/**
	 * Constructor
	 * 
	 * @param object Id 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (CommentNode $comment) {
		$this->_comment = $comment;
	}
	
	/**
	 * @var CommentNode object $_comment
	 * @access private
	 * @since 1/27/09
	 */
	private $_comment;
	
	/**
	 * get the id of a comment action 
	 * 
	 * @param <##>
	 * @return array of comment ids
	 * @access public
	 * @since 1/23/09
	 */
	public function getId () {
		return $this->getIdPrefix()."::".$this->_comment->getId();
	}


	/**
	 * get id prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 1/26/09
	 */
	protected function getIdPrefix () {
		return "comment";
	}
	
	/**
	 * get timestamp of action (e.g. create, edit, comment...)
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/26/09
	 */
	public function getTimeStamp ()  {
		return $this->_comment->getCreationDate();
	}
	
	/**
	 * get creator of action
	 * 
	 * @return Participation_Participant
	 * @access public
	 * @since 1/26/09
	 */
	public function getParticipant ()  {		
		$commentsManager = CommentManager::instance();		
		$director = SiteDispatcher::getSiteDirector();
		$site = $director->getRootSiteComponent($commentsManager->getCommentParentAsset($this->_comment)->getId()->getIdString());
		
		$participant = new Participation_Participant($site, 
			$this->_comment->getAuthor()->getId());
				
		return $participant;
	}
	
	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getCategory () {
		
		return "Commenter";
	
	}
	
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getDescription ()  {
		
		return "comment on content.";
	
	}
	
	/**
	 * get display name of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetDisplayName ()  {
		return $this->_comment->getSubject();
	}

	/**
	 * get url of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetUrl () {
		$commentsManager = CommentManager::instance();
		$nodeId = $commentsManager->getCommentParentAsset($this->_comment)->getId()->getIdString(); 
		$commentId = $this->_comment->getId()->getIdString();
		
		// need to add to url #commentId
		return SiteDispatcher::quickURL('view','html', 
			array('node' => $nodeId)).'#'.$commentId;
	}
	
	
}



?>