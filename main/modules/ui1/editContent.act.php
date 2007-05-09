<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editContent.act.php,v 1.2 2007/05/09 20:04:32 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(MYDIR."/main/library/PluginManager/SeguePlugins/SeguePluginsAjaxPlugin.abstract.php");

/**
 * This action provides a wizard for editing 
 * 
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editContent.act.php,v 1.2 2007/05/09 20:04:32 adamfranco Exp $
 */
class editContentAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/8/07
	 */
	function getHeadingText () {
		return _("Edit Content");
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access public
	 * @since 5/8/07
	 */
	function &getSiteComponent () {
		return $this->getSiteComponentForId($this->getQualifierId());
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 5/8/07
	 */
	function buildContent () {
		SeguePluginsAjaxPlugin::writeAjaxLib();
		parent::buildContent();
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
		
		$this->addNameAndDescriptionStep($wizard);
		$this->addDisplayOptionsStep($wizard);
		$this->addStatusStep($wizard);
		
		return $wizard;
	}
	
	/**
	 * Create the step for adding the title and description
	 * 
	 * @param object Wizard $wizard
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function &addNameAndDescriptionStep ( &$wizard ) {
		$component =& $this->getSiteComponent();
		$pluginManager =& Services::getService('PluginManager');
		$plugin =& $pluginManager->getPlugin($component->getAsset());
		
		$step =& $wizard->addStep("namedesc", new WizardStep());
		$step->setDisplayName(_("Title &amp; Content"));
		
		// Create the step text
		ob_start();
		
		$property =& $step->addComponent("display_name", new WTextField());
		$property->setSize(80);
		if ($component)
			$property->setValue($component->getDisplayName());
		$property->setErrorText(_("A value for this field is required."));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));

		print "\n<p><strong>"._("Title:")."</strong>";
// 		print "\n"._("The title of content: ");
		print "\n<br />[[display_name]]</p>";
		
		
		print "\n<p>\n\t<strong>"._("Content:")."</strong></p>\n\t";
		if ($plugin->supportsWizard()) {
			$property =& $step->addComponent("content", $plugin->getWizardComponent());
			
			print $plugin->getWizardText();
		} else {
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("plugin_manager");
			$url = $harmoni->request->quickURL("plugin_manager", "viewplugin",
				array("plugin_id" => $plugin->getId()));
			$harmoni->request->endNamespace();
			print "\n<iframe src='".$url."' height='600px' width='800px'/>";
		}
		
		$step->setContent(ob_get_clean());
		return $step;
	}
	
}

?>