<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editContent.act.php,v 1.3 2007/05/11 18:36:23 adamfranco Exp $
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
 * @version $Id: editContent.act.php,v 1.3 2007/05/11 18:36:23 adamfranco Exp $
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
		
		/*********************************************************
		 * Other headers and footers
		 *********************************************************/
		$harmoni =& Harmoni::instance();
		$outputHandler =& $harmoni->getOutputHandler();
		
		ob_start();
		// Add our common Harmoni javascript libraries
		require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");
		
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/TabbedContent.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/prototype.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/js_quicktags.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/MediaLibrary.js'></script>";
		print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/MediaLibrary.css'/>";
		
		$outputHandler->setHead(ob_get_clean());
		
		parent::buildContent();
	}
	
	/**
	 * Create the step for adding the title and description
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function &getTitleStep () {
		$component =& $this->getSiteComponent();
		$pluginManager =& Services::getService('PluginManager');
		$plugin =& $pluginManager->getPlugin($component->getAsset());
		
		$step =& new WizardStep();
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
			
			print "\n<div>[[content]]</div>";
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
	
	/**
	 * save the name and description step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/11/07
	 */
	function saveTitleStep ($values) {
		$component =& $this->getSiteComponent();
		$pluginManager =& Services::getService('PluginManager');
		$plugin =& $pluginManager->getPlugin($component->getAsset());
		
		$value = trim($values['display_name']);
		if (!$value)
			return false;
		$component->updateDisplayName($value);
		
		if ($plugin->supportsWizard()) {
			$plugin->updateFromWizard($values['content']);
		}
		
		return true;
	}
	
}

?>