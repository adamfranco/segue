<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: test.act.php,v 1.6 2006/01/16 22:27:11 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: test.act.php,v 1.6 2006/01/16 22:27:11 adamfranco Exp $
 */
class testAction 
	extends MainWindowAction
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
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Plugin Tests");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		
// 		$asset =& $repository->createAsset("My node", "My node description.",
// 					new Type("Plugins", "Segue", "TextBlock", "TextBlock plugins display a block of text."));
// 		printpre($asset->getId());
// 		exit;


		$this->displayPlugin('dev_id-28');
		$this->displayPlugin('dev_id-32');
	}
	
	/**
	 * Display a plugin.
	 * 
	 * @param string $id
	 * @return void
	 * @access public
	 * @since 1/16/06
	 */
	function displayPlugin ($id) {
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		$asset =& $repository->getAsset($idManager->getId($id));
		
		$configuration =& new ConfigurationProperties;
		$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
		
		
		$plugin =& Plugin::newInstance($asset, $configuration);
		
		
		$actionRows =& $this->getActionRows();
		ob_start();
		print "\n<div id='".$id."'>";
		
		if (!is_object($plugin)) {
			print $plugin;
		} else {
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace('SegueTextBlockPlugin:'.$id);
			$baseUrl =& $harmoni->request->mkURL();
			print $plugin->executeAndGetMarkup($baseUrl);
			$harmoni->request->endNamespace();
		}
		
		print "\n</div>";
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}
}

?>