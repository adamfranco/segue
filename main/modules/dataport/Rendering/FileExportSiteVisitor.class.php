<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */ 

require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");
require_once(dirname(__FILE__).'/NumComponentsVisitor.class.php');

/**
 * This vistor will export the files in a site.
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */
class FileExportSiteVisitor
	implements SiteVisitor
{
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 */
	public function __construct ($filePath) {
		if (!is_dir($filePath))
			throw new Exception("'$filePath' does not exist for export.");
		if (!is_writable($filePath))
			throw new Exception("'$filePath' is not writable for export.");
		
		$this->filePath = $filePath;
	}
	
	/**
	 * Record a file to our temporary directory.
	 * 
	 * @param object Asset $asset
	 * @param object FileRecord $fileRecord
	 * @return void
	 * @access protected
	 */
	protected function recordFile (Asset $asset, FileRecord $fileRecord) {
		$idMgr = Services::getService('Id');
		
		$parts = $fileRecord->getPartsByPartStructure($idMgr->getId("FILE_NAME"));
		$part = $parts->next();
		$fileName = preg_replace('/[^a-z0-9._-]/i', '_', $part->getValue());
		if (!strlen(trim($fileName, '._')))
			$fileName = $recordIdString;
		
		$fileParts = pathinfo($fileName);
		$base = $fileParts['basename'];
		$extension = $fileParts['extension'];
		
		$dataParts = $fileRecord->getPartsByPartStructure($idMgr->getId("FILE_DATA"));
		$dataPart = $dataParts->next();
		
		$i = 1;
		while (file_exists($this->filePath.'/'.$fileName)) {
			$fileName = $base.'-'.$i.$extension;
			$i++;
		}
		
		file_put_contents($this->filePath."/".$fileName, $dataPart->getValue());
	}
	
	/**
	 * Add the child cells of an organizer
	 * 
	 * @param OrganizerSiteComponent $siteComponent
	 * @return void
	 * @access protected
	 */
	protected function addOrganizerChildren (OrganizerSiteComponent $siteComponent) {
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				try {
					$child->acceptVisitor($this);
				} catch (PermissionDeniedException $e) {
				}
			}
		}
	}
	
	/**
	 * Answer an element that represents the comments attached to a block.
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return DOMElement
	 * @access protected
	 */
	protected function addComments (BlockSiteComponent $siteComponent) {
		if ($this->isAuthorizedToExportComments($siteComponent)) {
			$commentMgr = CommentManager::instance();
			$idMgr = Services::getService("Id");
			$comments = $commentMgr->getRootComments($idMgr->getId($siteComponent->getId()));
			while($comments->hasNext())
				$this->addCommentAttachedMedia($comments->next());
		}
	}
	
	/**
	 * Answer the files attached to a site component.
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @access protected
	 */
	protected function addAttachedMedia (BlockSiteComponent $siteComponent) {		
		$mediaAssetType = new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		$children = $siteComponent->getAsset()->getAssets();
		while ($children->hasNext()) {
			$child = $children->next();
			if ($mediaAssetType->isEqual($child->getAssetType())) {
				try {
					$this->addMediaAsset($child);
				} catch (PermissionDeniedException $e) {
				} catch (OperationFailedException $e) {
				}
			}
		}
	}
	
	/**
	 * Answer the files attached to a site component.
	 * 
	 * @param object CommentNode $comment
	 * @access protected
	 */
	protected function addCommentAttachedMedia (CommentNode $comment) {		
		$mediaAssetType = new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		$children = $comment->getAsset()->getAssets();
		while ($children->hasNext()) {
			$child = $children->next();
			if ($mediaAssetType->isEqual($child->getAssetType())) {
				try {
					$this->addMediaAsset($child);
				} catch (PermissionDeniedException $e) {
				} catch (OperationFailedException $e) {
				}
			}
		}
	}
	
	/**
	 * Answer a media file.
	 * 
	 * @param object Asset $asset
	 * @return DOMElement
	 * @access protected
	 */
	protected function addMediaAsset (Asset $asset) {
		// File Records
		$idMgr = Services::getService("Id");
		$fileRecords = $asset->getRecordsByRecordStructure($idMgr->getId('FILE'));
		if (!$fileRecords->hasNext()) {
			throw new OperationFailedException("No file records found. Incomplete media asset.");
		}
		while ($fileRecords->hasNext()) {
			$fileRecord = $fileRecords->next();
			$recordedPath = $this->recordFile($asset, $fileRecord);
		}
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 */
	protected function isAuthorizedToExport (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		// Since view AZs cascade up, just check at the node.
		return $authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $siteComponent->getQualifierId());
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 */
	protected function isAuthorizedToExportComments (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		return $authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view_comments'), $siteComponent->getQualifierId());
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		// Comments
		$this->addComments($siteComponent);
		
		// Files
		$this->addAttachedMedia($siteComponent);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		return $this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
		
		// Nested Menus
		$nestedMenu = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$nestedMenu->acceptVisitor($this);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$this->addOrganizerChildren($siteComponent);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$this->addOrganizerChildren($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$this->addOrganizerChildren($siteComponent);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$this->addOrganizerChildren($siteComponent);
	}
}

?>