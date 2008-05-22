<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllVisiblePortalFolder.class.php,v 1.2 2008/04/02 14:35:04 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * The PersonalPortalFolder contains all personal sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllVisiblePortalFolder.class.php,v 1.2 2008/04/02 14:35:04 adamfranco Exp $
 */
class AllVisiblePortalFolder
	implements PortalFolder 
{
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("All Visible");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return _("All sites that you can view.");
	}
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return "all_visible";
	}
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		$slotMgr = SlotManager::instance();
		
		// process of elimination.
		$slots = array();
		foreach ($this->getSiteIds() as $siteId) {
			try {
				$slots[] = $slotMgr->getSlotBySiteId($siteId);
			} catch (UnknownIdException $e) {
				$slot = new CustomSlot(null);
				$slot->populateSiteId($siteId);
				$slots[] = $slot;
			}
		}
		return $slots;
	}
	
	/**
	 * Answer a string of controls html to go along with this folder. In many cases
	 * it will be empty, but some implementations may need controls for adding new slots.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getControlsHtml () {
		return '';
	}
	
	/**
	 * Answer true if the slots returned by getSlots() have already been filtered
	 * by authorization and authorization checks should only be done when printing.
	 * This method enables speed increases on long pre-sorted lists.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function slotsAreFilteredByAuthorization () {
		return true;
	}
	
	/**
	 * Answer true if the edit controls should be displayed for the sites listed.
	 * If true, this can lead to slowdowns as authorizations are checked on large
	 * lists of large sites.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function showEditControls () {
		return false;
	}
	
	/**
	 * Answer an array of site-ids for sites visible
	 * 
	 * @return array of strings
	 * @access private
	 * @since 4/1/08
	 */
	private function getSiteIds () {
		if (!isset($_SESSION['PORTAL_ALL_SITES_CACHE']) 
			|| !isset($_SESSION['PORTAL_ALL_SITES_CACHE_USER'])
			|| $_SESSION['PORTAL_ALL_SITES_CACHE_USER'] != $this->getUserIdString())
		{
			$_SESSION['PORTAL_ALL_SITES_CACHE'] = array();
			$_SESSION['PORTAL_ALL_SITES_CACHE_USER'] = $this->getUserIdString();
		}
		
		if (!isset($_SESSION['PORTAL_ALL_SITES_CACHE'][$this->getIdString()])) {
			$repositoryManager = Services::getService("Repository");
			$idManager = Services::getService("Id");
			$authZ = Services::getService("AuthZ");
			
			$repository = $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			$assets = $repository->getAssetsByType(new HarmoniType('segue', 
								'edu.middlebury', 
								'SiteNavBlock', 
								'An Asset of this type is the root node of a Segue site.'));
			
			$siteIds = array();
			while ($assets->hasNext()) {
				$asset = $assets->next();
				if ($this->includeSite($asset->getId()))
					$siteIds[] = $asset->getId()->getIdString();
			}
			
			sort($siteIds);
			$_SESSION['PORTAL_ALL_SITES_CACHE'][$this->getIdString()] = $siteIds;
		}
		return $_SESSION['PORTAL_ALL_SITES_CACHE'][$this->getIdString()];
	}
	
	/**
	 * Answer the current user's id string.
	 * 
	 * @return string
	 * @access private
	 * @since 4/2/08
	 */
	private function getUserIdString () {
		$authN = Services::getService('AuthN');
		return $authN->getFirstUserId()->getIdString();
	}
	
	/**
	 * Answer true if this site should be included
	 * 
	 * @param object Id $id
	 * @return boolean
	 * @access protected
	 * @since 4/1/08
	 */
	protected function includeSite (Id $id) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		// Since View AZs now cascade up, we don't need to check isAuthorizedBelow()
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), $id))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>