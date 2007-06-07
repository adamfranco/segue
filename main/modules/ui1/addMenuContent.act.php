<?php
/**
 * @since 6/5/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addMenuContent.act.php,v 1.2 2007/06/07 18:04:18 adamfranco Exp $
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
 * @version $Id: addMenuContent.act.php,v 1.2 2007/06/07 18:04:18 adamfranco Exp $
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
	function &createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		$saveButton =& $wizard->getSaveButton();
		$saveButton->setLabel(_("Create >>"));
		
		$wizard->addStep("nav", $this->getNavStep());
		
		$wizard->addStep("content", $this->getContentStep());
		
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
		$wizard =& $this->getWizard($cacheName);
		
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
	function &getNavStep () {
		$pluginManager =& Services::getService('PluginManager');
		
		$step =& new WizardStep();
		$step->setDisplayName(_("Create New Navigation"));
		
		$property =& $step->addComponent("organizerId", new WHiddenField());
		$property->setValue(RequestContext::value('organizerId'));
		
		$property =& $step->addComponent("type", new WRadioList());
		
		$navTypes = $this->getNavTypes();
		
		foreach ($navTypes as $i => $navArray) {
			ob_start();
			print "\n<div>";
			$icon = MYPATH."/icons/".$navArray['icon'];
			print "\n\t<img src='".$icon."' width='200px' align='left' style='margin-right: 5px; margin-bottom: 5px;' alt='icon' />";
			print "\n\t<div>".$navArray['description']."</div>";
			print "\n</div>";
			print "\n<div style='clear: both;'></div>";
			$property->addOption($navArray['type']->asString(), 
				"<strong>".$navArray['name']."</strong>", 
				ob_get_clean());
				
			if (!$i) {
				$property->setValue($navArray['type']->asString());
				$set = true;
			}
		}
		
		// Create the step text
		ob_start();
				
		print "\n<div><strong>"._("Select a Navigation type and click 'Create >>' or click 'Next' to choose a Content item to add to the menu:")."</strong>";
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
		$director =& $this->getSiteDirector();
		$organizer =& $this->getSiteComponentForIdString($values['organizerId']);
		$componentType =& Type::fromString($values['type']);
		
		if ($componentType->getDomain() == 'segue-multipart')
			$component =& addComponentAction::createMultipartComponent($director, $componentType, $organizer);
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
				"name" => _("Content Page"),
				"description" => _("A single page of content."),
				"icon" => "Page.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart'),
				"name" => _("Content Page with Sidebar"),
				"description" => _("A single page of content with a sidebar that will be present when the page is viewed."),
				"icon" => "PageWithSideBar.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart'),
				"name" => _("Sub-Menu"),
				"description" => _("This option adds a new level of navigation under which Content Pages or other Sub-Menus can be added."),
				"icon" => "SubMenu.png"
			);
		$types[] = array(
				"type" => new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart'),
				"name" => _("Sub-Menu with Sidebar"),
				"description" => _("This option adds a new level of navigation under which Content Pages or other Sub-Menus can be added. A sidebar will also be present whenever any of the pages in this Sub-Menu are viewed."),
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
			$harmoni =& Harmoni::instance();
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