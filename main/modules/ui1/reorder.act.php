<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: reorder.act.php,v 1.1 2007/08/22 20:04:47 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ModifySettingsSiteVisitor.class.php");


/**
 * This action will reorder site components that are in a FlowOrganizer. These will
 * be some sort Block or NavBlock
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: reorder.act.php,v 1.1 2007/08/22 20:04:47 adamfranco Exp $
 */
class reorderAction 
	extends EditModeSiteAction
{
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