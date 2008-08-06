<?php
/**
 * @since 2/14/08
 * @package segue.ui1
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_wiki_component.act.php,v 1.1 2008/02/14 21:15:46 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Action for adding components from wiki-links
 * 
 * @since 2/14/08
 * @package segue.ui1
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_wiki_component.act.php,v 1.1 2008/02/14 21:15:46 adamfranco Exp $
 */
class add_wiki_componentAction
	extends MainWindowAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/14/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 2/14/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		ob_start();
		print "<p>";
		print _("This feature is not yet implemented. Please try again later.");
		print "</p>\n<p>";
		print "<a href='".strip_tags($_SERVER['HTTP_REFERER'])."'>&laquo;"._("Go Back")."</a>";
		print "</p>";
		
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}
	
}

?>