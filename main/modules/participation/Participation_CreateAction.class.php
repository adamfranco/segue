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
require_once(dirname(__FILE__)."/Participation_Action.interface.php");

 
/**
 * get info about create modification action
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_CreateAction
	implements Participation_Action
{

	/**
	 * Constructor
	 * @param Participation_View $view
	 * @param SiteComponent $node
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (Participation_View $view, SiteComponent $node) {
		$this->_view = $view;
		$this->_node = $node;
	}
	
	/**
	 * @var SiteComponent $_node
	 * @access private
	 * @since 1/23/09
	 */
	protected $_node;

	/**
	 * @var Participation_View $view
	 * @access private
	 * @since 1/23/09
	 */
	protected $_view;

	/**
	 * get the id of a action
	 * 
	 * @param <##>
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getId () {
		return $this->getIdPrefix()."::".$this->_node->getId();
	}
		
	/**
	 * get id prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 1/23/09
	 */
	protected function getIdPrefix () {
		return "create";
	}
	
	/**
	 * get timestamp of action (e.g. create, edit, comment...)
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/23/09
	 */
	public function getTimeStamp ()  {
		return $this->_node->getCreationDate();
	}
	
	/**
	 * get creator of action
	 * 
	 * @return Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipant ()  {
		$id = $this->_node->getCreator()->getIdString();
		return $this->_view->getParticipant($id);
	}

	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getCategoryId () {		
		return "author";
	
	}

	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getCategoryDisplayName () {		
		return _("Author");
	
	}
		
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getDescription ()  {
		
		return "content created.";
	
	}

	/**
	 * get display name of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetDisplayName ()  {
		return $this->_node->acceptVisitor(new ParticipationBreadCrumbsVisitor($this->_node));
	}
	
	/**
	 * get url of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetUrl ()  {
		return SiteDispatcher::quickURL('view','html', 
			array('node' => $this->_node->getId()));
	}
	
	
}



?>