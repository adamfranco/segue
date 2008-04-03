<?php
/**
 * @since 4/3/08
 * @package segue.resolver
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: segue1.act.php,v 1.1 2008/04/03 15:18:15 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * This action will try to resolve Segue 1-style parameters into a valid url.
 * Parameters accepted will likely be in the Request context, but will be named
 * according to Segue 1 expectations: site=xxx, section=yyy, page=zzzz, story=wwww
 * 
 * @since 4/3/08
 * @package segue.resolver
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: segue1.act.php,v 1.1 2008/04/03 15:18:15 adamfranco Exp $
 */
class segue1Action
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/3/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/3/08
	 */
	public function execute () {
		// Add in RequestContext data from URLs that were converted to Segue2 format,
		// but retain Segue1 parameters
		$get = array('action' => 'site');
		$segue1Identifiers = array('site', 'section', 'page', 'story');
		foreach ($segue1Identifiers as $key) {
			if (!isset($get[$key]) && RequestContext::value($key))
				$get[$key] = RequestContext::value($key);
		}
		
		$resolver = Segue1UrlResolver::instance();
		$resolver->resolveGetArray($get);
		
		// If the resolver didn't forward us, try just going to the site listed.
		if (isset($get['site']))
			RequestContext::sendTo(MYURL."/sites/".$get['site']);
		else
			throw new NullArgumentException("Could no resolve URL, no site specified.");
	}
	
}

?>