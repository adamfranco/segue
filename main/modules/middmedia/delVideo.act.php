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
	 * Build the XML content for this action
	 * 
	 * @return void
	 * @access protected
	 * @since 1/14/09
	 */
	protected function buildXml () {
		$this->delVideo(RequestContext::value('directory'), RequestContext::value('file'));
	}
	
}

?>