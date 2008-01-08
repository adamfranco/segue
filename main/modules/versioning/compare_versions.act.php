<?php
/**
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: compare_versions.act.php,v 1.2 2008/01/08 21:59:57 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../ui1/view.act.php");
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
 * @version $Id: compare_versions.act.php,v 1.2 2008/01/08 21:59:57 adamfranco Exp $
 */
class compare_versionsAction
	extends viewAction
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
			
			$requestedNode = $this->_director->getSiteComponentById(
				RequestContext::value("node"));
			
			if ($requestedNode->acceptVisitor(new IsBlockVisitor))
				$this->visitor = new HistoryCompareSiteVisitor($requestedNode);
			else
				$this->visitor = new ViewModeSiteVisitor();
		}
		return $this->visitor;
	}
	
}

?>