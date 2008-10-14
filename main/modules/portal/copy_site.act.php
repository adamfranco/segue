<?php
/**
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(MYDIR."/main/modules/dataport/Rendering/DomExportSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * This action will copy a site to a new placeholder
 * 
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class copy_siteAction
	extends Action
{
		
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/28/08
	 */
	public function isAuthorizedToExecute () {
		$siteAsset = $this->getSourceSiteAsset();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (RequestContext::value('command') == 'copy')
			// Currently just check for modify to see if there is 'site-level editor' access.
			// In the future, maybe this should be its own authorization.
			$toCheck = $idMgr->getId('edu.middlebury.authorization.modify');
		else if (RequestContext::value('command') == 'move')
			$toCheck = $idMgr->getId('edu.middlebury.authorization.delete');
		else
			throw new InvalidArgumentException("Invalid command. Must be move or copy.");
			
		if ($authZ->isUserAuthorized(
			$toCheck, 
			$siteAsset->getId()))
		{
			// Check to see that the user is an owner of the destination slot.
			$slot = $this->getDestSlot();
			if ($slot->isUserOwner())
				return true;
		}
		return false;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException(_("Your are not authorized to copy this site here."));
		
		$srcSlot = $this->getSourceSlot();
		$srcSiteAsset = $this->getSourceSiteAsset();
		$director = SiteDispatcher::getSiteDirector();
		$srcComponent = $director->getSiteComponentFromAsset($srcSiteAsset);
		$destSlot = $this->getDestSlot();
		
		if (RequestContext::value('command') == 'copy') {
			try {
				/*********************************************************
				 * Export the Site
				 *********************************************************/
				$exportDir = DATAPORT_TMP_DIR."/".$srcSlot->getShortname()."-".str_replace(':', '_', DateAndTime::now()->asString());
				mkdir($exportDir);
				
				// Do the export
				$visitor = new DomExportSiteVisitor($exportDir);
				$visitor->enableStatusOutput(_("Exporting from original location."));
				$srcComponent->acceptVisitor($visitor);
				$doc = $visitor->doc;
				
				// Validate the result
	// 			printpre(htmlentities($doc->saveXMLWithWhitespace()));
	// 			$tmp = new Harmoni_DomDocument;
	// 			$tmp->loadXML($doc->saveXMLWithWhitespace());
	// 			$tmp->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
				
				$doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
				
	// 			printpre($this->listDir($exportDir));
	// 			throw new Exception('test');
				
				/*********************************************************
				 * Import the site
				 *********************************************************/
				$importer = new DomImportSiteVisitor($doc, $exportDir, $director);
				if (RequestContext::value('copy_permissions') == 'true')
					$importer->enableRoleImport();
				
				if (RequestContext::value('copy_discussions') != 'true')
					$importer->disableCommentImport();
				
	// 			if (isset($values['owners'])) {
	// 				$idMgr = Services::getService('Id');
	// 				foreach($values['owners']['admins'] as $adminIdString)
	// 					$importer->addSiteAdministrator($idMgr->getId($adminIdString));
	// 			}
				
				$importer->enableStatusOutput(_("Importing into new location"));
				$importer->makeUserSiteAdministrator();			
				$site = $importer->importAtSlot($destSlot->getShortname());
				
				// Delete the decompressed Archive
				$this->deleteRecursive($exportDir);
				
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
					
					$item = new AgentNodeEntryItem("Copy Site", "Site copied from placeholder, '".$srcSlot->getShortname()."' to '".$destSlot->getShortname()."'.");
					$item->addNodeId($site->getQualifierId());
					
					$log->appendLogWithTypes($item,	$formatType, $priorityType);
				}
				
			} catch (Exception $e) {
				$this->deleteRecursive($exportDir);
				
				if (file_exists($exportDir.".tar.gz"))
					unlink($exportDir.".tar.gz");
				
				throw $e;
			}
		}
		
		// Move
		else {
			// Detach the site from the source slot.
			$srcSlot->deleteSiteId();
			
			// Attach the site to the dest slot.
			$destSlot->setSiteId($srcSiteAsset->getId());
			
			// Make the source an alias of the destination so that links still work.
			$srcSlot->makeAlias($destSlot);
			
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
				
				$item = new AgentNodeEntryItem("Move Site", "Site moved from placeholder, '".$srcSlot->getShortname()."' to '".$destSlot->getShortname()."'.");
				$item->addNodeId($destSlot->getSiteId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		}
		
		// Remove from selection?
		if (RequestContext::value('remove_after_use') == 'remove') {
			$selection = Segue_Selection::instance();
			$selection->removeSiteComponent($srcComponent);
		}
		
		print "\n"._("Done");
		exit;
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
	
	/**
	 * Recursively list a directory
	 * 
	 * @param string $path
	 * @return void
	 * @access protected
	 * @since 7/28/08
	 */
	protected function listDir ($path, $tabs = "") {
		ob_start();
		if (is_dir($path))
			print "\n\t";
		else
			print "\n".ByteSize::withValue(filesize($path))->asString();
		
		print $tabs.basename($path);
		
		if (is_dir($path)) {
			$entries = scandir($path);
			foreach ($entries as $entry) {
				if ($entry != '.' && $entry != '..') {
					print $this->listDir($path.DIRECTORY_SEPARATOR.$entry, $tabs."\t");
				}
			}
		}
		return ob_get_clean();
	}
	
	/**
	 * Answer the source slot
	 * 
	 * @return object Slot
	 * @access protected
	 * @since 7/28/08
	 */
	protected function getSourceSlot () {
		if (!preg_match('/^[a-z0-9_\.-]+$/i', RequestContext::value('srcSiteId')))
			throw new InvalidArgumentException("Invalid site id.");
		
		$slotMgr = SlotManager::instance();
		return $slotMgr->getSlotBySiteId(RequestContext::value('srcSiteId'));
	}
	
	/**
	 * Answer the source site asset.
	 * 
	 * @return object Asset
	 * @access protected
	 * @since 7/28/08
	 */
	protected function getSourceSiteAsset () {
		$slot = $this->getSourceSlot();
		return $slot->getSiteAsset();
	}
	
	/**
	 * Answer the slot.
	 * 
	 * @return object Slot
	 * @access protected
	 * @since 7/28/08
	 */
	protected function getDestSlot () {
		if (!preg_match('/^[a-z0-9_\.-]+$/i', RequestContext::value('destSlot')))
			throw new InvalidArgumentException("Invalid slot name.");
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname(RequestContext::value('destSlot'));
		if ($slot->siteExists()) 
			throw new OperationFailedException("Cannot copy site, slot already full.");
		
		return $slot;
	}
	
}

?>