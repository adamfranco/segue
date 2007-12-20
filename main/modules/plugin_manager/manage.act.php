<?php
/**
 * @since 12/18/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: manage.act.php,v 1.2 2007/12/20 16:12:25 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This action provides an interface for managing and installing plugins.
 * 
 * @since 12/18/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: manage.act.php,v 1.2 2007/12/20 16:12:25 adamfranco Exp $
 */
class manageAction
	extends MainWindowAction
{
		
	/**
	 * Authorization check
	 * 
	 * @return boolean
	 * @access public
	 * @since 12/18/07
	 */
	public function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.modify"),
 					$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
	 * Answer a title
	 * 
	 * @return string
	 * @access public
	 * @since 12/18/07
	 */
	public function getHeadingText () {
		return _("Manage Plugins");
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 12/18/07
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		ob_start();
		
		$pluginMgr = Services::getService("PluginManager");
		
		print "\n<div style='color: #F00; font-weight: bold; margin: 10px; padding: 5px; border: 1px dotted;'>This UI is currently read-only. See <a href='https://sourceforge.net/tracker/index.php?func=detail&amp;aid=1799748&amp;group_id=82171&amp;atid=565234'>the bug tracker</a> for status</div>";
		
		print "\n<table class='plugin_manager_list' border='1'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>"._("Enabled")."</th>";
		print "\n\t\t\t<th>"._("Type")."</th>";
		print "\n\t\t\t<th>"._("Version")."</th>";
		print "\n\t\t\t<th>"._("Available Version?")."</th>";
		print "\n\t\t\t<th>"._("Developer")."</th>";
		print "\n\t\t\t<th>"._("Name")."</th>";
		print "\n\t\t\t<th>"._("Description")."</th>";
		print "\n\t\t\t<th>"._("Icon")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		$allPlugins = $pluginMgr->getRegisteredPlugins();
		foreach ($allPlugins as $pluginType) {
			print "\n\t\t<tr>";
			
			$pluginClass = $pluginMgr->getPluginClass($pluginType);
			try {
				$pluginMgr->_loadPluginFiles($pluginType);
				
				print "\n\t\t\t<td>";
				if ($pluginMgr->isInstalled($pluginType)) {
					print "\n\t\t\t\t<input type='checkbox' name='".RequestContext::name('enabled')."' value='".$pluginType->asString()."' ";
					if ($pluginMgr->isEnabled($pluginType))
						print " checked='checked'";
					print "/>";
				} else {
					print "\n\t\t\t<button>"._("Install")."</button>";
				}
				print "</td>";
				
				print "\n\t\t\t<td class='type'>".$pluginType->getDomain()." ::".$pluginType->getAuthority()." ::".$pluginType->getKeyword()."</td>";
				print "\n\t\t\t<td class='version'>".call_user_func(array($pluginClass, 'getPluginVersion'))."</td>";
				$availVersion = call_user_func(array($pluginClass, 'getPluginVersionAvailable'));
				if (!is_null($availVersion))
					print "\n\t\t\t<td class='version'>".$availVersion."</td>";
				else
					print "\n\t\t\t<td class='version'>&nbsp;</td>";
				print "\n\t\t\t<td class='creator'>".implode(", ", call_user_func(array($pluginClass, 'getPluginCreators')))."</td>";
				print "\n\t\t\t<td class='name'>".call_user_func(array($pluginClass, 'getPluginDisplayName'))."</td>";
				print "\n\t\t\t<td class='description'>".call_user_func(array($pluginClass, 'getPluginDescription'))."</td>";
				
				print "\n\t\t\t<td class='icon'>";
				$icon = $pluginMgr->getPluginIconUrl($pluginType);
				if ($icon) {
					print "\n\t\t\t\t<img src='".$icon."' align='left' style='margin-right: 5px; margin-bottom: 5px; width: 150px;' alt='icon' />";
				}
				print "\n\t\t\t</td>";
			
			} catch (Exception $e) {
				print "\n\t\t\t<td colspan='8'>".$e->getMessage()."</td>";
			}
			print "\n\t\t</tr>";
		}
		print "\n\t</tbody>";
		print "\n</table>";
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK));
		return $actionRows;
	}
}

?>