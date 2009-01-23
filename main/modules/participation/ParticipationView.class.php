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
require_once(MYDIR."/main/modules/participation/Participant.class.php");
require_once(MYDIR."/main/modules/participation/Participation_ModAction.abstract.php");
require_once(MYDIR."/main/modules/participation/Participation_CreateAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(MYDIR."/main/modules/participation/ParticipationSiteVisitor.class.php");

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * get information about agent participation in a given site
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_View {
		


	/**
	 * Constructor
	 * 
	 * @param SiteNavBlockSiteComponent $site
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (SiteNavBlockSiteComponent $site) {
		$this->_site = $site;
	}
	
	/**
	 * @var SiteNavBlockSiteComponent $_site
	 * @access private
	 * @since 1/23/09
	 */
	private $_site;
	
	/**
	 * @var  string $_id
	 * @access private
	 * @since 1/23/09
	 */
	private $_id;

	/**
	 * get all participants in the site
	 * 
	 * @return array of Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipants () {
		
		throw new UnimplementedException();
	}

	/**
	 * get all participants in the site
	 * 
	 * @return array of Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getActions () {
		
		// use a participation site visitor to visit all componenents
		// and get participation info
		// need to figure out how to a SiteComponent from a SiteNavBlockSiteComponent 
		$currentNode = SiteDispatcher::getCurrentNode();
		$currentNode->acceptVisitor(new ParticipationSiteVisitor());

		//throw new UnimplementedException();
	}
	
	/**
	 * get a participant in the site
	 * 
	 * @param string $Id
	 * @return Participation_Participant
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipant ($id) {		
		$participant = new Participation_Participant($this->_site, $id);
		return $participant;
	}
	
	/**
	 * get an action in the site
	 * 
	 * @param string $Id
	 * @return Participation_Action
	 * @access public
	 * @since 1/23/09
	 */
	public function getAction ($id) {
		if (preg_match('/^create::.+/', $id))
			return new Participation_CreateAction($this->_site, $id);
		else if (preg_match('/^last_edit::.+/', $id))
			return new Participation_LastEditAction($this->_site, $id);
		else if (preg_match('/^history::.+/', $id))
			return new Participation_HistoryAction($this->_site, $id);
		else if (preg_match('/^comment::.+/', $id))
			return new Participation_HistoryAction($this->_site, $id);
		
		throw new UnknownIdException("Could not retrieve an action for id $id");
	}

}

?>