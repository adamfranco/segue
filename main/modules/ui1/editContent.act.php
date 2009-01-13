<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editContent.act.php,v 1.8 2008/02/21 18:53:31 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(MYDIR."/main/library/PluginManager/SeguePlugins/SegueAjaxPlugin.abstract.php");

/**
 * This action provides a wizard for editing 
 * 
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editContent.act.php,v 1.8 2008/02/21 18:53:31 adamfranco Exp $
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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 5/8/07
	 */
	function buildContent () {
		SegueAjaxPlugin::writeAjaxLib();
		
		/*********************************************************
		 * Other headers and footers
		 *********************************************************/
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		
		ob_start();
		print Segue_MediaLibrary::getHeadHtml();
		
		$outputHandler->setHead($outputHandler->getHead().ob_get_clean());
		
		parent::buildContent();
	}
	
	/**
	 * Answer the display-options step for this component
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/12/08
	 */
	public function getDisplayOptionsStep () {
		$component = $this->getSiteComponent();
		$step = parent::getDisplayOptionsStep();
		
		ob_start();
		$this->printDisplayType($component, $step);
		
		$step->setContent($step->getContent().ob_get_clean());
		return $step;
	}
	
	/**
	 * save the display options step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/12/08
	 */
	function saveDisplayOptionsStep ($values) {
		if (!parent::saveDisplayOptionsStep($values)) {
			return false;
		}
		$component = $this->getSiteComponent();
		$this->saveDisplayType($component, $values);
		return true;
	}
	
	/**
	 * Create the step for adding the title and description
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getTitleStep () {
		$component = $this->getSiteComponent();
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($component->getAsset());
		
		$step = new WizardStep();
		$step->setDisplayName(_("Title &amp; Content"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("display_name", new WTextField());
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
			$property = $step->addComponent("content", $plugin->getWizardComponent());
			
			print "\n<div>[[content]]</div>";
		} else {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace("plugin_manager");
			$url = $harmoni->request->quickURL("plugin_manager", "viewplugin",
				array("plugin_id" => $plugin->getId()));
			$harmoni->request->endNamespace();
			print "\n<iframe src='".$url."' height='600px' width='800px'/>";
		}
		
		$step->setContent(ob_get_clean());
		return $step;
	}
	
	/**
	 * save the name and description step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/11/07
	 */
	function saveTitleStep ($values) {
		$component = $this->getSiteComponent();
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($component->getAsset());
		
		$value = trim($values['display_name']);
		if (!$value)
			return false;
		$component->updateDisplayName($value);
		
		if ($plugin->supportsWizard()) {
			$plugin->updateFromWizard($values['content']);
		}
		
		return true;
	}
	
	/**
	 * Print out the displayType options
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object WizardStep $step
	 * @return null
	 * @access protected
	 * @since 5/12/08
	 */
	protected function printDisplayType (SiteComponent $siteComponent, WizardStep $step) {
		$property = $step->addComponent('displayType', new WSelectList);
		$property->setValue($siteComponent->getDisplayType());
		
		$property->addOption('Block_Standard', _('Standard Block'));
		$property->addOption('Block_Sidebar', _('Sidebar Block'));
		$property->addOption('Block_Alert', _('Alert Block'));
		$property->addOption('Header', _('Header'));
		$property->addOption('Footer', _('Footer'));
		
		
		print "\n\t\t\t\t<p style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Look and feel of this content block: ')."[[displayType]]";
		print "\n\t\t\t\t</p>";
		
		$property = $step->addComponent('headingDisplayType', new WSelectList);
		$property->setValue($siteComponent->getHeadingDisplayType());
		
		$property->addOption('Heading_1', _('Heading - Biggest'));
		$property->addOption('Heading_2', _('Heading - Big'));
		$property->addOption('Heading_3', _('Heading - Normal'));
		$property->addOption('Heading_Sidebar', _('Heading - For Sidebar'));
		
		print "\n\t\t\t\t<p style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Look and feel of this content block\'s heading: ')."[[headingDisplayType]]";
		print "\n\t\t\t\t</p>";
	}
	
	/**
	 * Save the displayType options
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/12/08
	 */
	protected function saveDisplayType (SiteComponent $siteComponent, array $values) {
		$siteComponent->setDisplayType($values['displayType']);
		$siteComponent->setHeadingDisplayType($values['headingDisplayType']);
		return true;
	}
	
}

?>