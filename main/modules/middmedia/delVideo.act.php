<?php
/**
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/MiddMediaAction.class.php');

/**
 * Delete a video
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class delVideoAction
	extends MiddMediaAction
{
		
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/09
	 */
	public function execute () {
		$this->start();
		try {
			$this->delVideo(RequestContext::value('directory'), RequestContext::value('file'));
		} catch (PermissionDeniedException $e) {
			$this->error($e->getMessage());
		} catch (SoapFault $e) {
			$this->error($e->getMessage());
		}
		$this->end();
	}
	
}

?>