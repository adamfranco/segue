<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: reorder.act.php,v 1.2 2007/11/08 17:40:44 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ModifySettingsSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsAuthorizableVisitor.class.php");


/**
 * This action will reorder site components that are in a FlowOrganizer. These will
 * be some sort Block or NavBlock
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: reorder.act.php,v 1.2 2007/11/08 17:40:44 adamfranco Exp $
 */
class reorderAction 
	extends EditModeSiteAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$director = $this->getSiteDirector();
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("reorder");
		$block = $director->getSiteComponentById(RequestContext::value('node'));
		$harmoni->request->endNamespace();
		
		$parent = $block->getParentComponent();
		while (!$parent->acceptVisitor(new IsAuthorizableVisitor)) 
			$parent = $parent->getParentComponent();
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId($parent->getId()));
	}
	
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	function processChanges ( SiteDirector $director ) {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("reorder");
		
		// Get our component
		$block = $director->getSiteComponentById(RequestContext::value('node'));
		
		// The reordering is in the Flow Organizer, so get the parent of our node
		$organizer = $block->getParentComponent();
		
		// Do the reordering
		$organizer->putSubcomponentInCell($block, RequestContext::value('position'));
		
		$harmoni->request->endNamespace();
		
	}
}

?>