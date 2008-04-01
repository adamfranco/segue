<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllVisiblePortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
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
 * @version $Id: AllVisiblePortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
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
	 * Answer an array of site-ids for sites visible
	 * 
	 * @return array of strings
	 * @access private
	 * @since 4/1/08
	 */
	private function getSiteIds () {
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
		return $siteIds;
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
		
		if ($authZ->isUserAuthorizedBelow(
			$idManager->getId("edu.middlebury.authorization.view"), $id))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>