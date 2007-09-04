<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editFlowOrg.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");

/**
 * This action provides a wizard for editing a navigation node
 * 
 * @since 5/11/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editFlowOrg.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */
class editFlowOrgAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/11/07
	 */
	function getHeadingText () {
		return _("Edit Page Display Options");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/11/07
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("display", $this->getDisplayOptionsStep());
		
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
	 * @since 5/11/07
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveDisplayOptionsStep($properties['display']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Create the step for adding the display options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/11/07
	 */
	function getDisplayOptionsStep () {
		$component = $this->getSiteComponent();
		$step = parent::getDisplayOptionsStep();
		
		ob_start();
		$this->printRowsColumns($component, $step);
		$this->printDirection($component, $step);
		$this->printWidth($component, $step);
		
		$step->setContent($step->getContent().ob_get_clean());
		return $step;
	}
	
	/**
	 * save the display options step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveDisplayOptionsStep ($values) {
		if (!parent::saveDisplayOptionsStep($values)) {
			return false;
		}
		$component = $this->getSiteComponent();
		$this->saveRowsColumns($component, $values);
		$this->saveDirection($component, $values);
		$this->saveWidth($component, $values);
		return true;
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/13/07
	 */
	function printRowsColumns ( $siteComponent, $step ) {		
		$property = $step->addComponent('rows', new WSelectList);
		$property->setValue(trim($siteComponent->getNumRows()));
		
		$property->addOption(0, _("Unlimited"));
		for ($i = 1; $i <= 10; $i++) {
			$property->addOption($i, $i);
		}
		
		$property = $step->addComponent('columns', new WSelectList);
		$property->setValue(trim($siteComponent->getNumColumns()));
		
		for ($i = 1; $i <= 10; $i++) {
			$property->addOption($i, $i);
		}
		
		
		print "\n\t\t\t\t<p style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Rows: ')."[[rows]]";
		print "\n\t\t\t\t\t<br/>"._('Columns: ')."[[columns]]";
		print "\n\t\t\t\t</p>";
	}
	
	/**
	 * Save the rows/columns results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveRowsColumns ( $component, $values ) {
		$component->updateNumRows($values['rows']);
		$component->updateNumColumns($values['columns']);
		return true;
	}
	
	/**
	 * Print direction controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/13/07
	 */
	function printDirection ( $siteComponent, $step ) {
		$property = $step->addComponent('direction', new WSelectList);
		$property->setValue($siteComponent->getDirection());
		
		$directions = array(
			"Left-Right/Top-Bottom" => _("Left-Right/Top-Bottom"),
			"Top-Bottom/Left-Right" => _("Top-Bottom/Left-Right"),
			"Right-Left/Top-Bottom" => _("Right-Left/Top-Bottom"),
			"Top-Bottom/Right-Left" => _("Top-Bottom/Right-Left"),
// 			"Left-Right/Bottom-Top" => _("Left-Right/Bottom-Top"),
// 			"Bottom-Top/Left-Right" => _("Bottom-Top/Left-Right"),
// 			"Right-Left/Bottom-Top" => _("Right-Left/Bottom-Top"),
// 			"Bottom-Top/Right-Left" => _("Bottom-Top/Right-Left")
		);	
		
		foreach ($directions as $direction => $label) {
			$property->addOption($direction, $label);
		}
		
		print "\n\t\t\t\t<p style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Index Direction: ')."[[direction]]";
		print "\n\t\t\t\t</p>";
	}
	
	/**
	 * Save the direction results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveDirection ( $component, $values ) {
		$component->updateDirection($values['direction']);
		return true;
	}
	
	/**
	 * Print width controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printWidth ( $siteComponent, $step ) {
		$property = $step->addComponent('width', new WTextField);
		$property->setValue($siteComponent->getWidth());
		$property->setSize(6);
		$property->setErrorRule(new WECRegex("^([0-9]+(px|%))?$"));
		$property->setErrorText(_("Must be blank or in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc."));
		
		print "<div style='font-weight: bold;'>"._('Maximum Width Guideline: ');
		print "[[width]]";
		print "</div>";
		print "<div style='font-size: smaller;'>"
			._("If desired, enter a width in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc.<br/><strong>Note:</strong> This width is a guideline and is not guarenteed to be enforced. Content will fill the page, using this guideline where possible. Content inside of this container may stretch it beyond the specified width.")."</div>";		
	}
	
	/**
	 * Save the width results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveWidth ( $component, $values ) {
		$component->updateWidth($values['width']);
		return true;
	}
}

?>