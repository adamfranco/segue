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
 * perform a site search and add the search folder to the session.
 * 
 * @since 08/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class perform_searchAction
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
			
			
			$folder = new SearchPortalFolder(RequestContext::value('query'));
			$usingOld = false;
			foreach ($_SESSION['portal_searches'] as $searchFolder) {
				if ($searchFolder->getIdString() == $folder->getIdString()
					&& $folder->getAgentId()->isEqual($searchFolder->getAgentId()))
				{
					$folder = $searchFolder;
					$usingOld = true;
					break;
				}
			}
			
			// run the search if needed.
			if (!$usingOld)
				$folder->search();
			
			$_SESSION['portal_searches'][] = $folder;
			print "\n\t<search id=\"".$folder->getIdString()."\"/>";
		
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
		$this->end();
	}
	
}

?>