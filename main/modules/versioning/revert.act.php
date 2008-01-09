<?php
/**
 * @since 1/8/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: revert.act.php,v 1.1 2008/01/09 17:28:18 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * Revert a plugin instance to a previous version.
 * 
 * @since 1/8/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: revert.act.php,v 1.1 2008/01/09 17:28:18 adamfranco Exp $
 */
class revertAction
	extends Action
{

	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/9/08
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$this->getAsset()->getId());
	}
		
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/9/08
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$pluginManager = Services::getService("Plugs");
		$plugin = $pluginManager->getPlugin($this->getAsset());
		
		if (!$plugin->supportsVersioning())
			throw new UnimplementedException("The ".get_class($plugin)." plugin doesn't support versioning, so it is impossible to revert.");
		
		$version = $plugin->getVersion(RequestContext::value("version_id"));
		$comment = "Reverting to Revision ".$version->getNumber();
		if (RequestContext::value('comment'))
			$comment .= " - ".urldecode(RequestContext::value('comment'));
		$version->apply($comment);
		
		$harmoni->history->goBack('revert_'.$plugin->getId());
	}
	
	/**
	 * Answer the asset that we are acting on.
	 * 
	 * @return object Asset
	 * @access protected
	 * @since 1/9/08
	 */
	protected function getAsset () {
		if (!isset($this->asset)) {
			$harmoni = Harmoni::instance();
		
			// Get the plugin asset id
			$id = RequestContext::value('node_id');

			// Get the plugin asset object
			$repositoryManager = Services::getService("Repository");
			$idManager = Services::getService("Id");
			$repository = $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			
			$this->asset = $repository->getAsset($idManager->getId($id));
		}
		
		return $this->asset;
	}
	
}

?>