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