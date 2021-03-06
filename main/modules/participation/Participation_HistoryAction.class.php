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
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/participation/Participation_Action.interface.php");
 
/**
 * get info about create modification action
 * 
 * @since 1/27/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_HistoryAction
	implements Participation_Action
{

	/**
	 * Constructor
	 * 
	 * @param Participation_View $view 
	 * @param SiteComponent $node
	 * @param SeguePluginVersion $version
	 * @return object
	 * @access public
	 * @since 1/27/09
	 */
	public function __construct (Participation_View $view, SiteComponent $node, $version) {
		$this->_view = $view;
		$this->_node = $node;
		$this->_version = $version;
	}
	
	/**
	 * @var SiteComponent $node
	 * @access private
	 * @since 1/27/09
	 */
	private $_view;

	/**
	 * @var SiteComponent $_node
	 * @access private
	 * @since 1/27/09
	 */
	private $_node;

	/**
	 * @var SeguePluginVersion $_version
	 * @access private
	 * @since 1/27/09
	 */
	private $_version;
	
	/**
	 * get the id of a version action 
	 * 
	 * @return string version id
	 * @access public
	 * @since 1/27/09
	 */
	public function getId () {	
		return $this->getIdPrefix()."::".$this->_version->getVersionId();
	}


	/**
	 * get id prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 1/27/09
	 */
	public function getIdPrefix () {
		return "version";
	}
	
	/**
	 * get timestamp of version action
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/27/09
	 */
	public function getTimeStamp ()  {	
		return $this->_version->getTimestamp();
	}
	
	/**
	 * get creator of action
	 * 
	 * @return Participation_Participant
	 * @access public
	 * @since 1/27/09
	 */
	public function getParticipant ()  {		
		return new Participation_Participant($this->_view, 
			$this->_version->getAgentId());
	}
	
 	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getCategoryId () {		
		return "editor";
	
	}

	/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getCategoryDisplayName () {		
		return _("Editor");
	
	}
	
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/09
	 */
	public function getDescription ()  {		
		return "an editor of this content.";	
	}
	
	/**
	 * get display name of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetDisplayName ()  {
		$nodeUrl = $this->_node->acceptVisitor(new ParticipationBreadCrumbsVisitor($this->_node));
		$versionUrl =  " (Version: ".$this->_version->getNumber().")";

		return $nodeUrl.$versionUrl;
	}

	/**
	 * get url of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetUrl () {			
		$url = SiteDispatcher::quickURL('versioning','compare_versions', array('node' => $this->_node->getId(), 'late_rev' => $this->_version->getVersionId()));

		return $url;
	}
	
	
}



?>