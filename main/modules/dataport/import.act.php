<?php
/**
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.6 2008/01/28 19:54:29 adamfranco Exp $
 */ 
require_once(MYDIR."/main/modules/ui1/add.act.php");


require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");

require_once(dirname(__FILE__)."/Rendering/DomImportSiteVisitor.class.php");


/**
 * This action will import a site into the slot-name given.
 * 
 * @since 1/22/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: import.act.php,v 1.6 2008/01/28 19:54:29 adamfranco Exp $
 */
class importAction
	extends addAction
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
		return $slotMgr->getSlotByShortname(RequestContext::value('site'));
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
		print "\n\t"._("Import comments?");
		print "\n\t\n</p>";
		
		$property = $step->addComponent('roles', new WCheckbox());
		$property->setValue(true);
		print "\n<p>[[roles]] ";
		print "\n\t"._("Import roles/permissions?");
		print "\n\t\n</p>";
		
		$step->setContent(ob_get_clean());
		
		// Site Admins.
		$this->addSiteAdminStep($wizard);
		
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
			
			// Decompress the archive into our temp-dir
			$archive = new Archive_Tar($archivePath);
			
			// Check for a containing directory and strip it if needed.
			$content = @$archive->listContent();
			$containerName = null;
			if ($content[0]['typeflag'] == 5) {
				$containerName = $content[0]['filename'].'/';
				for ($i = 1; $i < count($content); $i++) {
					// if one of the files isn't in the container, then we don't have a container of all
					if (strpos($content[$i]['filename'], $containerName) === false) {
						$containerName = null;
						break;
					}
				}
			}
			
			$decompressResult = @$archive->extractModify($decompressDir, $containerName);
			if (!$decompressResult)
				throw new Exception("Invalid Segue archive. '".$values['mode']['backup_file']['name']."' is not a valid GZIPed Tar archive.");
				
			
			
			// Do the import
			$director = $this->getSiteDirector();
			
			$doc = new Harmoni_DOMDocument;
			$doc->load($decompressDir."/site.xml");
			$mediaDir = $decompressDir;
			
			$importer = new DomImportSiteVisitor($doc, $mediaDir, $director);
			if ($values['mode']['roles'] == '1')
				$importer->enableRoleImport();
			
			if ($values['mode']['comments'] == '0')
				$importer->disableCommentImport();
			
			$importer->importAtSlot($values['mode']['slotname']);
			
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
			print "\n</div>";
			
			$wizard->backupFile->setValue(array('name' => null, 'size' => null, 'type' => null));
			
			return false;
		}
		
		return true;
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
		if ($this->_siteId) 
			return $harmoni->request->quickURL('ui1', "view", array(
				"node" => $this->_siteId));
		else
			return $harmoni->request->quickURL('slots', "browse");
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