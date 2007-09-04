<?php
/**
 * @since 8/30/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: content_rss.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */ 

require_once(POLYPHONY_DIR."/main/library/AbstractActions/RSSAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ContentRssSiteVisitor.class.php");

/**
 * <##>
 * 
 * @since 8/30/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: content_rss.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */
class content_rssAction
	extends RSSAction
{
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/4/06
	 */
	function isExecutionAuthorized () {
		return true;
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getRelm () {
		return "segue"; // Override for custom relm.
	}
	
	/**
	 * build the feed.  This is the main controller for this action
	 * 
	 * @return vold
	 * @access public
	 * @since 8/30/07
	 */
	public function buildFeed () {
		$harmoni = Harmoni::instance();


		/*********************************************************
		 * Asset version
		 *********************************************************/
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$this->_director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));			
		
		//this is the flow organizer
		$nodeId = $this->getNodeId();
		
		/*********************************************************
		 * Additional setup
		 *********************************************************/
		$siteComponent = $this->_director->getSiteComponentById($nodeId);
		$rootSiteComponent = $this->_director->getRootSiteComponent($nodeId);
		
		try {
			$this->setTitle($siteComponent->getDisplayName().': '.$siteComponent->getDisplayName());
			$this->setDescription($siteComponent->getDescription());
		} catch (Exception $e) {
			$this->setTitle($siteComponent->getDisplayName().': '.$siteComponent->getId());
			$this->setDescription('');
		}
		
		$this->setLink($harmoni->request->quickURL('ui1', 'view', array('node' => $nodeId)));
		
		$visitor = new ContentRssSiteVisitor($this);
		
		$siteComponent->acceptVisitor($visitor);
	}
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getNodeId () {
		if (RequestContext::value("site")) {
			$slotManager = SlotManager::instance();
			$slot = $slotManager->getSlotByShortname(RequestContext::value("site"));
			$nodeId = $slot->getSiteId()->getIdString();
		} else if (RequestContext::value("node")) {
			$nodeId = RequestContext::value("node");
		}
		
		if (!$nodeId)
			throw new Exception('No site node specified.');
		
		return $nodeId;
	}
	
}

?>