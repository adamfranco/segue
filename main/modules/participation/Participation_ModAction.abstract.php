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
 
 require_once(MYDIR."/main/modules/participation/Participation_Action.interface.php");

/**
 * gets information about a modification action
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
abstract class Participation_ModAction
	implements Participation_Action
{

	/**
	 * Constructor
	 * 
	 * @param SiteComponent $node
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	public function __construct (SiteComponent $node) {
		$this->_node = $node;
	}
	
	/**
	 * @var SiteComponent $_node
	 * @access private
	 * @since 1/23/09
	 */
	private $_node;

		
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
	 * get the id prefix depending no type of modication (e.g. create or edit)
	 * 
	 * @return string
	 * @access protected
	 * @since 1/23/09
	 */
	abstract protected function getIdPrefix ();
	
	/**
	 * get participatnts
	 * 
	 * @param array 
	 * @return array
	 * @access public
	 * @since 1/23/09
	 */
	public function getParticipants () {
		
		throw new UnimplementedException();
	
	}
	
		/**
	 * get category of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getCategory () {
		
		throw new UnimplementedException();
	
	}
	
	/**
	 * get description of action (e.g. create, edit, comment...)
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getDescription ()  {
		
		throw new UnimplementedException();
	
	}

	/**
	 * get timestamp of action (e.g. create, edit, comment...)
	 * 
	 * @return DateTime
	 * @access public
	 * @since 1/23/09
	 */
	public function getTimeStamp ()  {
		
		throw new UnimplementedException();
	
	}

	/**
	 * get display name of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetDisplayName ()  {
		
		throw new UnimplementedException();
	
	}

	/**
	 * get url of node that action is applied to
	 * 
	 * @return string
	 * @access public
	 * @since 1/23/09
	 */
	public function getTargetUrl ()  {
		
		throw new UnimplementedException();
	
	}


	
}

?>