<?php
/**
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.9 2008/04/10 17:42:39 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");

/**
 * Return a bread-crumbs string
 * 
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.9 2008/04/10 17:42:39 adamfranco Exp $
 */
class ParticipationBreadCrumbsVisitor 
	extends BreadCrumbsVisitor
{

	/**
	 * Visit a SiteNavBlock
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		//$this->addLink($siteNavBlock);
		
		ob_start();
		$links = array_reverse($this->_links);
		for ($i = 0; $i < count($links); $i++) {
			print "\n<span style='white-space: nowrap;'>";
			if ($i > 0)
				print $this->_separator;
			print $links[$i];
			print "</span>";
		}
		return ob_get_clean();
	}
	
}

?>