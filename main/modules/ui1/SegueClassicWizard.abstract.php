<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueClassicWizard.abstract.php,v 1.9 2007/09/05 14:09:35 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");

/**
 * This is an abstract action class with common functionality for all Segue
 * classic-mode wizards
 * 
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueClassicWizard.abstract.php,v 1.9 2007/09/05 14:09:35 adamfranco Exp $
 */
class SegueClassicWizard
	extends MainWindowAction
{
		
	/**
	 * Answer true if the user is authorized to run this wizard.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/8/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ = Services::getService("AuthZ");
		return $authZ->isUserAuthorized(
					$this->getAuthFunctionId(), 
					$this->getQualifierId());
	}
	
	/**
	 * Answer the authorization function Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function getAuthFunctionId () {
		$idManager = Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.modify");
	}
	
	/**
	 * Answer the qualifier Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function getQualifierId () {
		$component = $this->getSiteComponent();
		return $component->getQualifierId();
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access public
	 * @since 5/8/07
	 */
	function getSiteComponent () {
		$idManager = Services::getService("Id");
		return $this->getSiteComponentForId(
			$idManager->getId(RequestContext::value("node")));
	}
	
	/**
	 * Answer the site component for a given Id
	 * 
	 * @param object Id $id
	 * @return object SiteComponent
	 * @access public
	 * @since 5/8/07
	 */
	function getSiteComponentForId ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id->getIdString());
	}
	
	/**
	 * Answer the site component for a given Id string
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access public
	 * @since 6/4/07
	 */
	function getSiteComponentForIdString ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id);
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 5/8/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$centerPane = $this->getActionRows();
		$qualifierId = $this->getQualifierId();
		$cacheName = get_class($this).'_'.$qualifierId->getIdString();
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$this->runWizard ( $cacheName, $centerPane );
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
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("namedesc", $this->getTitleStep());
		$wizard->addStep("display", $this->getDisplayOptionsStep());
		$wizard->addStep("status", $this->getStatusStep());
		
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
			
			if (!$this->saveTitleStep($properties['namedesc']))
				return FALSE;
			if (!$this->saveDisplayOptionsStep($properties['display']))
				return FALSE;
			if (!$this->saveStatusStep($properties['status']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the url to return to
	 * 
	 * @return string
	 * @access public
	 * @since 5/8/07
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$harmoni->request->forget("returnNode");
		$harmoni->request->forget("returnAction");
		return $harmoni->request->quickURL(
			'ui1', $harmoni->request->get("returnAction"),
			array('node' => $harmoni->request->get("returnNode")));
	}
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access public
	 * @since 4/14/06
	 */
	function getSiteDirector () {
			if (!isset($this->_director)) {
			/*********************************************************
			 * XML Version
			 *********************************************************/
	// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
	// 		
	// 		$this->document = new DOMIT_Document();
	// 		$this->document->setNamespaceAwareness(true);
	// 		$success = $this->document->loadXML($this->filename);
	// 
	// 		if ($success !== true) {
	// 			throwError(new Error("DOMIT error: ".$this->document->getErrorCode().
	// 				"<br/>\t meaning: ".$this->document->getErrorString()."<br/>", "SiteDisplay"));
	// 		}
	// 
	// 		$director = new XmlSiteDirector($this->document);
			
			
			/*********************************************************
			 * Asset version
			 *********************************************************/
			$repositoryManager = Services::getService('Repository');
			$idManager = Services::getService('Id');
			
			$this->_director = new AssetSiteDirector(
				$repositoryManager->getRepository(
					$idManager->getId('edu.middlebury.segue.sites_repository')));
		}
		
		return $this->_director;
	}
	
/*********************************************************
 * Steps
 *********************************************************/

	
	/**
	 * Create the step for adding the title and description
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getTitleStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Title &amp; Description"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("display_name", new WTextField());
		$property->setSize(80);
		if ($component)
			$property->setValue($component->getDisplayName());
		$property->setErrorText(_("A value for this field is required."));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));

		print "\n<p><strong>"._("Title:")."</strong>";
// 		print "\n"._("The title of content: ");
		print "\n<br />[[display_name]]</p>";
		
		
		$property = $step->addComponent("description", WTextArea::withRowsAndColumns(4,80));
		if ($component)
			$property->setValue($component->getDescription());
		print "\n<p><strong>"._("Description:")."</strong>";
// 		print "\n"._("The Description for this content: ");
		print "\n<br/>[[description]]";
		print "</p>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * save the name and description step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveTitleStep ($values) {
		$component = $this->getSiteComponent();
		
		$value = trim($values['display_name']);
		if (!$value)
			return false;
		$component->updateDisplayName($value);
		
		$value = trim($values['description']);
		$component->updateDescription($value);
		
		return true;
	}
	
	/**
	 * Create the step for adding the display options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getDisplayOptionsStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Display Options"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("show_titles", new WSelectList());
		$property->addOption('default', _("use default"));
		$property->addOption('true', _("override-show"));
		$property->addOption('false', _("override-hide"));
		$property->setValue('default');
		
		if ($component) {
			$val = $component->showDisplayNames();
			if ($val === true)
				$property->setValue('true');
			else if ($val === false)
				$property->setValue('false');
			
			$parent = $component->getParentComponent();
		} else {
			$parent = null;
		}
		

		print "\n<p><strong>"._("Display Content Titles:")."</strong> ";
		print "\n[[show_titles]]";
		
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				(($parent->showDisplayNames())?_('show'):_('hide')),
				_("Current default setting: %1"));
		}
		print "\n</p>";
		
		$property = $step->addComponent("enable_comments", new WSelectList());
		$property->addOption('default', _("use default"));
		$property->addOption('true', _("override-enable"));
		$property->addOption('false', _("override-disable"));
		$property->setValue('default');
		
		if ($component) {
			$val = $component->commentsEnabled();
			if ($val === true)
				$property->setValue('true');
			else if ($val === false)
				$property->setValue('false');
		}
		
		print "\n<p><strong>"._("Enable Comments:")."</strong> ";
		print "\n[[enable_comments]]";
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				(($parent->showComments())?_('enabled'):_('disabled')),
				_("Current default setting: %1"));
		}
		print "\n</p>";
		
		$step->setContent(ob_get_clean());
		
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
		$component = $this->getSiteComponent();
		$component->updateShowDisplayNames($values['show_titles']);
		$component->updateCommentsEnabled($values['enable_comments']);
		return true;
	}
	
	/**
	 * Create the step for status options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getStatusStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Status"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("status", new WRadioList());
		$property->addOption('published', _("Published"));
		$property->addOption('draft', _("Draft"));
		$property->addOption('date', _("Published during period _____ to _______"));
		
// 		if ($component) {
// 			$val = $component->showDisplayNames();
// 			if ($val === true)
// 				$property->setValue('true');
// 			else if ($val === false)
// 				$property->setValue('false');
// 		}
		

		print "\n<strong>"._("Status:")."</strong>";
		print "\n<br/>[[status]]";
		
		
// 		$property = $step->addComponent("description", WTextArea::withRowsAndColumns(4,80));
// 		if ($component)
// 			$property->setValue($component->getDescription());
// 		print "\n<h2>"._("Description")."</h2>";
// 		print "\n"._("The Description for this content: ");
// 		print "\n<br />[[description]]";
// 		print "\n<div style='width: 400px'> &nbsp; </div>";
		
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * save the status step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveStatusStep ($values) {
		return true;
	}
}

?>