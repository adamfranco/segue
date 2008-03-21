<?php
/**
 * @since 3/21/08
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: missing.act.php,v 1.1 2008/03/21 17:10:47 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/MainWindowAction.class.php');

/**
 * This action displays a message for missing media files
 * 
 * @since 3/21/08
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: missing.act.php,v 1.1 2008/03/21 17:10:47 adamfranco Exp $
 */
class missingAction
	extends MainWindowAction
{
		
	/**
	 * AZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/21/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 3/21/08
	 */
	public function buildContent () {
		$rows = $this->getActionRows();
		
		$message =  _("The file that you requested, '%1', does not exist in this site.");
		$message = str_replace('%1', RequestContext::value('filename'), $message);
		
		$rows->add(new Block($message, HIGHLIT_BLOCK));
	}
	
}

?>