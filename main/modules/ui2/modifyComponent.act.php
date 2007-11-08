<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modifyComponent.act.php,v 1.4 2007/11/08 17:40:45 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ModifySettingsSiteVisitor.class.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modifyComponent.act.php,v 1.4 2007/11/08 17:40:45 adamfranco Exp $
 */
class modifyComponentAction 
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
		$component = $director->getSiteComponentById(RequestContext::value('node'));
				
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId($component->getId()));
	}
	
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
		$component = $director->getSiteComponentById(RequestContext::value('node'));
		$component->acceptVisitor(new ModifySettingsSiteVisitor());
	}
}

?>