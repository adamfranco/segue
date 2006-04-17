<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addComponent.act.php,v 1.1 2006/04/17 18:10:26 adamfranco Exp $
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
 * @version $Id: addComponent.act.php,v 1.1 2006/04/17 18:10:26 adamfranco Exp $
 */
class addComponentAction 
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
	function processChanges ( &$director ) {
		// Get the target organizer's Id & Cell
		$targetOrgId = RequestContext::value('organizerId');
		$targetCell = RequestContext::value('cellIndex');
		
		$component =& $director->createSiteComponent(RequestContext::value('componentType'));
			
		$organizer =& $director->getSiteComponentById($targetOrgId);
		$oldCellId = $organizer->putSubcomponentInCell($component, $targetCell);
		
		if (RequestContext::value('displayName'))
			$component->updateDisplayName(RequestContext::value('displayName'));
	}
}

?>