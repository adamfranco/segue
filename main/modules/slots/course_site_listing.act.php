<?php
/**
 * @since 8/11/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * Answer a listing of course websites for the semester selected.
 * 
 * @since 8/11/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class course_site_listingAction
	extends XmlAction
{
		
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 08/11/08
	 */
	public function isAuthorizedToExecute () {
		if (!defined('SEGUE_COURSE_SITE_LISTING_KEY'))
			throw new ConfigurationErrorException("SEGUE_COURSE_SITE_LISTING_KEY must be defined to enable couse-listing.");
			
		if (RequestContext::value('key') == SEGUE_COURSE_SITE_LISTING_KEY)
			return true;
		else
			return false;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 08/11/08
	 */
	public function execute () {
		$this->start();
		try {
		
			if (!$this->isAuthorizedToExecute())
				throw new PermissionDeniedException("You are not authorized to load course listing.");
			
			$semester = RequestContext::value('semester');
			$year = strval(RequestContext::value('year'));
			$courseMgr = SegueCourseManager::instance();
			
			if (!isset($GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS']) || !is_array($GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS']) || !count($GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS']))
				throw new ConfigurationErrorException("\$GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS'] must be an array to enable couse-listing.");	
			$semesters = $GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS'];
			
			if (!in_array($semester, $semesters))
				throw new InvalidArgumentException("'semester' must be one of ".implode(", ", $semesters).".");
			
			if (!preg_match('/^(19|20)[0-9]{2,}$/', $year))
				throw new InvalidArgumentException("Year '$year' must be four digits.");
			
			if (!defined('SEGUE_COURSE_SITE_LISTING_MATCH_CALLBACK'))
				throw new ConfigurationErrorException("SEGUE_COURSE_SITE_LISTING_MATCH_CALLBACK must be defined to enable couse-listing.");
			
			$func = SEGUE_COURSE_SITE_LISTING_MATCH_CALLBACK;
			
			$slotMgr = SlotManager::instance();
			$agentMgr = Services::getService('Agent');
			$slots = $slotMgr->getAllSlots();
			while($slots->hasNext()) {
				$slot = $slots->next();
				if ($slot->siteExists() && $func($slot->getShortname(), $semester, $year)) {
					print "\n\t<site id=\"".$slot->getSiteId()->getIdString()."\">";
					print "\n\t\t<shortname>".$slot->getShortname()."</shortname>";
					$siteAsset = $slot->getSiteAsset();
					print "\n\t\t<displayName>".strip_tags($siteAsset->getDisplayName())."</displayName>";
					print "\n\t\t<description>".strip_tags($siteAsset->getDescription())."</description>";
					print "\n\t\t<url>".SiteDispatcher::getSitesUrlForSiteId(
						$slot->getSiteId()->getIdString())."</url>";
					foreach ($slot->getOwners() as $ownerId) {
						$owner = $agentMgr->getAgent($ownerId);
						print "\n\t\t<owner id=\"".$ownerId->getIdString()."\">";
						print "\n\t\t<displayName>".$owner->getDisplayName()."</displayName>";
						
						$propertiesIterator = $owner->getProperties();
						while ($propertiesIterator->hasNext()) {
							$properties = $propertiesIterator->next();
							$keys = $properties->getKeys();
							while ($keys->hasNext()) {
								$key = $keys->next();
								print "\n\t\t<property key=\"".$key."\">";
								print "<![CDATA[".$properties->getProperty($key)."]]>";
								print "</property>";
							}
						}
						
						print "\n\t\t</owner>";
					}
					print "\n\t</site>";
				}
			}
		
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
		$this->end();
	}
}

?>