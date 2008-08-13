<?php
/**
 * @since 08/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * Clear a search from the session.
 * 
 * @since 08/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class clear_searchAction
	extends XmlAction
{
		
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 08/11/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 08/11/08
	 */
	public function execute () {
		$this->start();
		try {
		
			if (!$this->isAuthorizedToExecute())
				$this->error(_("You are not authorized to search."), "PermissionDenied");
			
			if (!isset($_SESSION['portal_searches']))
				$_SESSION['portal_searches'] = array();
			
			$newSearches = array();
			foreach ($_SESSION['portal_searches'] as $searchFolder) {
				if ($searchFolder->getIdString() != RequestContext::value('id')) {
					$newSearches[] = $searchFolder;
				}
			}
			
			$_SESSION['portal_searches'] = $newSearches;
			print "success";
		
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
		$this->end();
	}
	
}

?>