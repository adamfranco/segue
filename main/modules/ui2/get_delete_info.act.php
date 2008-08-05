<?php
/**
 * @since 8/5/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/XmlAction.class.php');
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/media/MediaAsset.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * Return an XML document with information about the contents of the element to be
 * deleted.
 * 
 * @since 8/5/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class get_delete_infoAction
	extends XmlAction
	implements SiteVisitor
{
	/**
	 * @var int $sections;  
	 * @access private
	 * @since 8/5/08
	 */
	private $sections = 0;
	
	/**
	 * @var int $pages;  
	 * @access private
	 * @since 8/5/08
	 */
	private $pages = 0;
	
	/**
	 * @var int $blocks;  
	 * @access private
	 * @since 8/5/08
	 */
	private $blocks = 0;
	
	/**
	 * @var int $posts;  
	 * @access private
	 * @since 8/5/08
	 */
	private $posts = 0;
	
	/**
	 * @var int $media;  
	 * @access private
	 * @since 8/5/08
	 */
	private $media = 0;
	
	/**
	 * @var boolean $firstSeen; false 
	 * @access private
	 * @since 8/5/08
	 */
	private $firstSeen = false;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 8/5/08
	 */
	public function __construct () {
		$this->mediaFileType = MediaAsset::getMediaFileType();
	}
		
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/5/08
	 */
	public function isAuthorizedToExecute () {
		$component = $this->getSiteComponent();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		return $authZ->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.delete'), 
			$component->getQualifierId());
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 8/5/08
	 */
	public function execute () {
		try {
			if (!$this->isAuthorizedToExecute())
				throw new PermissionDeniedException(_("Your are not authorized to delete here."));
			
			// Traverse the subtree
			$this->getSiteComponent()->acceptVisitor($this);
			
			// print out our info
			$this->start();
			print "\n\t<siteComponent";
			
			if ($this->sections)
				print " sections='".$this->sections."'";
				
			if ($this->pages)
				print " pages='".$this->pages."'";
				
			if ($this->blocks)
				print " blocks='".$this->blocks."'";
				
			if ($this->posts)
				print " posts='".$this->posts."'";
				
			if ($this->media)
				print " media='".$this->media."'";
			
			print "/>";
			$this->end();
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e);
			$this->error($e->getMessage(), get_class($e));
		}
	}
	
	/**
	 * Answer the target component.
	 * 
	 * @return object SiteComponent
	 * @access protected
	 * @since 8/5/08
	 */
	protected function getSiteComponent () {
		if (!isset($this->destComponent)) {
			$director = SiteDispatcher::getSiteDirector();
			$this->destComponent = $director->getSiteComponentById(RequestContext::value('node'));
		}
		
		return $this->destComponent;
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		if (!$this->firstSeen) {
			$this->firstSeen = true;
		} else {
			$this->blocks++;
		}
		
		// Discussions
		$cm = CommentManager::instance();
		$this->posts += $cm->getNumComments($siteComponent->getAsset());
		
		// Media
		$mediaAssets = $this->getAllMediaAssets($siteComponent->getAsset());
		$this->media += $mediaAssets->count();
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if (!$this->firstSeen) {
			$this->firstSeen = true;
		} else {
			if ($siteComponent->isSection())
				$this->sections++;
			else
				$this->pages++;
		}
		
		if ($siteComponent->getNestedMenuOrganizer())
			$siteComponent->getNestedMenuOrganizer()->acceptVisitor($this);
		else
			$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		if (!$this->firstSeen) {
			$this->firstSeen = true;
		}
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		if (!$this->firstSeen) {
			$this->firstSeen = true;
		}
		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$child->acceptVisitor($this);
			}
		}
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		$this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		if (!$this->firstSeen) {
			$this->firstSeen = true;
		}
		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$child->acceptVisitor($this);
			}
		}
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/5/08
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		$this->visitFlowOrganizer($siteComponent);
	}
	
	/**
	 * Answer all media assets below the specified asset
	 * 
	 * @param object Asset $asset
	 * @param optional object Id $excludeId
	 * @return object Iterator
	 * @access protected
	 * @since 2/26/07
	 */
	protected function getAllMediaAssets ( Asset $asset, $excludeId = null ) {
		if ($excludeId && $excludeId->isEqual($asset->getId())) {
			return false;
		}
		
		if ($this->mediaFileType->isEqual($asset->getAssetType())) {
			$tmp = array();
			$tmp[] = $asset;
			$iterator = new HarmoniIterator($tmp);
			return $iterator;
		} else {
			$iterator = new MultiIteratorIterator();
			$children = $asset->getAssets();
			while ($children->hasNext()) {
				$result = $this->getAllMediaAssets($children->next(), $excludeId);
				if ($result) {
					$iterator->addIterator($result);
				}
			}
			
			return $iterator;
		}
	}
}

?>