<?php
/**
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add tables for the SiteThemes
 * 
 * @since 6/12/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update014_RebuildImplicitAZsAction
	extends Update
{
	
	/**
	 * @var boolean $checkSeparate;  Tell the list to check speparately since that takes a while.
	 * @access public
	 * @since 6/12/08
	 */
	public $checkSeparate = true;
	
	/**
	 * @var array $toDo;  
	 * @access private
	 * @since 6/13/08
	 */
	private $toDo = array();
	
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 6/12/08
	 */
	function getDateIntroduced () {
		return Date::withYearMonthDay(2008, 8, 7);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 6/12/08
	 */
	function getTitle () {
		return _("Rebuild Implicit view AZs");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 6/12/08
	 */
	function getDescription () {
		return _("This update rebuild all of the Implicit 'view' Authorizations that should be cascading up. This state was caused by a bug in Segue beta 20 to beta 29 (fixed in beta 30).");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/12/08
	 */
	function isInPlace () {
		$hierarchyMgr = Services::getService("HierarchyManager");
		$idMgr = Services::getService("IdManager");	
		$hierarchyId = $idMgr->getId("edu.middlebury.authorization.hierarchy");
		$hierarchy = $hierarchyMgr->getHierarchy($hierarchyId);
		
		$view = $idMgr->getId("edu.middlebury.authorization.view");
		
		$authZ = Services::getService("AuthZ");
		
		$query = new SelectQuery();
		$query->addColumn('COUNT(az2_explicit_az.id)', 'num');
		$query->addTable('az2_explicit_az');
		$query->addTable('az2_j_node_node', INNER_JOIN, 'az2_j_node_node.fk_child = az2_explicit_az.fk_qualifier');
		$query->addTable('az2_implicit_az', LEFT_JOIN, '(az2_implicit_az.fk_explicit_az = az2_explicit_az.id AND az2_j_node_node.fk_parent = az2_implicit_az.fk_qualifier)');
		$query->addWhereEqual('az2_explicit_az.fk_function', 'edu.middlebury.authorization.view');
		$query->addWhereNull('az2_implicit_az.fk_explicit_az');
		
// 		printpre($query->asString());
		$dbc = Services::getService('DatabaseManager');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		$num = $result->field('num');
		$result->free();
		
// 		$explicitAZs = $authZ->getExplicitAZs(null, $view, null, false);
// 		$status = new StatusStars(_("Checking Explicit View AZs"));
// 		$status->initializeStatistics($explicitAZs->count());
// 		$this->toDo = array();
// 		while ($explicitAZs->hasNext()) {
// 			$az = $explicitAZs->next();
// 			$status->updateStatistics();
// 			$qualifier = $az->getQualifier();
// 			$parents = $qualifier->getParents();
// 			if ($parents->hasNext()) {
// 				if (!$authZ->isAuthorized($az->getAgentId(), $view, $parents->next()->getId())) {
// 					$this->toDo[] = $hierarchy->getNode($qualifier->getId());
// 				}
// 			}
// 		}
		
		if ($num) {
			printpre(str_replace('%1', $num, _("%1 nodes found with missing implicit AZs.")));
			return false;
		}
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/12/08
	 */
	function runUpdate () {
		set_time_limit(600);
		
		$hierarchyMgr = Services::getService("HierarchyManager");
		$idMgr = Services::getService("IdManager");	
		$hierarchyId = $idMgr->getId("edu.middlebury.authorization.hierarchy");
		$hierarchy = $hierarchyMgr->getHierarchy($hierarchyId);
		
		$view = $idMgr->getId("edu.middlebury.authorization.view");
		
		$authZ = Services::getService("AuthZ");
		
		$query = new SelectQuery();
		$query->addColumn('az2_explicit_az.id', 'explicit_az_id');
		$query->addTable('az2_explicit_az');
		$query->addTable('az2_j_node_node', INNER_JOIN, 'az2_j_node_node.fk_child = az2_explicit_az.fk_qualifier');
		$query->addTable('az2_implicit_az', LEFT_JOIN, '(az2_implicit_az.fk_explicit_az = az2_explicit_az.id AND az2_j_node_node.fk_parent = az2_implicit_az.fk_qualifier)');
		$query->addWhereEqual('az2_explicit_az.fk_function', 'edu.middlebury.authorization.view');
		$query->addWhereNull('az2_implicit_az.fk_explicit_az');
		
		$dbc = Services::getService('DatabaseManager');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		$status = new StatusStars(str_replace('%1', $result->getNumberOfRows(), _("Rebuilding cascading-up implicit 'view' AZs on %1 nodes.")));
		$status->initializeStatistics($result->getNumberOfRows());
		$azCache = $authZ->getAuthorizationCache();
		while ($result->hasNext()) {
			$row = $result->next();
			$azCache->createImplicitAZsUpForAZ($azCache->getExplicitAZById($row['explicit_az_id']));
			
			$status->updateStatistics();
		}
		return true;
	}
}

?>