<?php
/**
 * @since 11/15/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RoleAction.class.php,v 1.5 2008/03/31 20:10:28 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * An abstract class to provide common methods
 * 
 * @since 11/15/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RoleAction.class.php,v 1.5 2008/03/31 20:10:28 adamfranco Exp $
 */
abstract class RoleAction
	extends MainWindowAction
{
		
		/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSiteId () {
		return SiteDispatcher::getCurrentRootNode()->getQualifierId();
	}
	
	/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSite () {
		return SiteDispatcher::getCurrentRootNode();
	}
	
	/**
	 * Answer the qualifier Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getQualifierId () {
		$component = SiteDispatcher::getCurrentNode();
		return $component->getQualifierId();
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponent () {
		return SiteDispatcher::getCurrentNode();
	}
	
	/**
	 * Answer the site component for a given Id
	 * 
	 * @param object Id $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponentForId ( $id ) {
		$director = SiteDispatcher::getSiteDirector();
		return $director->getSiteComponentById($id->getIdString());
	}
	
	/**
	 * Answer the site component for a given Id string
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 6/4/07
	 */
	protected function getSiteComponentForIdString ( $id ) {
		$director = SiteDispatcher::getSiteDirector();
		return $director->getSiteComponentById(strval($id));
	}
}

?>