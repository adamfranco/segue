<?php
/**
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomExportSiteVisitor.class.php,v 1.10 2008/04/18 20:39:15 achapin Exp $
 */ 

require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");

/**
 * This vistor will return an XML version of a site.
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomExportSiteVisitor.class.php,v 1.10 2008/04/18 20:39:15 achapin Exp $
 */
class DomExportSiteVisitor
	implements SiteVisitor
{
	
	/**
	 * @var DOMDocument $doc;  
	 * @access public
	 * @since 1/17/08
	 */
	public $doc;
	
	/**
	 * @var boolean $rootAdded;  
	 * @access private
	 * @since 1/17/08
	 */
	private $rootAdded = false;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/17/08
	 */
	public function __construct ($filePath) {
		if (!is_dir($filePath))
			throw new Exception("'$filePath' does not exist for export.");
		if (!is_writable($filePath))
			throw new Exception("'$filePath' is not writable for export.");
		
		$this->filePath = $filePath;
		$this->doc = new Harmoni_DOMDocument;
		$this->doc->appendChild($this->doc->createElement('Segue2'));
		$this->doc->documentElement->setAttribute('export_date', DateAndTime::now()->asString());
		$this->doc->documentElement->setAttribute('segue_version', displayAction::getSegueVersion());
		$this->doc->documentElement->setAttribute('segue_export_version', '2.0');
		
		$this->agents = $this->doc->documentElement->appendChild($this->doc->createElement('agents'));
		$this->xpath = new DOMXPath($this->doc);
		$this->rootAdded = false;
	}
	
	/**
	 * Record an element as the docuement root if none exists
	 * 
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function record (DOMElement $element) {
		if (!$this->rootAdded) {
			$this->doc->documentElement->insertBefore($element, $this->agents);
			$this->rootAdded = true;
		}
	}
	
	/**
	 * Record an agent so that their information can be passed on to new systems.
	 * 
	 * @param object Id $agentId
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function recordAgent (Id $agentId) {
		$query = 'count(agent[@id = "'.$agentId->getIdString().'"])';
		if (!$this->xpath->evaluate($query, $this->agents)) {
			$element = $this->agents->appendChild($this->doc->createElement('agent'));
			$element->setAttribute('id', $agentId->getIdString());
			$agentMgr = Services::getService('Agent');
			$agent = $agentMgr->getAgentOrGroup($agentId);
			$element->appendChild($this->getCDATAElement('displayName', $agent->getDisplayName()));
			
			$propertiesIterator = $agent->getProperties();
			while ($propertiesIterator->hasNext()) {
				$properties = $propertiesIterator->next();
				$keys = $properties->getKeys();
				while ($keys->hasNext()) {
					$propElement = $element->appendChild($this->doc->createElement('property'));
					$key = $keys->next();
					$propElement->appendChild($this->getCDATAElement('key', $key));
					$propElement->appendChild($this->getCDATAElement('string', $properties->getProperty($key)));
				}
			}
		}
	}
	
	/**
	 * Record a file to our temporary directory.
	 * 
	 * @param object Asset $asset
	 * @param object FileRecord $fileRecord
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function recordFile (Asset $asset, FileRecord $fileRecord) {
		if (!file_exists($this->filePath."/media"))
			mkdir($this->filePath."/media");
			
		$assetDir = "media/".preg_replace('/[^a-z0-9_-]/i', '_', $asset->getId()->getIdString());
		if (!file_exists($this->filePath."/".$assetDir))
			mkdir($this->filePath."/".$assetDir);
		
		$recordIdString = preg_replace('/[^a-z0-9_-]/i', '_', $fileRecord->getId()->getIdString());
		$fileDir = $assetDir."/".$recordIdString;
		if (!file_exists($this->filePath."/".$fileDir))
			mkdir($this->filePath."/".$fileDir);
		
		$idMgr = Services::getService('Id');
		
		$parts = $fileRecord->getPartsByPartStructure($idMgr->getId("FILE_NAME"));
		$part = $parts->next();
		$fileName = preg_replace('/[^a-z0-9._-]/i', '_', $part->getValue());
		if (!strlen(trim($fileName, '._')))
			$fileName = $recordIdString();
		
		$parts = $fileRecord->getPartsByPartStructure($idMgr->getId("FILE_DATA"));
		$part = $parts->next();
		file_put_contents($this->filePath."/".$fileDir."/".$fileName, $part->getValue());
		
		return $fileDir."/".$fileName;
	}
	
	/**
	 * Answer an element with a single CDATA section
	 * 
	 * @param string $elementName
	 * @param string $data
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getCDATAElement ($elementName, $data) {
		$element = $this->doc->createElement($elementName);
		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * answer the display Name element
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getDisplayName (BlockSiteComponent $siteComponent) {
		$string = String::withValue($siteComponent->getDisplayName());
		$string->makeUtf8();
		
		return $this->getCDATAElement('displayName', $string->asString());
	}
	
	/**
	 * Add the description
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getDescription (BlockSiteComponent $siteComponent) {
		$string = String::withValue($siteComponent->getDescription());
		$string->makeUtf8();
		
		return $this->getCDATAElement('description', $string->asString());
	}
	
	/**
	 * Add common option attributes
	 * 
	 * @param SiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function addCommonOptions (SiteComponent $siteComponent, DOMElement $element) {
		$element->setAttribute('id', $siteComponent->getId());
		$element->setAttribute('showDisplayNames', $this->cascadingBooleanValue(
			$siteComponent->showDisplayNames()));
		$element->setAttribute('showHistory', $this->cascadingBooleanValue(
			$siteComponent->showHistorySetting()));
		$element->setAttribute('sortMethod', $siteComponent->sortMethodSetting());
		$element->setAttribute('commentsEnabled', $this->cascadingBooleanValue(
			$siteComponent->commentsEnabled()));
		$element->setAttribute('width', $siteComponent->getWidth());
	}
	
	/**
	 * Add the create and modify dates and agents
	 * 
	 * @param SiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/18/08
	 */
	protected function addCreateAndModify (SiteComponent $siteComponent, DOMElement $element) {
		$element->setAttribute('create_date', $siteComponent->getCreationDate()->asString());
		if (!is_null($siteComponent->getCreator())) {
			$element->setAttribute('create_agent', $siteComponent->getCreator()->getIdString());
			$this->recordAgent($siteComponent->getCreator());
		}
		$element->setAttribute('modify_date', $siteComponent->getModificationDate()->asString());
// 		if (!is_null($siteComponent->getModifier()))
// 			$element->setAttribute('modify_agent', $siteComponent->getCreator()->getIdString());
	}
	
	/**
	 * Add options for organizers
	 * 
	 * @param OrganizerSiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function addOrganizerOptions (OrganizerSiteComponent $siteComponent, DOMElement $element) {
		$element->setAttribute('rows', $siteComponent->getNumRows());
		$element->setAttribute('cols', $siteComponent->getNumColumns());
		$element->setAttribute('direction', $siteComponent->getDirection());
	}
	
	/**
	 * Add options for flow organizers
	 * 
	 * @param FlowOrganizerSiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function addFlowOrganizerOptions (FlowOrganizerSiteComponent $siteComponent, DOMElement $element) {
		$element->setAttribute('overflowStyle', $siteComponent->getOverflowStyle());
	}
	
	/**
	 * Answer one of the following strings, 'true', 'false', 'default'
	 * 
	 * @param mixed $value
	 * @return string
	 * @access protected
	 * @since 1/17/08
	 */
	protected function cascadingBooleanValue ($value) {
		if ($value === 'default')
			return 'default';
		if ($value === true)
			return 'true';
		if ($value === false)
			return 'false';
		
		throw new Exception ("Value, '$value', should be one of true, false, or 'default'.");
	}
	
	/**
	 * Add the child cells of an organizer
	 * 
	 * @param OrganizerSiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function addOrganizerChildren (OrganizerSiteComponent $siteComponent, DOMElement $element) {
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$cell = $element->appendChild($this->doc->createElement("cell"));
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				try {
					$cell->appendChild($child->acceptVisitor($this));
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
	 * @since 1/17/08
	 */
	protected function getComments (BlockSiteComponent $siteComponent) {
		$element = $this->doc->createElement('comments');
		if ($this->isAuthorizedToExport($siteComponent)) {
			$commentMgr = CommentManager::instance();
			$idMgr = Services::getService("Id");
			$comments = $commentMgr->getRootComments($idMgr->getId($siteComponent->getId()));
			while($comments->hasNext())
				$element->appendChild($this->getComment($comments->next()));
		}
		return $element;
	}
	
	/**
	 * Answer an element that represents a comment.
	 * 
	 * @param object CommentNode $comment
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getComment (CommentNode $comment) {
		$element = $this->doc->createElement('Comment');
		$element->setAttribute('id', $comment->getId());
		$element->appendChild($this->getType($comment->getAsset()->getAssetType()));
		$element->appendChild($this->getCDATAElement('subject', $comment->getSubject()));
		$this->addCommentCreateAndModify($comment, $element);
		
		$this->addPluginContent($comment, $element);

		$repliesElem = $element->appendChild($this->doc->createElement('replies'));
		$replies = $comment->getReplies();
		while($replies->hasNext())
			$repliesElem->appendChild($this->getComment($replies->next()));
		return $element;
	}

	/**
	 * Answer an element that represents the tags attached to a block.
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return DOMElement
	 * @access protected
	 * @since 4/17/08
	 */
	protected function getTags (BlockSiteComponent $siteComponent) {
		$element = $this->doc->createElement('tags');
		if ($this->isAuthorizedToExport($siteComponent)) {
			$tags = array();
			$tagManager = Services::getService("Tagging");
			$item = HarmoniNodeTaggedItem::forId($siteComponent->getId(), 'segue');
			$tagInfoIterator = $tagManager->getTagInfoForItem($item);
			while($tagInfoIterator->hasNext())
				$element->appendChild($this->getTagApplication($tagInfoIterator->next()));
		}
		return $element;
	}

	/**
	 * Answer an element that represents a single tag application for a block.
	 * 
	 * @param object TagInfo $tagInfo
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getTagApplication (TagInfo $tagInfo) {
		$element = $this->doc->createElement('tag', $tagInfo->tag->getValue());
		
		$element->setAttribute('agent_id', $tagInfo->agentId->getIdString());
		$this->recordAgent($tagInfo->agentId);
		$element->setAttribute('create_date', $tagInfo->timestamp->asString());

		return $element;
	}
	
	/**
	 * Add the create and modify dates and agents
	 * 
	 * @param CommentNode $comment
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function addCommentCreateAndModify (CommentNode $comment, DOMElement $element) {
		$element->setAttribute('create_date', $comment->getCreationDate()->asString());
		if (!is_null($comment->getAuthor())) {
			$element->setAttribute('create_agent', $comment->getAuthor()->getId()->getIdString());
			$this->recordAgent($comment->getAuthor()->getId());
		}
		$element->setAttribute('modify_date', $comment->getModificationDate()->asString());
// 		if (!is_null($comment->getModifier()))
// 			$element->setAttribute('modify_agent', $comment->getCreator()->getIdString());
	}
	
	/**
	 * Answer an element that represents an OKI type
	 * 
	 * @param object Type $type
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getType (Type $type) {
		$element = $this->doc->createElement('type');
		$element->appendChild($this->doc->createElement('domain', $type->getDomain()));
		$element->appendChild($this->doc->createElement('authority', $type->getAuthority()));
		$element->appendChild($this->doc->createElement('keyword', $type->getKeyword()));
		return $element;
	}
	
	/**
	 * Add the plugin content elements to an element
	 * 
	 * @param mixed SiteComponent or CommentNode $node
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function addPluginContent ($node, DOMElement $element) {
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($node->getAsset());
		if ($plugin->supportsVersioning()) {
			$element->appendChild($this->getCurrentPluginVersion($plugin));
			$element->appendChild($this->getPluginHistory($plugin));
		} else {
			$element->appendChild($this->getRawPluginContent($plugin));
		}
	}
	
	/**
	 * Answer an XML Element representation of the current plugin state
	 * 
	 * @param object SeguePluginsAPI $plugin
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getCurrentPluginVersion (SeguePluginsAPI $plugin) {
		$current = $this->doc->createElement('currentVersion');
		$pluginXml = $plugin->exportVersion();
		$current->appendChild($this->doc->importNode($pluginXml->documentElement, true));
		return $current;
	}
	
	/**
	 * Answer an XML representation of the plugin history
	 * 
	 * @param object SeguePluginsDriverAPI $plugin
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getPluginHistory (SeguePluginsDriverAPI $plugin) {
		$history = $this->doc->createElement('history');
		$versions = $plugin->getVersions();
		foreach ($versions as $version) {
			$entry = $history->appendChild($this->doc->createElement('entry'));
			$entry->setAttribute('number', $version->getNumber());
			$entry->setAttribute('time_stamp', $version->getTimestamp()->asString());
			$entry->setAttribute('agent_id', $version->getAgentId()->getIdString());
			$this->recordAgent($version->getAgentId());
			$entry->setAttribute('isCurrent', (($version->isCurrent())?'true':'false'));
			$comment = $entry->appendChild($this->doc->createElement('comment'));
			$comment->appendChild($this->doc->createCDATASection($version->getComment()));
			$entry->appendChild($this->doc->importNode($version->getVersionXml()->documentElement, true));
		}
		return $history;
	}
	
	/**
	 * For Plugins that do not support versioning. Answer the raw plugin content.
	 * 
	 * @param object SeguePluginsDriverAPI $plugin
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getRawPluginContent (SeguePluginsDriverAPI $plugin) {
		$current = $this->doc->createElement('currentContent');
		$content = $current->appendChild($this->doc->createElement('content'));
		$content->appendChild($this->doc->createCDATASection($plugin->getContent()));
		
		$string = String::withValue($plugin->getRawDescription());
		$string->makeUtf8();
				
		$desc = $current->appendChild($this->doc->createElement('rawDescription'));
		$desc->appendChild($this->doc->createCDATASection($string->asString()));
		return $current;
	}
	
	/**
	 * Answer an XML representation of the files attached to a site component.
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return DOMElement $element
	 * @access protected
	 * @since 1/18/08
	 */
	protected function getAttachedMedia (BlockSiteComponent $siteComponent) {
		$element = $this->doc->createElement('attachedMedia');
		
		$mediaAssetType = new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		$children = $siteComponent->getAsset()->getAssets();
		while ($children->hasNext()) {
			$child = $children->next();
			if ($mediaAssetType->isEqual($child->getAssetType())) {
				try {
					$element->appendChild($this->getMediaAsset($child));
				} catch (PermissionDeniedException $e) {
				}
			}
		}
		
		return $element;
	}
	
	/**
	 * Answer an XML representation of a media file.
	 * 
	 * @param object Asset $asset
	 * @return DOMElement
	 * @access protected
	 * @since 1/18/08
	 */
	protected function getMediaAsset (Asset $asset) {
		$element = $this->doc->createElement('mediaAsset');
		
		$element->setAttribute('id', $asset->getId()->getIdString());
		
		$element->setAttribute('create_date', $asset->getCreationDate()->asString());
		if (!is_null($asset->getCreator()))
			$element->setAttribute('create_agent', $asset->getCreator()->getIdString());
		$element->setAttribute('modify_date', $asset->getModificationDate()->asString());
		
		$element->appendChild($this->getCDATAElement('displayName', $asset->getDisplayName()));
		$element->appendChild($this->getCDATAElement('description', $asset->getDescription()));
		
		// File Records
		$idMgr = Services::getService("Id");
		$fileRecords = $asset->getRecordsByRecordStructure($idMgr->getId('FILE'));
		while ($fileRecords->hasNext()) {
			$fileRecord = $fileRecords->next();
			$fileElement = $element->appendChild($this->doc->createElement('file'));
			
			$fileElement->setAttribute('id', $fileRecord->getId()->getIdString());
			
			$parts = $fileRecord->getPartsByPartStructure($idMgr->getId("FILE_NAME"));
			$part = $parts->next();
			$fileElement->appendChild($this->getCDATAElement('name', $part->getValue()));
			
			$recordedPath = $this->recordFile($asset, $fileRecord);
			$fileElement->appendChild($this->getCDATAElement('path', $recordedPath));
		}
		
		// Dublin Core Record
		$records = $asset->getRecordsByRecordStructure($idMgr->getId('dc'));
		if ($records->hasNext()) {
			$record = $records->next();
			
			$dcElement = $element->appendChild($this->doc->createElement('dublinCore'));
			
			$dcElement->setAttribute('id', $record->getId()->getIdString());
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.title"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('title', $valueObj->asString()));
			}
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.description"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('description', $valueObj->asString()));
			}
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.creator"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('creator', $valueObj->asString()));
			}
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.source"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('source', $valueObj->asString()));
			}
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.publisher"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('publisher', $valueObj->asString()));
			}
			
			$parts = $record->getPartsByPartStructure($idMgr->getId("dc.date"));
			if ($parts->hasNext()) {
				$part = $parts->next();
				$valueObj = $part->getValue();
				$dcElement->appendChild($this->getCDATAElement('date', $valueObj->asString()));
			}
		}
		
		
		return $element;
	}
	
	/**
	 * Answer the roles set at the level
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return DOMElement
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getRoles (BlockSiteComponent $siteComponent) {		
		$element = $this->doc->createElement('roles');
		
		try {
			$roleMgr = SegueRoleManager::instance();
			$noAccess = $roleMgr->getRole('no_access');
			foreach ($this->getAgentsToCheck($siteComponent->getQualifierId()) as $agentId) {
				$role = $roleMgr->getAgentsExplicitRole($agentId, $siteComponent->getQualifierId());
				if ($role->isGreaterThan($noAccess)) {
					$entry = $element->appendChild($this->doc->createElement('entry'));
					$entry->setAttribute('role', $role->getIdString());
					$entry->setAttribute('agent_id', $agentId->getIdString());
					$this->recordAgent($agentId);
				}
			}
		} catch (PermissionDeniedException $e) {
		}
		return $element;
	}
	
	/**
	 * Answer a list of agents to check
	 *
	 * @param object Id $qualifierId
	 * @return array of Id objects
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getAgentsToCheck (Id $qualifierId) {
		try {
			if (!isset($this->agentsToCheck)) {
				$roleMgr = SegueRoleManager::instance();
				$this->agentsToCheck = $roleMgr->getAgentsWithRoleAtLeast($roleMgr->getRole('reader'), $qualifierId);
			}
			
			return $this->agentsToCheck;
		} catch (PermissionDeniedException $e) {
			return array();	
		}
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 * @since 1/18/08
	 */
	protected function isAuthorizedToExport (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		return $authZ->isUserAuthorizedBelow($idMgr->getId('edu.middlebury.authorization.view'), $siteComponent->getQualifierId());
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 * @since 1/18/08
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
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('Block');
		$this->record($element);
		
		$element->appendChild($this->getType($siteComponent->getContentType()));
		$element->appendChild($this->getDisplayName($siteComponent));
		$element->appendChild($this->getDescription($siteComponent));
		$this->addCommonOptions($siteComponent, $element);
		$this->addCreateAndModify($siteComponent, $element);
		
		$element->appendChild($this->getRoles($siteComponent));
		
		// Plugin Content
		$this->addPluginContent($siteComponent, $element);
		
		// Comments
		$element->appendChild($this->getComments($siteComponent));
		
		// Files
		$element->appendChild($this->getAttachedMedia($siteComponent));
		
		//tags
		$element->appendChild($this->getTags($siteComponent));
		
		$element->setAttribute('blockDisplayType', $siteComponent->getDisplayType());
		$element->setAttribute('headingDisplayType', $siteComponent->getHeadingDisplayType());
		
		return $element;
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('NavBlock');
		$this->record($element);
		
		$element->appendChild($this->getDisplayName($siteComponent));
		$element->appendChild($this->getDescription($siteComponent));
		$this->addCommonOptions($siteComponent, $element);
		$this->addCreateAndModify($siteComponent, $element);
		
		$element->appendChild($this->getRoles($siteComponent));
		
		$element->appendChild($siteComponent->getOrganizer()->acceptVisitor($this));
		
		// Nested Menus
		$nestedMenu = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$element->appendChild($nestedMenu->acceptVisitor($this));
		
		return $element;
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('SiteNavBlock');
		$this->record($element);
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($siteComponent->getId());
		$element->setAttribute('slot_name', $slot->getShortName());
		
		$element->appendChild($this->getDisplayName($siteComponent));
		$element->appendChild($this->getDescription($siteComponent));
		$this->addCommonOptions($siteComponent, $element);
		$this->addCreateAndModify($siteComponent, $element);
		
		$element->appendChild($this->getRoles($siteComponent));
		
		// Add the theme info
		$element->appendChild($this->getTheme($siteComponent));
		
		$element->appendChild($siteComponent->getOrganizer()->acceptVisitor($this));
		
		return $element;
	}
	
	/**
	 * Answer the theme element
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return DOMElement
	 * @access protected
	 * @since 6/6/08
	 */
	protected function getTheme ( SiteNavBlockSiteComponent $siteComponent ) {
		$theme = $siteComponent->getTheme();
		
		$element = $this->doc->createElement('theme');
		$element->setAttribute("id", $theme->getIdString());
		
		if ($theme->supportsOptions()) {
			$optSession = $theme->getOptionsSession();
			foreach ($optSession->getOptions() as $option) {
				$value = $option->getValue();
				if ($value != $option->getDefaultValue()) {
					$optionElement = $this->doc->createElement('theme_option_choice', $value);
					$optionElement->setAttribute("id", $option->getIdString());
					$element->appendChild($optionElement);
				}
			}
		}
		
		return $element;
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('FixedOrganizer');
		$this->record($element);
		
		$this->addCommonOptions($siteComponent, $element);
		$this->addOrganizerOptions($siteComponent, $element);
		$this->addOrganizerChildren($siteComponent, $element);
		
		return $element;
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('NavOrganizer');
		$this->record($element);
		
		$this->addCommonOptions($siteComponent, $element);
		$this->addOrganizerOptions($siteComponent, $element);
		$this->addOrganizerChildren($siteComponent, $element);
		
		return $element;
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('FlowOrganizer');
		$this->record($element);
		
		$this->addCommonOptions($siteComponent, $element);
		$this->addOrganizerOptions($siteComponent, $element);
		$this->addFlowOrganizerOptions($siteComponent, $element);
		$this->addOrganizerChildren($siteComponent, $element);
		
		return $element;
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->doc->createElement('MenuOrganizer');
		$this->record($element);
		
		$this->addCommonOptions($siteComponent, $element);
		$element->setAttribute('direction', $siteComponent->getDirection());
		$this->addFlowOrganizerOptions($siteComponent, $element);
		$element->setAttribute('target_id', $siteComponent->getTargetId());
		$this->addOrganizerChildren($siteComponent, $element);
		
		$element->setAttribute('menuDisplayType', $siteComponent->getDisplayType());
		
		return $element;
	}
	
}

?>