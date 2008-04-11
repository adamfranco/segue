<?php
/**
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_plugins.act.php,v 1.4 2008/04/11 20:07:41 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * 
 * 
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_plugins.act.php,v 1.4 2008/04/11 20:07:41 adamfranco Exp $
 */
class list_pluginsAction 
	extends XmlAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$this->start();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('plugin_manager');
		$pluginManager = Services::getService("Plugs");
		
		foreach ($this->getTypes() as $type) {
			print "\n\t<pluginType typeString=\"".$type->asString()."\">";
			print "\n\t\t<domain>".$type->getDomain()."</domain>";
			print "\n\t\t<authority>".$type->getAuthority()."</authority>";
			print "\n\t\t<keyword>".$type->getKeyword()."</keyword>";
			print "\n\t\t<description><![CDATA[".$type->getDescription()."]]></description>";
			print "\n\t\t<icon><![CDATA[".$pluginManager->getPluginIconUrl($type)."]]></icon>";
			print "\n\t</pluginType>";
		}
		
		$harmoni->request->endNamespace();
		$this->end();
	}
	
	/**
	 * Answer the types.
	 * 
	 * @return array
	 * @access protected
	 * @since 4/11/08
	 */
	protected function getTypes () {
		$pluginManager = Services::getService("Plugs");
		return $pluginManager->getEnabledPlugins();
	}
}

?>