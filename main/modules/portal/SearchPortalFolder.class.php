<?php
/**
 * @since 8/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * Search Folders search for sites and retain a list of results
 * 
 * @since 8/11/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class SearchPortalFolder
	implements PortalFolder
{
	
	/**
	 * Constructor
	 * 
	 * @param string $query
	 * @return void
	 * @access public
	 * @since 8/11/08
	 */
	public function __construct ($query) {
		ArgumentValidator::validate($query, NonzeroLengthStringValidatorRule::getRule());
		if (strip_tags($query) != $query)
			throw new InvalidArgumentException("Invalid query '".htmlspecialchars($query)."'.");
		
		$this->query = strip_tags($query);
		
		$this->queryParts = explode(' ', 
			mb_convert_encoding($this->query, 'UTF-8', mb_detect_encoding($this->query, "ASCII,UTF-8,ISO-8859-1,JIS,EUC-JP,SJIS")));
		
		$this->id = urlencode($this->query);
		
		$authN = Services::getService('AuthN');
		$this->agentId = $authN->getFirstUserId();
	}
	
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return $this->query;
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return "<button onclick='PortalSearch.clear(\"".urlencode($this->getIdString())."\", this.parentNode.parentNode); return false;'>X</button>";
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
		return 'search-'.$this->id;
	}
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		if (!isset($this->siteIds))
			$this->search();
		
		$slotMgr = SlotManager::instance();
		
		// process of elimination.
		$slots = array();
		foreach ($this->siteIds as $siteId) {
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
		false;
	}
	
	/**
	 * Answer the AgentId used for this search.
	 * 
	 * @return object Id
	 * @access public
	 * @since 8/11/08
	 */
	public function getAgentId () {
		return $this->agentId;
	}
	
	/**
	 * Perform the search
	 * 
	 * @return void
	 * @access public
	 * @since 8/11/08
	 */
	public function search () {
		$repositoryManager = Services::getService("Repository");
		$idManager = Services::getService("Id");
		$authZ = Services::getService("AuthZ");
		
		$repository = $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		$assets = $repository->getAssetsByType(new HarmoniType('segue', 
							'edu.middlebury', 
							'SiteNavBlock', 
							'An Asset of this type is the root node of a Segue site.'));
		
		$this->siteIds = array();
		while ($assets->hasNext()) {
			$asset = $assets->next();
			
			if ($this->includeSite($asset))
				$this->siteIds[] = $asset->getId()->getIdString();
		}
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
	 * Match a string to our query.
	 * 
	 * @param string $input
	 * @return boolean
	 * @access protected
	 * @since 8/11/08
	 */
	protected function match ($input) {
		$encoding = mb_detect_encoding($input, "ASCII,UTF-8,ISO-8859-1,JIS,EUC-JP,SJIS");
		$input = mb_convert_encoding($input, 'UTF-8', $encoding);
		foreach ($this->queryParts as $part) {
			// Return false if we are missing one of the parts.
			if (mb_stripos($input, $part, 0, 'UTF-8') === false)
				return false;
		}
		
		return true;
	}
	
	/**
	 * Answer true if this site should be included
	 * 
	 * @param object Asset $asset
	 * @return boolean
	 * @access protected
	 * @since 4/1/08
	 */
	protected function includeSite (Asset $asset) {
		$matches = false;
		
		if ($this->match($asset->getDisplayName()))
			$matches = true;
		else if ($this->match($asset->getDescription()))
			$matches = true;
			
		if (!$matches) {
			$slotMgr = SlotManager::instance();
			try {
				$slot = $slotMgr->getSlotBySiteId($asset->getId());
			} catch (UnknownIdException $e) {
			}
			
			if (isset($slot) && $this->match($slot->getShortname()))
				$matches = true;
		}
		
		if (!$matches)
			return false;
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		// Since View AZs now cascade up, we don't need to check isAuthorizedBelow()
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

?>