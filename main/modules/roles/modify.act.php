<?php
/**
 * @since 11/14/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.1 2007/11/15 19:22:38 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsAuthorizableVisitor.class.php");
require_once(dirname(__FILE__)."/Visitors/PopulateRolesVisitor.class.php");


/**
 * An action for editing permissions of a particular site
 * 
 * @since 11/14/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.1 2007/11/15 19:22:38 adamfranco Exp $
 */
class modifyAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/14/07
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$centerPane = $this->getActionRows();
		$qualifierId = $this->getSiteId();
		$cacheName = get_class($this).'_'.$qualifierId->getIdString();
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$this->runWizard ( $cacheName, $centerPane );
	}
	
	/**
	 * Create the wizard
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 11/14/07
	 */
	public function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SingleStepWizard::withDefaultLayout();
		
		$step = $wizard->addStep("permissions", new WizardStep);
		$step->setDisplayName(_("Permissions"));
		
		
		$property = $step->addComponent("perms_table", new RowHierarchicalRadioMatrix);
		
		
		$roleMgr = SegueRoleManager::instance();
		// Add the options
		foreach($roleMgr->getRoles() as $role)
			$property->addOption($role->getIdString(), $role->getDisplayName(), $role->getDescription());
		
		$idMgr = Services::getService("Id");
		$agentId = $idMgr->getId('edu.middlebury.institute');
		
		$this->getSite()->acceptVisitor(new PopulateRolesVisitor($property, $agentId));
		
		
		ob_start();
		print "\n<h2>"._("Permissions")."</h2>";
		print "\n<p>";
		print _("Permissions are additive -- this means that you can add additional permissions (but not remove them) for child-nodes.");
		print "\n</p>\n";
		
		print "\n<p>";
		print _("Tip: Hold down the <em>SHIFT</em> key while clicking to revoke roles from child-nodes.");
		print "\n</p>\n";
		
		print "[[perms_table]]";
		
		$step->setContent(ob_get_clean());
		
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
			
			// todo .....
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSiteId () {
		$idManager = Services::getService("Id");
		return $idManager->getId($this->getSite()->getId());
	}
	
	/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSite () {
		$siteComponent = $this->getSiteComponent();
		return $siteComponent->getDirector()->getRootSiteComponent($siteComponent->getId());
	}
	
	/**
	 * Answer the qualifier Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getQualifierId () {
		$component = $this->getSiteComponent();
		return $component->getQualifierId();
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponent () {
		$idManager = Services::getService("Id");
		return $this->getSiteComponentForId(
			$idManager->getId(RequestContext::value("node")));
	}
	
	/**
	 * Answer the site component for a given Id
	 * 
	 * @param object Id $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponentForId ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id->getIdString());
	}
	
	/**
	 * Answer the site component for a given Id string
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 6/4/07
	 */
	protected function getSiteComponentForIdString ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id);
	}
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access protected
	 * @since 4/14/06
	 */
	protected function getSiteDirector () {
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
}

?>