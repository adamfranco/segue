<?php
/**
 * @since 1/17/07
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeControlsSiteVisitor.class.php,v 1.4 2008/01/10 21:03:29 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/ControlsSiteVisitor.class.php");

/**
 * A version of the ControlsSiteVisitor customized for Edit-Mode
 * 
 * @since 1/17/07
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeControlsSiteVisitor.class.php,v 1.4 2008/01/10 21:03:29 adamfranco Exp $
 */
class EditModeControlsSiteVisitor
	extends ControlsSiteVisitor
{
		
	/**
	 * Answer controls for NavBlock SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printDisplayName($siteComponent);		
		$this->printDescription($siteComponent);
		$this->printShowHistory($siteComponent);
		$this->printCommentSettings($siteComponent);
		$this->printSortMethod($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
}

?>