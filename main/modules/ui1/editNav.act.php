<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editNav.act.php,v 1.3 2008/01/09 22:19:41 adamfranco Exp $
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
 * @version $Id: editNav.act.php,v 1.3 2008/01/09 22:19:41 adamfranco Exp $
 */
class editNavAction
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
		return _("Edit Navigation");
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
		$this->printSortMethod($component, $step);		
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
		$this->saveSortMethod($component, $values);
		return true;
	}
}

?>