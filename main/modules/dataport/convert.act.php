<?php
/**
 * @since 2/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: convert.act.php,v 1.17 2008/04/01 13:36:30 adamfranco Exp $
 */ 

require_once(HARMONI."/oki2/SimpleTableRepository/SimpleTableRepositoryManager.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

require_once(dirname(__FILE__)."/Segue1To2Converter/Segue1To2Director.class.php");
require_once(dirname(__FILE__)."/import.act.php");
require_once(dirname(__FILE__)."/Rendering/Segue1MappingImportSiteVisitor.class.php");


/**
 * Convert a Segue1 site export to a Segue2 site export. This is pretty much just a test 
 * script for now.
 * 
 * @since 2/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: convert.act.php,v 1.17 2008/04/01 13:36:30 adamfranco Exp $
 */
class convertAction
	extends importAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/4/08
	 */
	public function isAuthorizedToExecute () {
		// @todo: load the list of source slots and dest slots and make sure that
		// the user is authorized to do the import.
		
		$authN = Services::getService("AuthN");
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 2/4/08
	 */
	public function buildContent () {
		try {
		
			$status = new StatusStars(_("Preparing Site Import"));
			$status->initializeStatistics(4);
			
			$destPath = DATAPORT_TMP_DIR."/Segue1Conversion-".$this->getDestSlotName();
			mkdir($destPath);
			$destFilePath = $destPath.'/media';
			mkdir($destFilePath);
			
			$status->updateStatistics();
			
			// Download and convert the site
			$doc = $this->convertFrom1To2($destFilePath, 'media');
			$doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			
			$status->updateStatistics();
			
			// Debug output
// 			$outputDoc2 = new Harmoni_DOMDocument;
// 			$outputDoc2->loadXML($doc->saveXMLWithWhitespace());
// 			printpre(htmlentities($outputDoc2->saveXML()));
// 			throw new Exception('test');
			
			// Add the user as the owner
			$authN = Services::getService("AuthN");
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotByShortname($this->getDestSlotName());
			$slot->addOwner($authN->getFirstUserId());
			
			// Make the slot personal if it matches the personal naming scheme.
			$userName = PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			if ($slot->getType() != Slot::personal 
				&& preg_match('/^'.$userName.'(-.+)?$/', $this->getDestSlotName())) 
			{
				$slot = $slotMgr->convertSlotToType($slot, Slot::personal);
			}
			
			$status->updateStatistics();
			
			// Import the converted site
			$director = SiteDispatcher::getSiteDirector();
			$importer = new Segue1MappingImportSiteVisitor($doc, $destPath, $director);
			$status->updateStatistics();
			$importer->enableStatusOutput();
			$importer->makeUserSiteAdministrator();
			$importer->enableRoleImport();
			$importer->setOrigenSlotname($this->getSourceSlotName());
			$importer->setDestinationSlotname($this->getDestSlotName());
			$site = $importer->importAtSlot($this->getDestSlotName());
			
			// Set the media quota if it is bigger than our default
			$quota = $importer->getMediaQuota();
			if ($quota > $slot->getMediaQuota()->value())
				$slot->setMediaQuota(ByteSize::withValue($quota));
						
			// Delete the output directory
			try {
				if (file_exists($destPath))
					$this->deleteRecursive($destPath);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
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
				
				$item = new AgentNodeEntryItem("Create Site", "Site imported for placeholder, '".$this->getDestSlotName()."', from Segue 1 placeholder, '".$this->getSourceSlotName()."'.");
				$item->addNodeId($site->getQualifierId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
						
			$harmoni = Harmoni::instance();
			RequestContext::sendTo($harmoni->request->quickURL('dataport', 'choose_site'));
			
		} catch (Exception $importException) {
			
			
			// Delete the output directory
			try {
				if (file_exists($destPath))
					$this->deleteRecursive($destPath);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
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
				
				$item = new AgentNodeEntryItem("Create Site", "Failure in importing site for placeholder, '".$this->getDestSlotName()."', from Segue 1 placeholder, '".$this->getSourceSlotName()."'.");
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			throw $importException;			
		}
	}
	
	/**
	 * Convert a Segue1 export into a Segue2 export
	 * 
	 * @param string $destFilePath The path that Segue2 export file will be placed in.
	 * @param string $relativeOutputFilePath The output file path relative to
	 * 				encode into the xml output.	 * @return object DOMDocument The Segue2 export document
	 * @access protected
	 * @since 3/14/08
	 */
	protected function convertFrom1To2 ($destFilePath, $relativeOutputFilePath) {
		try {
			$sourcePath = $this->downloadSegue1Export();
				
			$sourceFilePath = $sourcePath."/media";
			$sourceDocPath = $sourcePath."/site.xml";
	
			$sourceDoc = new Harmoni_DOMDocument;
			$sourceDoc->load($sourceDocPath);
			
			$converter = new Segue1To2Director($destFilePath, $relativeOutputFilePath);
			$outputDoc = $converter->convert($sourceDoc, $sourceFilePath);
			
			// Delete the source directory
			$this->cleanUpSourcePath($sourcePath);
			
		} catch (DOMException $e) {			
			$size = ByteSize::withValue(filesize($sourceDocPath));
			
			$this->cleanUpSourcePath($sourcePath);
			
			if ($e->getCode() === DOMSTRING_SIZE_ERR)
				throw new DOMException("The export of '".$this->getSourceSlotName()."' is too large to load (".$size->asString().") or contains an element that is too large to load.", DOMSTRING_SIZE_ERR);
			
			throw $e;
		} catch (Exception $e) {		
			if (isset($sourcePath))
				$this->cleanUpSourcePath($sourcePath);
			throw $e;
		}
		return $outputDoc;
	}
	
	/**
	 * Clean up our source docs
	 * 
	 * @param string $sourcePath
	 * @return void
	 * @access private
	 * @since 3/24/08
	 */
	private function cleanUpSourcePath ($sourcePath) {
		// Delete the source directory
		try {
			if (isset($sourcePath) && file_exists($sourcePath))
				$this->deleteRecursive($sourcePath);
		} catch (Exception $deleteException) {
			print "\n<div>\n\t";
			print $deleteException->getMessage();
			print "\n</div>";
		}
	}
	
	/**
	 * Download the segue1 export and decompress it into a temporary directory.
	 * 
	 * @return string The path of the decompressed export files.
	 * @access protected
	 * @since 3/14/08
	 */
	protected function downloadSegue1Export () {
		if (!defined('DATAPORT_TMP_DIR'))
			throw new ConfigurationErrorException("DATAPORT_TMP_DIR must be defined in the Segue configuration.");
				
		if (!defined('DATAPORT_SEGUE1_URL'))
			throw new ConfigurationErrorException('DATAPORT_SEGUE1_URL is not defined.');
		
		if (!defined('DATAPORT_SEGUE1_SECRET_KEY'))
			throw new ConfigurationErrorException('DATAPORT_SEGUE1_SECRET_KEY is not defined.');
			
		if (!defined('DATAPORT_SEGUE1_SECRET_VALUE'))
			throw new ConfigurationErrorException('DATAPORT_SEGUE1_SECRET_VALUE is not defined.');
		
		$sourceUrl = DATAPORT_SEGUE1_URL.'/export/getSiteExport.php?site='.$this->getSourceSlotName()
				.'&'.DATAPORT_SEGUE1_SECRET_KEY.'='.DATAPORT_SEGUE1_SECRET_VALUE;
		
		$destDir = DATAPORT_TMP_DIR."/Segue1Export-".$this->getSourceSlotName();
		mkdir($destDir);
		$destArchive = $destDir.".tar.gz";
		
		try {
			if (!copy($sourceUrl, $destArchive))
				throw new OperationFailedException('Could not download Segue 1 export for '.$this->getSourceSlotName().'.');
			
			$this->decompressArchive($destArchive, $destDir);
			
			// Delete the archive file
			try {
				if (file_exists($destArchive))
					unlink($destArchive);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
		} catch (Exception $e) {
			// Delete the archive file
			try {
				if (file_exists($destArchive))
					unlink($destArchive);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
			// Delete the decompressed directory
			try {
				if (file_exists($destDir))
					$this->deleteRecursive($destDir);
			} catch (Exception $deleteException) {
				print "\n<div>\n\t";
				print $deleteException->getMessage();
				print "\n</div>";
			}
			
			throw $e;
		}
		
		return $destDir;
	}
	
	/**
	 * Answer the name of the requested slot.
	 * 
	 * @return string
	 * @access protected
	 * @since 3/14/08
	 */
	protected function getSlotName () {
		return $this->getDestSlotName();
	}
	
	/**
	 * Answer the Segue1 source slot name
	 *
	 * @return return string
	 * @access protected
	 * @since 3/14/08
	 */
	protected function getSourceSlotName () {
		if (!RequestContext::value('source_slot'))
			throw new NullArgumentException("No source placeholder/slot specified.");
		return RequestContext::value('source_slot');
	}
	
	/**
	 * Answer the Segue2 destination slot name
	 *
	 * @return return string
	 * @access protected
	 * @since 3/14/08
	 */
	protected function getDestSlotName () {
		if (!RequestContext::value('dest_slot'))
			throw new NullArgumentException("No destination placeholder/slot specified.");
		return RequestContext::value('dest_slot');
	}
}

?>