<?php
/**
 * @since 3/14/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: map.act.php,v 1.1 2008/03/14 21:55:15 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteMapSiteVisitor.class.php");


/**
 * action for displaying site maps
 * 
 * @since 3/14/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: map.act.php,v 1.1 2008/03/14 21:55:15 achapin Exp $
 */
class mapAction {
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		return $azMgr->isUserAuthorizedBelow(
			$idMgr->getId('edu.middlebury.authorization.view'),
			$idMgr->getId($this->getNodeId()));
	}
	
	/**
	 * Answer a message in the case of no authorization
	 * 
	 * @return string
	 * @access public
	 * @since 3/14/08
	 */
	public function getUnauthorizedMessage () {
		$message = _("You are not authorized to view the requested node.");
		$message .= "\n<br/>";
		$authNMgr = Services::getService("AuthN");
		if (!$authNMgr->isUserAuthenticatedWithAnyType())
			$message .= _("Please log in or use your browser's 'Back' Button.");
		else
			$message .= _("Please use your browser's 'Back' Button.");
		
		return $message;
	}
	
		/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 3/14/08
	 */
	public final function execute () {
		if (!$this->isAuthorizedToExecute()) {
			header('HTTP/1.0 401 Unauthorized');
			$this->setTitle(_("Unauthorized"));
			$this->setDescription(_("You are not authorized to view this feed."));
			$this->write();
			exit;
		}
		
		$this->buildMap();
		$this->write();
		exit;
	}

	
	/**
	 * builds a site map 
	 * 
	 * @return void
	 * @access public
	 * @since 3/14/08
	 */
	public function buildMap () {
		$harmoni = Harmoni::instance();
		
		$siteComponent = $this->getSiteComponent();
		
		// set feed channel title and url
// 		$this->setTitle($siteComponent->getDisplayName()." - "._("Content"));
// 		$this->setLink($harmoni->request->quickURL("ui1","view",array("node" => $siteComponent->getId())));
		
// 		if (method_exists($siteComponent, 'getDescription'))
// 			$this->setDescription(strip_tags($siteComponent->getDescription()));
			
		// add items to the feed
		$siteComponent->acceptVisitor();
		
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
		
	
}

?>