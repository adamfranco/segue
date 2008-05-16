<?php
/**
 * @since 5/9/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");

/**
 * This class is a user-interface for changing theme options.
 * 
 * @since 5/9/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_optionsAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/9/08
	 */
	function getHeadingText () {
		return _("Theme options");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/9/08
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("options", $this->getOptionsStep());
		$wizard->addStep("advanced", $this->getAdvancedStep());
		
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
	 * @since 5/9/08
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveOptionsStep($properties['options']))
				return FALSE;
			if (!$this->saveAdvancedStep($properties['advanced']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the theme step
	 * 
	 * @return object WizardStep
	 * @access protected
	 * @since 5/8/08
	 */
	protected function getOptionsStep () {
		$component = $this->getSiteComponent();
		$step =  new WizardStep();
		$step->setDisplayName(_("Basic Options"));
		ob_start();
		
		print "\n<h2>"._("Basic Options")."</h2>";
		print "\n<p>";
		print _("Here you can set the options for the current theme."); 
		print "\n</p>\n";
				
		$theme = $component->getTheme();
		if (!$theme->supportsOptions()) {
			print "\n<p>"._("This theme does not currently support options")."</p>";
			$step->setContent(ob_get_clean());
			return $step;
		}
		$optionsSession = $theme->getOptionsSession();
		
		foreach ($optionsSession->getOptions() as $option) {
			print "\n<h3>".$option->getDisplayName()."</h3>";
			print "\n<p>".$option->getDescription()."</p>";
			print "[[".$option->getIdString()."]]";
			$property = $step->addComponent($option->getIdString(), new WSelectList());
			$property->setValue($option->getValue());
			
			$values = $option->getValues();
			$labels = $option->getLabels();
			for ($j = 0; $j < count($values); $j++) {
				$property->addOption($values[$j], $labels[$j]);
			}
		}
		
				
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the theme step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/8/08
	 */
	protected function saveOptionsStep (array $values) {
		$component = $this->getSiteComponent();
		$theme = $component->getTheme();
		
		if (!$theme->supportsOptions()) {
			return false;
		}
		$optionsSession = $theme->getOptionsSession();
		
		foreach ($optionsSession->getOptions() as $option) {
			if ($values[$option->getIdString()] != $option->getValue())
				$option->setValue($values[$option->getIdString()]);
		}
		
		$component->updateTheme($theme);
		
		return true;
	}
	
	/**
	 * Answer a wizard step for advanced theme editing.
	 * 
	 * @return object WizardStep
	 * @access protected
	 * @since 5/15/08
	 */
	protected function getAdvancedStep () {
		$component = $this->getSiteComponent();
		$step =  new WizardStep();
		$step->setDisplayName(_("Advanced Editing"));
		$harmoni = Harmoni::instance();
		ob_start();
		
		print "\n<div class='theme_edit_step'>";
		print "\n<h2>"._("Advanced Theme Editing")."</h2>";
		print "\n<p>";
		print _("Here you can edit the markup and CSS for the theme."); 
		print "\n</p>\n";
				
		$theme = $component->getTheme();
		if (!$theme->supportsModification()) {
			print "\n<p>"._("This theme does not currently support modification. You can make a copy of it for just this site that you can then modify.")."</p>";
			
			$property = $step->addComponent('create_copy', WSaveButton::withLabel(_("Create Local Theme Copy")));
			
			print "[[create_copy]]";
			$step->setContent(ob_get_clean());
			return $step;
		}
		
		$modSess = $theme->getModificationSession();
		
		print "\n<h3>"._("Theme Information")."</h3>";
		print "\n<table class='info_table'><tr><td>";
		$property = $step->addComponent('display_name', new WTextField);
		$property->setSize(40);
		$property->setValue($theme->getDisplayName());
		print "\n<h4>"._("Display Name")."</h4>\n[[display_name]]";
		
		$property = $step->addComponent('description', new WTextArea);
		$property->setRows(10);
		$property->setColumns(40);
		$property->setValue($theme->getDescription());
		print "\n<br/><h4>"._("Description")."</h4>\n[[description]]";
		
		print "\n</td><td>";
		$property = $step->addComponent('thumbnail', new WFileUploadField);
		$property->setAcceptedMimetypes(array('image/png', 'image/jpeg', 'image/gif'));
		print "\n<h4>"._("Thumbnail")."</h4>\n[[thumbnail]]";
		print "<div><br/>"._("Current Thumbnail: ")."<br/>";
		
		try {
			$currentThumbnail = $theme->getThumbnail();
			$property->setStartingDisplay($currentThumbnail->getBasename(), 
				$currentThumbnail->getSize());
			print "\n\t<img src='".$harmoni->request->quickUrl('gui2', 'theme_thumbnail',
				array('theme' => $theme->getIdString(), 'rand' => rand(1,10000)))."' width='200px'/>";
		} catch (UnknownIdException $e) {
			print "<em>"._("none")."</em>";
		}
		print "</div>";
		print "\n</td></tr></table>";
		
		print "\n<h3>"._("Theme Data")."</h3>";
		
		$property = $step->addComponent('global_css', new WTextArea);
		$property->setRows(20);
		$property->setColumns(40);
		$property->setValue($modSess->getGlobalCss());
		print "\n<h4>"._("Global CSS")."</h4>\n[[global_css]]";
		
		print "\n<table class='theme_advanced_table'>";
		foreach ($modSess->getComponentTypes() as $type) {
// 			print "\n\t<tr>\n\t\t<th colspan='2'>".$type."</th>\n\t</tr>";
			print "\n\t<tr>";
			print "\n\t\t<th>".$type." CSS</th>";
			print "\n\t\t<th>".$type." HTML</th>";
			print "\n\t</tr>";
			
			print "\n\t<tr>";
			print "\n\t\t<td>[[".$type."-css]]</td>";
			print "\n\t\t<td>[[".$type."-html]]</td>";
			print "\n\t</tr>";
			
			$property = $step->addComponent($type.'-css', new WTextArea);
			$property->setRows(10);
			$property->setColumns(40);
			$property->setValue($modSess->getCssForType($type));
			
			$property = $step->addComponent($type.'-html', new WTextArea);
			$property->setRows(10);
			$property->setColumns(60);
			$property->setValue($modSess->getTemplateForType($type));
		}
		print "\n</table>";
		
		$property = $step->addComponent('options', new WTextArea);
		$property->setRows(40);
		$property->setColumns(100);
		$property->setValue($modSess->getOptionsDocument()->saveXMLWithWhiteSpace());
		print "\n<h3>"._("Theme Options")."</h3>";
		$help = _("In the text area below you can add an XML document that describes any options for this theme. This document must conform to the %1. (View an example %2.)");
		$schema = "<a href='".$harmoni->request->quickURL('gui2', 'view_options_schema')."' target='_blank'>"._("options schema")."</a>";
		$example = "<a href='".$harmoni->request->quickURL('gui2', 'view_options_example')."' target='_blank'>"._("options document")."</a>";
		print "\n<p>".str_replace('%1', $schema, str_replace('%2', $example, $help))."</p>";
		print "\n<p>"._("Each option defines a set of choices for the user. These choices are composed of one or more settings. When a choice is used, all occurrances of the marker in the CSS and HTML above will be replaced with the value of that setting.")."</p>";
		print "\n[[options]]";
		
		
		print "\n</div>";
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the advanced step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/15/08
	 */
	protected function saveAdvancedStep (array $values) {
		$component = $this->getSiteComponent();
		$theme = $component->getTheme();
		
		if (isset($values['create_copy']) && $values['create_copy']) {
			// Get the first source that supports admin.
			$guiMgr = Services::getService('GUIManager');
			foreach ($guiMgr->getThemeSources() as $source) {
				if ($source->supportsThemeAdmin()) {
					$adminSession = $source->getThemeAdminSession();
					$newTheme = $adminSession->createCopy($theme);
					
					$component->updateTheme($newTheme);
					
					$this->reloadToStep = 2;
					return true;
				}
			}
			// Nowhere to copy to.
			print "<p>"._("Error: No available source to copy this theme to.")."</p>";
			return false;
		}
		
		// Save Advanced edits
		$modSess = $theme->getModificationSession();
		$modSess->updateDisplayName($values['display_name']);
		$modSess->updateDescription($values['description']);
		if (!is_null($values['thumbnail']['tmp_name'])) {
			$file = new Harmoni_Filing_FileSystemFile($values['thumbnail']['tmp_name']);
			$file->setMimeType($values['thumbnail']['type']);
			$modSess->updateThumbnail($file);
		}
		$modSess->updateGlobalCss($values['global_css']);
		
		foreach ($modSess->getComponentTypes() as $type) {
			$modSess->updateCssForType($type, $values[$type.'-css']);
			$modSess->updateTemplateForType($type, $values[$type.'-html']);
		}
		
		$optionsString = trim ($values['options']);
		$optionsDoc = new Harmoni_DOMDocument;
		$optionsDoc->preserveWhiteSpace = false;
		if (strlen($optionsString) && $optionsString != '<?xml version="1.0"?>')
			$optionsDoc->loadXML($values['options']);
		try {
			$modSess->updateOptionsDocument($optionsDoc);
		} catch (ValidationFailedException $e) {
			print "<strong>"._("Error in Options Definition:")." </strong>";
			print $e->getMessage();
			return false;
		}
		
		return true;
	}
}

?>