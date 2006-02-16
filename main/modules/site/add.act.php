<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.3 2006/02/16 22:06:57 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.3 2006/02/16 22:06:57 adamfranco Exp $
 */
class addAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"),
			$idManager->getId("edu.middlebury.segue.sites_repository"));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a <em>Site</em> here.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("parent_id");
		
		$centerPane =& $this->getActionRows();
		$cacheName = 'add_site_wizard_'.RequestContext::value('parent_id');
		
		$this->runWizard ( $cacheName, $centerPane );
	}
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {	
		return _("Add a Site");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp =& $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
// 		
// 		// :: Content ::
// 		$step =& $wizard->addStep("contentstep", new WizardStep());
// 		$step->setDisplayName(_("Content")." ("._("optional").")");
// 		
// 		$property =& $step->addComponent("content", WTextArea::withRowsAndColumns(20,50));
// 		
// 		// Create the step text
// 		ob_start();
// 		print "\n<h2>"._("Content")."</h2>";
// 		print "\n"._("This is an optional place to put content for this <em>Asset</em>. <br />If you would like more structure, you can create new schemas to hold the <em>Asset's</em> data.");
// 		print "\n<br />[[content]]";
// 		print "\n<div style='width: 400px'> &nbsp; </div>";
// 		$step->setContent(ob_get_contents());
// 		ob_end_clean();
		
		// :: Effective/Expiration Dates ::
		$step =& $wizard->addStep("datestep", new WizardStep());
		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
		
		// Create the properties.
		$property =& $step->addComponent("effective_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
	
		$property =& $step->addComponent("expiration_date", new WTextField());
	//	$property->setDefaultValue();
//		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Effective Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
		print "\n<br />[[effective_date]]";
		
		print "\n<h2>"._("Expiration Date")."</h2>";
		print "\n"._("The date that this <em>Asset</em> expires: ");
		print "\n<br />[[expiration_date]]";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		
// 		
// 		// :: Parent ::
// 		$step =& $wizard->addStep("parentstep", new WizardStep());
// 		$step->setDisplayName(_("Parent")." ("._("optional").")");
// 		
// 		// Create the properties.
// 		$property =& $step->addComponent("parent", new WSelectList());
// 		$harmoni =& Harmoni::instance();
// 		
// 		$property->addOption("NONE", _("None"));
// 		
// 		$assets =& $repository->getAssets();
// 		$authZManager =& Services::getService("AuthZ");
// 		$idManager =& Services::getService("Id");
// 		while ($assets->hasNext()) {
// 			$asset =& $assets->next();
// 			$assetId =& $asset->getId();
// 			if ($authZManager->isUserAuthorized(
// 				$idManager->getId("edu.middlebury.authorization.add_children"),
// 				$assetId))
// 			{
// 				$property->addOption($assetId->getIdString(), $assetId->getIdString()." - ".$asset->getDisplayName());
// 			}
// 		}
// 		
// 		if (RequestContext::value('parent'))
// 			$property->setValue(RequestContext::value('parent'));
// 		else
// 			$property->setValue("NONE");
// 				
// 		// Create the step text
// 		ob_start();
// 		print "\n<h2>"._("Parent <em>Asset</em>")."</h2>";
// 		print "\n"._("Select one of the <em>Assets</em> below if you wish to make this new asset a child of another asset: ");
// 		print "\n<br />[[parent]]";
// 		
// 		$step->setContent(ob_get_contents());
// 		ob_end_clean();
		
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
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		
		if (!$wizard->validate()) return false;
		
		// Make sure we have a valid Repository
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		$properties = $wizard->getAllValues();
		
		// First, verify that we chose a parent that we can add children to.
		if (!isset($properties['parentstep']['parent'])
			|| !$properties['parentstep']['parent'] 
			|| $properties['parentstep']['parent'] == 'NONE'
			|| ($parentId =& $idManager->getId($properties['parentstep']['parent'])
				&& $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.add_children"), $parentId)))
		{
		
			$assetType = new HarmoniType('site_components', 
										'edu.middlebury.segue', 
										'site', 
										'An Asset of this type is the root node of a Segue site.');
			
			$asset =& $repository->createAsset($properties['namedescstep']['display_name'], 
										$properties['namedescstep']['description'], 
										$assetType);
										
			$assetId =& $asset->getId();
			$this->_assetId =& $assetId;
			
// 			$content =& Blob::withValue($properties['contentstep']['content']);
// 			$asset->updateContent($content);
			
			// Update the effective/expiration dates
			if ($properties['datestep']['effective_date'])
				$asset->updateEffectiveDate(
					DateAndTime::fromString($properties['datestep']['effective_date']));
			if ($properties['datestep']['expiration_date'])
				$asset->updateExpirationDate(
					DateAndTime::fromString($properties['datestep']['expiration_date']));
			
			// Add our parent if we have specified one.
			if (isset($properties['parentstep']['parent'])
				&& $properties['parentstep']['parent'] 
				&& $properties['parentstep']['parent'] != 'NONE') 
			{
				$parentId =& $idManager->getId($properties['parentstep']['parent']);
				$parentAsset =& $repository->getAsset($parentId);
				$parentAsset->addAsset($assetId);
			}
			
			return TRUE;
		} 
		// If we don't have authorization to add to the picked parent, send us back to
		// that step.
		else {
			$wizard->setStep("parentstep");
			return FALSE;
		}
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		if ($this->_assetId) 
			return $harmoni->request->quickURL("site", "view", array(
				"node" => $this->_assetId->getIdString()));
		else
			return $harmoni->request->quickURL();
	}
}

?>