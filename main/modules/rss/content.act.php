<?php
/**
 * @since 3/10/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: content.act.php,v 1.1 2008/03/11 17:49:17 achapin Exp $
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/RSSAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * generate RSS feed of content blocks
 * 
 * @since 3/10/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: content.act.php,v 1.1 2008/03/11 17:49:17 achapin Exp $
 */
class contentAction
	extends RSSAction
	implements SiteVisitor
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/10/08
	 */
	function isExecutionAuthorized () {
		$siteComponent = $this->getSiteComponent();
		
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		return $azMgr->isUserAuthorizedBelow(
			$idMgr->getId('edu.middlebury.authorization.view'),
			$siteComponent->getQualifierId());
	}
	
	/**
	 * builds an RSS feed
	 * 
	 * @return void
	 * @access public
	 * @since 3/10/08
	 */
	public function buildFeed () {
		$harmoni = Harmoni::instance();
		
		$siteComponent = $this->getSiteComponent();
		
		// set feed channel title and url
		$this->setTitle($siteComponent->getDisplayName()." - "._("Content"));
		$this->setLink($harmoni->request->quickURL("ui1","view",array("node" => $siteComponent->getId())));
		
		if (method_exists($siteComponent, 'getDescription'))
			$this->setDescription(strip_tags($siteComponent->getDescription()));
			
		// add items to the feed
		$siteComponent->acceptVisitor($this);
		
	}

	/**
	 * answer the site component specified in url
	 * 
	 * @return object SiteComponent
	 * @access protected
	 * @since 3/10/08
	 */
	protected function getSiteComponent () {
		
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));			
		
		if (!$nodeId = $this->getNodeId())
			throwError(new Error('No site node specified.', 'SiteDisplay'));

		return $director->getSiteComponentById($nodeId);
	}	

	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access protected
	 * @since 7/30/07
	 */
	protected function getNodeId () {
		if (RequestContext::value("site")) {
			$slotManager = SlotManager::instance();
			$slot = $slotManager->getSlotByShortname(RequestContext::value("site"));
			if ($slot->siteExists())
				$nodeId = $slot->getSiteId()->getIdString();
			else
				throw new UnknownIdException("A Site has not been created for the slotname '".$slot->getShortname()."'.");
		} else if (RequestContext::value("node")) {
			$nodeId = RequestContext::value("node");
		}
		
		if (!isset($nodeId) || !strlen($nodeId))
			throw new NullArgumentException('No site node specified.');
		
		return $nodeId;
	}
	
	/*********************************************************
	 * Vistor methods
	 *********************************************************/

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {		
		$harmoni = Harmoni::instance();
		
		// check to see if user is authorized to view block
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($siteComponent->getId())))
		{
			return;
		}

	
		$item = $this->addItem(new RSSItem);
		$item->setTitle($siteComponent->getDisplayName());
		$item->setLink($harmoni->request->quickURL("ui1","view",array("node" => $siteComponent->getId())), true);
		$item->setPubDate($siteComponent->getModificationDate());
		
		$agentMgr = Services::getService("Agent");
		$agent = $agentMgr->getAgent($siteComponent->getCreator());
		$item->setAuthor($agent->getDisplayName());
				
		$item->setCommentsLink($harmoni->request->quickURL("ui1","view",array("node" => $siteComponent->getId())));		
				
		//@todo get full content from plugin
		$item->setDescription($siteComponent->getDescription());

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
		$this->visitBlock($siteComponent);
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
		$organizer = $siteComponent->getOrganizer();
		$organizer->acceptVisitor($this);
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
		$this->visitNavBlock($siteComponent);
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
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		foreach ($siteComponent->getSortedSubcomponents() as $child) {
			$child->acceptVisitor($this);
		}
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
		$this->visitFlowOrganizer($siteComponent);
	}	
	
}

?>