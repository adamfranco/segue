<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: deleteComponent.act.php,v 1.5 2007/08/22 20:04:48 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: deleteComponent.act.php,v 1.5 2007/08/22 20:04:48 adamfranco Exp $
 */
class deleteComponentAction 
	extends EditModeSiteAction
{
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function processChanges ( SiteDirector $director ) {		
		$component =& $director->getSiteComponentById(RequestContext::value('node'));
		
		$this->findSafeReturnNode($director, $component);
		
		$organizer =& $component->getParentComponent();
		if ($organizer)
			$organizer->detatchSubcomponent($component);

		$director->deleteSiteComponent($component);
	}
	
	/**
	 * Return the browser to the page from whence they came
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function returnToCallerPage () {
		$harmoni =& Harmoni::instance();
		if ($this->_returnNode) {
			if (!$action = RequestContext::value('returnAction'))
				 $action = "editview";
			RequestContext::locationHeader($harmoni->request->quickURL(
				$harmoni->request->getRequestedModule(), $action,
				array("node" => $this->_returnNode)));	
		} else {
			RequestContext::locationHeader($harmoni->request->quickURL(
				"ui2", "list"));
		}
	}
	
	/**
	 * Find a safe return node. If we are deleting the return node or the 
	 * return node is a descendent of the node we are deleting, use the deleted
	 * node's parent as the return node.
	 * 
	 * @param object SiteComponent $componentToDelete
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function findSafeReturnNode ( &$director, &$componentToDelete ) {
		if (RequestContext::value('returnNode')) {
			// Traverse up to see if the componentToDelete is an ancestor of the 
			// return node or the return node itself.
			$node =& $director->getSiteComponentById(RequestContext::value('returnNode'));
			while ($node) {
				if ($componentToDelete->getId() == $node->getId()) {
					$parentComponent =& $componentToDelete->getParentComponent();
					$this->_returnNode = $parentComponent->getId();
					return;
				}
				$node =& $node->getParentComponent();
			}
			
			// If the return node isn't going to be deleted, just use it.
			$this->_returnNode = RequestContext::value('returnNode');
		} else {
			$this->_returnNode = null;
		}
	}
}

?>