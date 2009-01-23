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

//require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * get information about a given agent's participation in a given site
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_Participant {

	/**
	 * Constructor
	 * 
	 * @param SiteNavBlockSiteComponent $site
	 * @param string $id
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (SiteNavBlockSiteComponent $site, $id) {
		$this->_site = $site;
		$this->_id = $id;
	
	}
	
	/**
	 * @var  SiteNavBlockSiteComponent $_site
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
	 * get the id of a participant in the site
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getId () {		
		return $this->_id;
	}

	/**
	 * get the display name of a participant in the site
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getDisplayName () {
	
		$harmoni = Harmoni::instance();
		
		$idManager = Services::getService("Id");
		$agentID = $idManager->getId($this->_id);
		
		$agentManager = Services::getService("Agent");
		
		$agent = $agentManager->getAgent ($agentID);
		
		return $agent->getDisplayName();

	//	throw new UnimplementedException();
	}

	/**
	 * get all participants in the site
	 * 
	 * @return object Id
	 * @access public
	 * @since 1/23/09
	 */
	public function getActions () {
		
		
	}

}

?>