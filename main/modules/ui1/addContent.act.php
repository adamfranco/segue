<?php
/**
 * @since 6/1/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addContent.act.php,v 1.9 2008/02/19 17:49:57 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(POLYPHONY."/main/library/Wizard/SingleStepWizard.class.php");
require_once(MYDIR."/main/library/Roles/SegueRoleManager.class.php");

/**
 * A 1-step wizard to choose what kind of content to create
 * 
 * @since 6/1/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: addContent.act.php,v 1.9 2008/02/19 17:49:57 adamfranco Exp $
 */
class addContentAction
	extends SegueClassicWizard
{
		
	/**
	 * Answer the authorization function Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function getAuthFunctionId () {
		$idManager = Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.add_children");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/8/07
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SingleStepWizard::withText(
				"<div>\n" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[_cancel]]\n" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n"
		);
		$saveButton = $wizard->getSaveButton();
		$saveButton->setLabel(_("Create >>"));
		
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
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveContentStep($properties['content']))
				return FALSE;
			
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
	function getContentStep () {
		$pluginManager = Services::getService('PluginManager');
		
		$step = new WizardStep();
		$step->setDisplayName(_("Create New Content"));
		
		$property = $step->addComponent("organizerId", new WHiddenField());
		$property->setValue(RequestContext::value('organizerId'));
		
		$property = $step->addComponent("type", new WSaveWithChoiceButtonList());
		
		$plugins = $pluginManager->getEnabledPlugins();
		
		$set = false;
		foreach ($plugins as $key => $pType) {
			ob_start();
			print "\n<div>";
			$icon = $pluginManager->getPluginIconUrl($pType);
			if ($icon) {
				print "\n\t<img src='".$icon."' width='300px' align='left' style='margin-right: 5px; margin-bottom: 5px;' alt='icon' />";
			}
			try {
				$class = $pluginManager->getPluginClass($pType);
				$name = call_user_func(array($class, 'getPluginDisplayName'));
			} catch (UnknownIdException $e) {
				$name = $pType->getKeyword();
			}
			print " <strong>".$name."</strong>";
			print "\n\t<div>".$pType->getDescription()."</div>";
			print "\n</div>";
			print "\n<div style='clear: both;'></div>";
			$property->addOption($key, 
				str_replace('%1', $name,_('Create %1 >> ')), 
				ob_get_clean());
			if (!$set) {
				$property->setValue($key);
				$set = true;
			}
		}
		
		// Create the step text
		ob_start();
				
		print "\n<div><strong>"._("Select a Content Type:")."</strong>";
// 		print "\n"._("The title of content: ");
		print "\n<br /><br />[[type]]</div>[[organizerId]]";
		
		$step->setContent(ob_get_clean());
		return $step;
	}
	
	/**
	 * Save the type step
	 * 
	 * @param array $values1
	 * @return boolean
	 * @access public
	 * @since 6/4/07
	 */
	function saveContentStep ($values) {
		$director = $this->getSiteDirector();
		$organizer = $this->getSiteComponentForIdString($values['organizerId']);
		$componentType = HarmoniType::fromString($values['type']);
		
		$component = $director->createSiteComponent($componentType, $organizer);
		
		// Check the Role of the user. If it is less than 'Editor', make them an editor
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getUsersRole($component->getQualifierId(), true);
		$editor = $roleMgr->getRole('editor');
		if ($role->isLessThan($editor))
			$editor->applyToUser($component->getQualifierId(), true);
		
		
		$this->_newId = $component->getId();
		return true;
	}
	
	/**
	 * Answer the url to return to
	 * 
	 * @return string
	 * @access public
	 * @since 6/4/07
	 */
	function getReturnUrl () {
		if (isset($this->_newId)) {
			$harmoni = Harmoni::instance();
			return $harmoni->request->quickURL(
				'ui1', 'editContent',
				array('node' => $this->_newId,
					'returnAction' => $harmoni->request->get("returnAction"),
					'returnNode' => $harmoni->request->get("returnNode")));
		} else {
			return parent::getReturnUrl();
		}
	}
}

?>