<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AccessPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MainPortalCategory.class.php");

require_once(dirname(__FILE__)."/AllVisiblePortalFolder.class.php");


/**
 * A Portal Category is a container for folders. Each category implementation can
 * determine what folders are contained by them statically or programatically.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AccessPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
class SearchPortalCategory
	extends MainPortalCategory 
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 4/1/08
	 */
	public function __construct () {
		$this->folders = array();
		if (isset($_SESSION['portal_searches'])) {
			$this->folders = $_SESSION['portal_searches'];
		}
	}
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Search");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		$harmoni = Harmoni::instance();
		ob_start();
// 		$harmoni->request->startNamespace('portal_search');
		print "\n<form action='";
		print $harmoni->request->quickUrl('portal', 'perform_search'); 
		print "' method='POST'>";
		print "\n\t<input type='text' name='".RequestContext::name('query')."' size='15'/>";
		print "\n\t<input type='submit' value='"._("Search &raquo;")."'/>";
		print "\n</form>";
// 		$harmoni->request->endNamespace();
		return ob_get_clean();
	}
	
	/**
	 * Answer a string Identifier for this category that is unique within this 
	 * category list.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return 'search';
	}
	
}

?>