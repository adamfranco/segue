<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.2 2007/09/24 20:49:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(MYDIR."/main/library/PluginManager/SeguePlugins/SeguePluginsAjaxPlugin.abstract.php");
require_once(dirname(__FILE__)."/Rendering/EditModeSiteVisitor.class.php");

/**
 * This action provides a wizard for editing a navigation node
 * 
 * @since 5/11/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.2 2007/09/24 20:49:09 adamfranco Exp $
 */
class editSiteAction
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
		return _("Edit Site");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 9/24/07
	 */
	function createWizard () {
		$wizard = parent::createWizard();
		
		$wizard->addStep("header", $this->getHeaderStep());
		
		return $wizard;
	}
	
	/**
	 * Answer a step for editing the header.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Site Header and Footer"));
		
		// Create the step text
		ob_start();
		
		try {
			$visitor = new EditModeSiteVisitor;
			try {
				$headerId = $visitor->getHeaderId($component);
			} 
			// If we don't have a header, see if we have an empty header cell.
			catch (Exception $e) {
				$headerCellId = $visitor->getHeaderCellId($component);
			}
			
			
			$harmoni = Harmoni::instance();
			print "<iframe src='".$harmoni->request->quickURL('ui1', 'editHeader')."' height='800px' width='100%' />";
		} catch (Exception $e) {
			print _("This site is configured in a way that does not have a site header. A header can be added using the <em>New Mode</em> user interface.");
		}
		
		$step->setContent(ob_get_clean());
		
		return $step;
	}
}

?>