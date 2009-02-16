<?php
/**
 * @since 2/16/09
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * Answer a list of agents that match the query passed
 * 
 * @since 2/16/09
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class search_agentsAction
	extends XmlAction
{
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/14/07
	 */
	public function isAuthorizedToExecute () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				SiteDispatcher::getCurrentRootNode()->getQualifierId());
			
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to search users.");
	}
	
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 2/16/09
	 */
	public function execute () {
		$this->start();
		if (!$this->isAuthorizedToExecute())
			$this->error($this->getUnauthorizedMessage());
		
		$this->end();
	}
	
}

?>