<?php

/**
 * Set up the Slots system
 *
 * USAGE: Copy this file to slots.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: slots_default.conf.php,v 1.4 2008/04/01 16:31:14 adamfranco Exp $
 */
 
 	$idMgr = Services::getService("Id");
	
	// Add group ids which should be given a 'personal slot'
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=students,OU=Students_By_Year,OU=Groups,DC=middlebury,DC=edu');
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=All LS People,OU=LS Lists,OU=Groups,DC=middlebury,DC=edu');
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=MIIS Faculty,OU=Groups,DC=middlebury,DC=edu');
	PersonalSlot::$validGroups[] = $idMgr->getId('CN=MIIS Staff,OU=Groups,DC=middlebury,DC=edu');
	
	
	// Add group ids which correspond to faculty or other instructors
	SegueCourseSection::$instructorGroups[] = $idMgr->getId('CN=All Faculty,OU=General,OU=Groups,DC=middlebury,DC=edu');
// 	SegueCourseSection::$instructorGroups[] = $idMgr->getId('CN=All Staff,OU=General,OU=Groups,DC=middlebury,DC=edu');
	SegueCourseSection::$instructorGroups[] = $idMgr->getId('CN=All LS Faculty,OU=LS Lists,OU=Groups,DC=middlebury,DC=edu');
	SegueCourseSection::$instructorGroups[] = $idMgr->getId('CN=MIIS Faculty,OU=Groups,DC=middlebury,DC=edu');
	SegueCourseSection::$instructorGroups[] = $idMgr->getId('CN=MIIS Staff,OU=Groups,DC=middlebury,DC=edu');
	
	// Set a default Media quota
// 	SlotAbstract::setDefaultMediaQuota(ByteSize::fromString('10MB'));

	/*********************************************************
	 * Redirects for splitting sites based on location-category
	 *********************************************************/
// 	SiteDispatcher::setBaseUrlForLocationCategory('main', 'http://segue.example.edu/index.php');
// 	SiteDispatcher::setBaseUrlForLocationCategory('community', 'http://seguecommunity.example.edu/index.php');
	
	/*********************************************************
	 * Define a key for providing a listing of course-sites
	 * to other systems.
	 *
	 * This listing can be accessed at
	 * http://segue.example.com/index.php?module=slots&action=course_listing&key=xxxxxxxxx
	 * where xxxxxxxxx is the key defined below.
	 *
	 * Please define your own unique key.
	 *********************************************************/
// 	define('SEGUE_COURSE_SITE_LISTING_KEY', 'jwf0u34nfg08923ng-2983456ty');
// 	$GLOBALS['SEGUE_COURSE_SITE_LISTING_SEMESTERS'] = array('f', 's', 'w', 'l');
// 	
// 	/**
// 	 * Match a slot-name against a semester and year filter. If the slotname
// 	 * matches the semester and year filters, return true, false otherwise
// 	 * 
// 	 * @param string $slotname The slotname to match
// 	 * @param string $semesterFilter
// 	 * @param string $yearFilter A four-digit year.
// 	 * @return boolean
// 	 * @access public
// 	 * @since 8/12/08
// 	 */
// 	function match_segue_course_site($slotname, $semesterFilter, $yearFilter) {		
// 		return preg_match('/^[a-z]{2,4}[0-9]{3,4}[a-z]?-'.$semesterFilter.substr(strval($yearFilter), 2, 2).'$/', $slotname);
// 	}
// 	define('SEGUE_COURSE_SITE_LISTING_MATCH_CALLBACK', 'match_segue_course_site');
	