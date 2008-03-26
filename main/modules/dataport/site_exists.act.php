<?php
/**
 * @since 3/26/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: site_exists.act.php,v 1.1 2008/03/26 13:52:31 adamfranco Exp $
 */ 

/**
 * Answer a text document with the string 'true' or the string 'false' depending
 * on the existance of a site with that name. This will be used by Segue 1 to 
 * determine redirects.
 * 
 * @since 3/26/08
 * @package segue.dataport.
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: site_exists.act.php,v 1.1 2008/03/26 13:52:31 adamfranco Exp $
 */
class site_existsAction
	extends Action
{
		
	/**
	 * Authorization. As existence is not sensitive information, allow anonymous access.
	 *
	 * @return boolean
	 * @access public
	 * @since 3/26/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	public function execute () {
		$slotMgr = SlotManager::instance();
		if (is_null(RequestContext::value('site')))
			throw new NullArgumentException("No site specified.");
		$slot = $slotMgr->getSlotByShortname(RequestContext::value('site'));
		$exists = $slot->siteExists();
		
		header("Content-Type: text/plain");
		if ($exists)
			print "true";
		else
			print "false";
		
		exit;
	}
	
}

?>