<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.1 2006/02/22 19:40:45 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/view.act.php");

/**
 * display the site with editing options.
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.1 2006/02/22 19:40:45 adamfranco Exp $
 */
class editviewAction 
	extends viewAction
{
	/**
	 * If true, editing controls will be displayed
	 * @var boolean $_showControls;  
	 * @access private
	 * @since 2/22/06
	 */
	var $_showControls = true;
}