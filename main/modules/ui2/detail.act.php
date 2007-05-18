<?php
/**
 * @since 5/18/07
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: detail.act.php,v 1.1 2007/05/18 20:00:58 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/view.act.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/DetailViewModeSiteVisitor.class.php");

/**
 * Detail view of a content block and its discussions.
 * 
 * @since 5/18/07
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: detail.act.php,v 1.1 2007/05/18 20:00:58 adamfranco Exp $
 */
class detailAction
	extends viewAction
{
		
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function &getSiteVisitor () {
		if (!isset($this->visitor)) {
			/*********************************************************
			 * Asset version
			 *********************************************************/
			$repositoryManager =& Services::getService('Repository');
			$idManager =& Services::getService('Id');
			
			$director =& new AssetSiteDirector(
				$repositoryManager->getRepository(
					$idManager->getId('edu.middlebury.segue.sites_repository')));			
			
			if (!$nodeId = RequestContext::value("node"))
				throwError(new Error('No site node specified.', 'SiteDisplay'));
			
			
			$this->visitor =& new DetailViewModeSiteVisitor(
				$director->getSiteComponentById($nodeId));
		}
		return $this->visitor;
	}
	
}

?>