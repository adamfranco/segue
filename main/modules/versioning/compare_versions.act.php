<?php
/**
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: compare_versions.act.php,v 1.4 2008/04/01 16:21:21 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../view/html.act.php");
require_once(dirname(__FILE__)."/Rendering/HistoryCompareSiteVisitor.class.php");


/**
 * View the history list of a block.
 * 
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: compare_versions.act.php,v 1.4 2008/04/01 16:21:21 adamfranco Exp $
 */
class compare_versionsAction
	extends htmlAction
{
		
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 1/7/08
	 */
	public function getSiteVisitor () {
		if (!isset($this->visitor)) {
			
			$requestedNode = SiteDispatcher::getCurrentNode();
			
			if ($requestedNode->acceptVisitor(new IsBlockVisitor))
				$this->visitor = new HistoryCompareSiteVisitor($requestedNode);
			else
				$this->visitor = new ViewModeSiteVisitor();
		}
		return $this->visitor;
	}
	
}

?>