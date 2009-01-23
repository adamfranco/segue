<?php
/**
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

 require_once(MYDIR."/main/modules/participation/Participation_ModAction.abstract.php");
 
/**
 * get info about last edited modification action
 * 
 * @since 1/23/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Participation_LastEditAction
	extends Participation_ModAction
{
		
	/**
	 * get id prefix
	 * 
	 * @return string
	 * @access protected
	 * @since 1/23/09
	 */
	protected function getIdPrefix () {
		return "last_edit";
	}
	
}

?>