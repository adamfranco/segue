<?php
/**
 * @since 5/15/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editMenu.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/editFlowOrg.act.php");

/**
 * This action allows editing of menu options
 * 
 * @since 5/15/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editMenu.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */
class editMenuAction
	extends editFlowOrgAction
{

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
	 * Print rows/columns controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param object WizardStep $step
	 * @return void
	 * @access public
	 * @since 5/13/07
	 */
	function printRowsColumns ( $siteComponent, $step ) {		
	
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
		
		$property->addOption('Menu_Left', _('Menu on the Left'));
		$property->addOption('Menu_Right', _('Menu on the Right'));
		$property->addOption('Menu_Top', _('Menu on the Top'));
		$property->addOption('Menu_Bottom', _('Menu on the Bottom'));
		
		
		print "\n\t\t\t\t<p style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Where is this menu positioned? ')."[[displayType]]";
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
		return true;
	}
	
}

?>