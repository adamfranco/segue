<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: test.act.php,v 1.15 2007/05/09 20:04:32 adamfranco Exp $
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
 * @version $Id: test.act.php,v 1.15 2007/05/09 20:04:32 adamfranco Exp $
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
		
// 		$navNodeSchema =& $repository->getRecordStructure(
// 				$idManager->getId("Repository::edu.middlebury.segue.sites_repository::edu.middlebury.segue.nav_nod_rs"));
// 		$navNodeSchema->createPartStructure(
// 					"child_cells", 
// 					"The destination cells of the children of this node", 
// 					new HarmoniType("Repository", "edu.middlebury.harmoni", "string"), 
// 					false, 
// 					false, 
// 					false,
// 					$idManager->getId("edu.middlebury.segue.nav_nod_rs.child_cells"));
// 		exit;
		
// 		$asset =& $repository->createAsset("My node", "My node description.",
// 					new Type("Plugins", "Segue", "TextBlock", "TextBlock plugins display a block of text."));

// Assignment testing
// the asset
// 		$asset =& $repository->createAsset("My node", "My node description.",
// 					new Type("Plugins", "Segue", "Assignment", "Assignment plugins allow for online assignments."));
// // the assignment record structure and part structures
// 		$A_rs =& $repository->createRecordStructure("SegueAssignment", "for Segue Assignment Plugin", "plugin", "");
// 		$A_R_ps =& $A_rs->createPartStructure("SegueAssignmentReading", "reading entry for Segue Assignment Plugin", new Type("Plugins", "Segue", "string", "a reading assignment instance"), false, true, false);
// 		$A_Q_ps =& $A_rs->createPartStructure("SegueAssignmentQuestion", "question entry for Segue Assignment Plugin", new Type("Plugins", "Segue", "string", "a question assignment instance"), false, true, false);
// // the response record structure and part structures
// 		$R_rs =& $repository->createRecordStructure("SegueResponse", "for Segue Assignment Plugin", "plugin", "");
// 		$R_R_ps =& $R_rs->createPartStructure("SegueResponseReading", "reading status for Segue Assignment Plugin", new Type("Plugins", "Segue", "boolean", "a reading status instance"), false, true, false);
// 		$R_A_ps =& $R_rs->createPartStructure("SegueResponseAnswer", "Answer entry for Segue Assignment Plugin", new Type("Plugins", "Segue", "string", "answer to assignment question instance"), false, true, false);
// // the records
// 		$A_r =& $asset->createRecord($A_rs->getId());
// 		$R_r =& $asset->createRecord($R_rs->getId());
// // the parts
// 		$A_r->createPart($A_R_ps->getId(), String::withValue("Chapter 2"));
// 		$A_r->createPart($A_R_ps->getId(), String::withValue("Chapter 3"));
// 		$A_r->createPart($A_R_ps->getId(), String::withValue("Chapter 4"));
// 
// 		$A_r->createPart($A_Q_ps->getId(), String::withValue("What is your favorite color?"));
// 		$A_r->createPart($A_Q_ps->getId(), String::withValue("What is your quest"));
// 		$A_r->createPart($A_Q_ps->getId(), String::withValue("What did chapter 4 talk about that neither chapter 3 nor chapter 2 stated explicitly, but may well have implied?"));
// 
// 		$R_r->createPart($R_R_ps->getId(), Boolean::withValue("false"));
// 		$R_r->createPart($R_R_ps->getId(), Boolean::withValue("false"));
// 		$R_r->createPart($R_R_ps->getId(), Boolean::withValue("false"));
// 
// 		$R_r->createPart($R_A_ps->getId(), String::withValue("Chapter 2"));
// 		$R_r->createPart($R_A_ps->getId(), String::withValue("Chapter 2"));
// 		$R_r->createPart($R_A_ps->getId(), String::withValue("Chapter 2"));
// 
// 		printpre($asset->getId());
// 		exit;



		$this->displayPlugin('dev_id-27');
		$this->displayPlugin('dev_id-28');
		$this->displayPlugin('dev_id-29');
		$this->displayPlugin('dev_id-30');
		$this->displayPlugin('dev_id-73');
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
		$configuration->addProperty('plugin_path', $path = MYPATH."/plugins");
		
		$plugin =& Plugin::newInstance($asset, $configuration);
		
		
		$actionRows =& $this->getActionRows();
		ob_start();
		
		print AjaxPlugin::getPluginSystemJavascript();
		
		if (!is_object($plugin)) {
			print $plugin;
		} else {
			print "\n<div id='plugin:".$plugin->getId()."'>";
			
			print $plugin->executeAndGetMarkup();
			
			print "\n</div>";
		}
		
		
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}
}

?>