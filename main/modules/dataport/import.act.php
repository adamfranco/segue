<?php
/**
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.14 2008/04/01 13:36:30 adamfranco Exp $
 */ 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

// Use a custom version of Archive/Tar if requested.
if (defined('ARCHIVE_TAR_PATH'))
	require_once(ARCHIVE_TAR_PATH);
else
	require_once("Archive/Tar.php");


require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");

require_once(dirname(__FILE__)."/Rendering/DomImportSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/UntrustedAgentDomImportSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/UntrustedAgentAndTimeDomImportSiteVisitor.class.php");


/**
 * This action will import a site into the slot-name given.
 * 
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.14 2008/04/01 13:36:30 adamfranco Exp $
 */
class importAction
	extends MainWindowAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/22/08
	 */
	public function isAuthorizedToExecute () {
		// For now, just make this admin-only
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.add_children"),
 					$idManager->getId("edu.middlebury.authorization.root"));
		
		// This is probably the way to do it if we want it open and not admin-only
		$slot = $this->getSlot();
		if ($slot->isUserOwner())
			return true;
		else
			return false;
	}
	
	/**
	 * Answer the slot for this action.
	 * 
	 * @return object SegueSlot
	 * @access protected
	 * @since 1/28/08
	 */
	protected function getSlot () {
		$slotMgr = SlotManager::instance();
		return $slotMgr->getSlotByShortname($this->getSlotName());
	}
	
	/**
	 * Answer the name of the requested slot.
	 * 
	 * @return string
	 * @access protected
	 * @since 3/14/08
	 */
	protected function getSlotName () {
		return RequestContext::value('site');
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 1/28/08
	 */
	function getHeadingText () {	
		$slot = $this->getSlot();
		return str_replace('%1', $slot->getShortname(), _("Import a site into the '%1' placeholder."));
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 1/28/08
	 */
	public function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("site");
		
		$centerPane = $this->getActionRows();
		$cacheName = 'import_site_wizard_'.RequestContext::value('site');
		
		$this->runWizard ( $cacheName, $centerPane );
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 1/28/08
	 */
	public function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = RequiredStepWizard::withDefaultLayout();
		
		$step = $wizard->addStep("mode", new WizardStep());
		$step->setDisplayName(_("Import Mode"));
		
		ob_start();
		
		$property = $step->addComponent('slotname', new WTextField());
		$property->setValue($this->getSlot()->getShortname());
		$property->setEnabled(false, true);
		
		print "\n<p>";
		print "\n\t"._("The site backup file uploaded will be imported into the following placeholder:");
		print "\n\t<br/>[[slotname]]\n</p>";
		
		$property = $step->addComponent('backup_file', new WFileUploadField());
		$wizard->backupFile = $property; // Save a reference for later use.
		print "\n<p>";
		print "\n\t"._("Please choose a Segue backup file to import:");
		print "\n\t<br/>[[backup_file]]\n</p>";
		
		$property = $step->addComponent('comments', new WCheckbox());
		$property->setValue(true);
		print "\n<p>[[comments]] ";
		print "\n\t"._("Import discussions?");
		print "\n\t\n</p>";
		
		$property = $step->addComponent('roles', new WCheckbox());
		$property->setValue(true);
		print "\n<p>[[roles]] ";
		print "\n\t"._("Import roles/permissions?");
		print "\n\t\n</p>";
		
		$property = $step->addComponent('trust', new WSelectList());
		$property->addOption('all', _('Trust both'));
		$property->addOption('time_only', _('Trust timestamps, but not agents'));
		$property->addOption('none', _('Do not trust timestamps or agents'));
		$property->setValue('all');
		print "\n<p>";
		print "\n\t"._("Trust Level:");
		print "\n\t [[trust]]";
		print "\n\t<br/>";
		print _("This option sets whether or not to trust the agents and timestamps listed in the backup file. If this file may have been maliciously changed to alter the history recorded in it, use one of the lower trust settings to force the agent or timestamp to be those of the current agent/time at the moment of import.");
		print "\n</p>";
		
		$step->setContent(ob_get_clean());
		
		// Site Admins.
		$this->addSiteAdminStep($wizard);
		
		return $wizard;
	}
	
	/**
	 * Add any additional site admins to a multi-select.
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access protected
	 * @since 1/28/08
	 */
	protected function addSiteAdminStep (Wizard $wizard) {
		/*********************************************************
		 * Owner step if multiple owners
		 *********************************************************/
		$step = new WizardStep();
		$step->setDisplayName(_("Choose Admins"));	
		
		$property = $step->addComponent("admins", new WMultiCheckList);
		
		$agentMgr = Services::getService("Agent");
		$i = 0;
		$owners = $this->getOwners();
		foreach ($owners as $ownerId) {
			$i++;
			$owner = $agentMgr->getAgent($ownerId);
			$property->addOption($ownerId->getIdString(), htmlspecialchars($owner->getDisplayName()));
			$property->setValue($ownerId->getIdString());
		}
		$property->setSize($i);
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Choose Site Admins")."</h2>";
		print "\n<p>"._("The following users are listed as owners of this placeholder. Keep them selected if you would like them be administrators of this site or de-select them if they should not be administrators of this site. Any choice made now can be changed later through the 'Permissions' screen for the site.");
		print "\n<br />[[admins]]</p>";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		if ($i) {
			$step = $wizard->addStep("owners", $step);
			$wizard->makeStepRequired('owners');
		}
	}
	
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 1/28/08
	 */
	public function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		if (!$wizard->validate()) return false;
		
		$values = $wizard->getAllValues();
// 		printpre($values);
// 		return false;
		
		try {
			if (!defined('DATAPORT_TMP_DIR'))
				throw new Exception("DATAPORT_TMP_DIR must be defined in the Segue configuration.");
			
			$archivePath = $values['mode']['backup_file']['tmp_name'];
			$archiveName = basename($archivePath);
			$decompressDir = DATAPORT_TMP_DIR.'/'.$archiveName.'_source';
			
			if (!$values['mode']['backup_file']['size'])
				throw new Exception("File upload error - archive was not successfully uploaded and has no size.");
			$this->decompressArchive($archivePath, $decompressDir);
			
			
			// Do the import
			$director = SiteDispatcher::getSiteDirector();
			
			$doc = new Harmoni_DOMDocument;
			$doc->load($decompressDir."/site.xml");
			// Validate the document contents
			$doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			
			$mediaDir = $decompressDir;
			
			switch ($values['mode']['trust']) {
				case 'all':
					$class = 'DomImportSiteVisitor';
					break;
				case 'time_only':
					$class = 'UntrustedAgentDomImportSiteVisitor';
					break;
				default:
					$class = 'UntrustedAgentAndTimeDomImportSiteVisitor';
			}
			$importer = new $class($doc, $mediaDir, $director);
			if ($values['mode']['roles'] == '1')
				$importer->enableRoleImport();
			
			if ($values['mode']['comments'] == '0')
				$importer->disableCommentImport();
			
			if (isset($values['owners'])) {
				$idMgr = Services::getService('Id');
				foreach($values['owners']['admins'] as $adminIdString)
					$importer->addSiteAdministrator($idMgr->getId($adminIdString));
			}
			
			$importer->enableStatusOutput();
			$site = $importer->importAtSlot($values['mode']['slotname']);
			
			// Delete the uploaded file
			unlink($archivePath);
			
			// Delete the decompressed Archive
			$this->deleteRecursive($decompressDir);
			
		} catch (Exception $importException) {
			// Delete the uploaded file
			try {
				if (file_exists($archivePath))
					unlink($archivePath);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
			// Delete the decompressed Archive
			try {
				if (file_exists($decompressDir))
					$this->deleteRecursive($decompressDir);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
			print "\n<div>\n\t";
			print $importException->getMessage();
// 			print HarmoniErrorHandler::printDebugBacktrace($importException->getTrace());
			print "\n</div>";
			
			$wizard->backupFile->setValue(array('name' => null, 'size' => null, 'type' => null));
			
			/*********************************************************
			 * Log the failure
			 *********************************************************/
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log = $loggingManager->getLogForWriting("Segue");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Error",
								"Recoverable errors.");
				
				$item = new AgentNodeEntryItem("Create Site", "Failure in importing site for placeholder, '".$values['mode']['slotname']."'. ".$importException->getMessage());
				$item->setBacktrace($importException->getTrace());
				$item->addTextToBactrace("Archive Upload: ".printpre($values['mode']['backup_file'], true));
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			return false;
		}
		
		/*********************************************************
		 * Log the success
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Create Site", "Site imported for placeholder, '".$values['mode']['slotname']."'.");
			$item->addNodeId($site->getQualifierId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		return true;
	}
	
	/**
	 * Decompress a tar archive of a site.
	 * 
	 * @param string $archivePath
	 * @param string $decompressDir
	 * @return void
	 * @access public
	 * @since 3/14/08
	 */
	public function decompressArchive ($archivePath, $decompressDir) {
		if (!file_exists($archivePath))
			throw new Exception("Archive, '".basename($archivePath)."' does not exist.");
		if (!is_readable($archivePath))
			throw new Exception("Archive, '".basename($archivePath)."' is not readable.");
		
		// Decompress the archive into our temp-dir
		$archive = new Archive_Tar($archivePath);
		
		// Check for a containing directory and strip it if needed.
		$content = @$archive->listContent();
		if (!is_array($content) || !count($content))
			throw new Exception("Invalid Segue archive. '".basename($archivePath)."' is not a valid GZIPed Tar archive.");
		$containerName = null;
// 			printpre($content);
		if ($content[0]['typeflag'] == 5) {
			$containerName = trim($content[0]['filename'], '/').'/';
			for ($i = 1; $i < count($content); $i++) {
				// if one of the files isn't in the container, then we don't have a container of all
				if (strpos($content[$i]['filename'], $containerName) === false) {
					$containerName = null;
					break;
				}
			}
		}
// 			printpre($containerName);
		
		$decompressResult = @$archive->extractModify($decompressDir, $containerName);
		if (!$decompressResult)
			throw new Exception("Could not decompress Segue archive: '".basename($archivePath)."' size, ".ByteSize::withValue(filesize($archivePath))->asString().".");
			
		if (!file_exists($decompressDir."/site.xml"))
			throw new Exception("Invalid Segue archive. 'site.xml' was not found in '".implode("', '", scandir($decompressDir))."'.");
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 1/28/08
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$harmoni->request->forget('site');
		if (isset($this->_siteId) && $this->_siteId) 
			return $harmoni->request->quickURL('ui1', "view", array(
				"node" => $this->_siteId));
		else
			return $harmoni->request->quickURL('slots', "browse");
	}
	
	/**
	 * Answer a list of owners to add to the Site Admins step.
	 *
	 * @return array
	 * @access protected
	 * @since 1/28/08
	 */
	protected function getOwners () {
		// In this case we don't want to filter out the user Id
		// because this is an admin-only action and the admin user
		// running it may or may not be one of the desired owners.
		$slot = $this->getSlot();
		return $slot->getOwners();
	}
	
	/**
	 * Recursively delete a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function deleteRecursive ($path) {
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					$this->deleteRecursive($path.DIRECTORY_SEPARATOR.$entry);
				}
			}
			rmdir($path);
		} else {
			unlink($path);
		}
	}
	
}

?>