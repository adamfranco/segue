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
 * Load a list of directories for the current user and return them as an XML file
 * for the JS media library.
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class getDirsAction
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
		foreach ($this->getDirs() as $dir) {
			print "\n<directory ";
			print "name=\"".$dir['name']."\" ";
			print "bytesUsed=\"".$dir['bytesused']."\" ";
			print "bytesAvailable=\"".$dir['bytesavailable']."\" ";
			print "/>";
		}
		$this->end();
	}
	
}

?>