<?php
/**
 * @since 6/5/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addMenuContent.act.php,v 1.6 2007/09/25 14:07:32 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/addContent.act.php");
require_once(dirname(__FILE__)."/../ui2/addComponent.act.php");

/**
 * Add content to a menu
 * 
 * @since 6/5/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addMenuContent.act.php,v 1.6 2007/09/25 14:07:32 adamfranco Exp $
 */
class addMenuContentAction
	extends addContentAction
{
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 6/5/07
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withText(
				"<div>\n" .
				"[[_stepsBar]]" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[_cancel]]<br/>\n" .
				"[[_prev]]" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"<br/>\n" .
				"[[_next]]" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n"
			);
		$saveButton = $wizard->getSaveButton();
		$saveButton->setLabel(_("Create >>"));
		
		$wizard->addStep("nav", $this->getNavStep());
		
		$step = $this->getContentStep();
		$step->setDisplayName(_("Create New Menu Content"));
		$wizard->addStep("content", $step);
		
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
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if ($wizard->getCurrentStepName() == "nav") {
				if (!$this->saveNavStep($properties['nav']))
					return FALSE;
			} else {
				if (!$this->saveContentStep($properties['content']))
					return FALSE;
			}
			
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
	function getNavStep () {
		$pluginManager = Services::getService('PluginManager');
		
		$step = new WizardStep();
		$step->setDisplayName(_("Create New Navigation"));
		
		$property = $step->addComponent("organizerId", new WHiddenField());
		$property->setValue(RequestContext::value('organizerId'));
		
		$property = $step->addComponent("type", new WSaveWithChoiceButtonList());
		
		$navTypes = $this->getNavTypes();
		
		foreach ($navTypes as $i => $navArray) {
			ob_start();
			print " <strong>".$navArray['name']."</strong>";
			print "\n<div>";
			$icon = MYPATH."/icons/".$navArray['icon'];
			print "\n\t<img src='".$icon."' width='300px' align='left' style='margin-right: 5px; margin-bottom: 5px;' alt='icon' />";
			print "\n\t<div>".$navArray['description']."</div>";
			print "\n</div>";
			print "\n<div style='clear: both;'></div>";
			$property->addOption($navArray['type']->asString(), 
				_("Create >>"), 
				ob_get_clean());
				
			if (!$i) {
				$property->setValue($navArray['type']->asString());
				$set = true;
			}
		}
		
		// Create the step text
		ob_start();
				
		print "\n<div>"._("Select a Navigation type and click 'Create >>' or click 'Next' to choose a Content item to add to the menu:")."<hr/>";
// 		print "\n"._("The title of content: ");
		print "\n<br /><br />[[type]]</div>[[organizerId]]";
		
		$step->setContent(ob_get_clean());
		return $step;
	}
	
	/**
	 * Save the navigation step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 6/5/07
	 */
	function saveNavStep ($values) {
		$director = $this->getSiteDirector();
		$organizer = $this->getSiteComponentForIdString($values['organizerId']);
		$componentType = Type::fromString($values['type']);
		
		if ($componentType->getDomain() == 'segue-multipart')
			$component = addComponentAction::createMultipartComponent($director, $componentType, $organizer);
		else
			return false;
		
		$this->_newId = $component->getId();
		$this->_newIsNav = true;
		
		return true;
	}
	
	/**
	 * Answer an array of navigation types
	 * 
	 * @return array
	 * @access public
	 * @since 6/5/07
	 */
	function getNavTypes () {
		$types = array();
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart'),
				"name" => _("Single Column Page"),
				"description" => _("Chose this for a single column page in which you can append any number of content blocks."),
				"icon" => "Page.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart'),
				"name" => _("Page with Sidebar"),
				"description" => _("Chose this if you want a page with a main column and a sidebar column."),
				"icon" => "PageWithSideBar.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart'),
				"name" => _("Sub Menu"),
				"description" => _("Chose this if you want to add another level of navigation in which you can add any number of sub-pages."),
				"icon" => "SubMenu.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart'),
				"name" => _("Sub Menu with Sidebar"),
				"description" => _("Chose this if you want to add another level of navigation in which you can add any number of sub-pages AND include a right sidebar for content common to all these sub-pages!"),
				"icon" => "SubMenuWithSideBar.png"
			);
		return $types;
	}
	
	/**
	 * Answer the url to return to
	 * 
	 * @return string
	 * @access public
	 * @since 6/4/07
	 */
	function getReturnUrl () {
		if (isset($this->_newIsNav) && $this->_newIsNav && isset($this->_newId)) {
			$harmoni = Harmoni::instance();
			return $harmoni->request->quickURL(
				'ui1', 'editNav',
				array('node' => $this->_newId,
					'returnAction' => $harmoni->request->get("returnAction"),
					'returnNode' => $this->_newId));
		} else {
			return parent::getReturnUrl();
		}
	}
}

?>