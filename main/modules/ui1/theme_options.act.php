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
		$step->setDisplayName(_("Theme Options"));
		ob_start();
		
		print "\n<h2>"._("Theme Options")."</h2>";
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
}

?>