<?php
/**
 * @since 6/1/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addContent.act.php,v 1.1 2007/06/04 16:31:57 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");

/**
 * A 1-step wizard to choose what kind of content to create
 * 
 * @since 6/1/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addContent.act.php,v 1.1 2007/06/04 16:31:57 adamfranco Exp $
 */
class addContentAction
	extends SegueClassicWizard
{
		
	/**
	 * Answer the authorization function Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function &getAuthFunctionId () {
		$idManager =& Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.add_children");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/8/07
	 */
	function &createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("type", $this->getTypeStep());
		
		return $wizard;
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 5/9/07
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveTypeStep($properties['type']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the step for choosing the content type to add.
	 * 
	 * @return object WizardComponent
	 * @access public
	 * @since 6/1/07
	 */
	function &getTypeStep () {
		$pluginManager =& Services::getService('PluginManager');
		
		$step =& new WizardStep();
		$step->setDisplayName(_("Content Type"));
		
		$property =& $step->addComponent("content_type", new WRadioList());
		
		$plugins = $pluginManager->getEnabledPlugins();
		
		foreach ($plugins as $key => $pType) {
			ob_start();
			print "\n<div'>";
			$iconFile = $pluginManager->getPluginDir($pType)."/icon.png";
			if (file_exists($iconFile)) {
				print "\n\t<img src='".$iconFile."' width='50px' style='float: left;'/>";
			}
			print "\n\t<div><strong>".$pType->getKeyword()."</strong></div>";
			print "\n\t<div>".$pType->getDescription()."</div>";
			print "\n</div>";
			$property->addOption($key, ob_get_clean());
		}
		
		// Create the step text
		ob_start();
				
		print "\n<p><strong>"._("Select a Content Type:")."</strong>";
// 		print "\n"._("The title of content: ");
		print "\n<br />[[content_type]]</p>";
		
		$step->setContent(ob_get_clean());
		return $step;
	}
	
}

?>