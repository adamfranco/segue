<?php
/**
 * @since 4/17/08
 * @package  segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");
require_once(HARMONI.'/oki2/AuthZ2/authz/AuthorizationManager.class.php');
require_once(HARMONI.'/oki2/AuthZ2/hierarchy/HierarchyManager.class.php');

/**
 * <##>
 * 
 * @since 4/17/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update007_AuthZ2Action
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/24/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 4, 17);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Authorization 2");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will install the new authorization and hierarchy tables as well as migrate data to them. If the update is successfull, the original Hierarchy and Authorization tables will be removed. 
		
		<br/><br/><strong>Caution: This update will cause irrevocable changes to your database. Back up your data before running it.</strong> 
		
		<br/><br/>Disable user access to Segue while running this update. This update should take about 15 minutes to run on a 10,000-node hierarchy.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		
		$az2Tables = array(
			'az2_explicit_az',
			'az2_function',
			'az2_function_type',
			'az2_hierarchy',
			'az2_implicit_az',
			'az2_j_node_node',
			'az2_node',
			'az2_node_ancestry',
			'az2_node_type'
		);
		
		$azTables = array(
			'az_authorization',
			'az_function',
			'hierarchy',
			'j_node_node',
			'node',
			'node_ancestry'
		);
		
		$tables = $dbc->getTableList();
		// Check for new tables
		foreach ($az2Tables as $table) {
			if (!in_array($table, $tables))
				return false;
		}
		
		// Check for old tables
		foreach ($azTables as $table) {
			if (in_array($table, $tables))
				return false;
		}
		
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function runUpdate () {
		$prepStatus =  new StatusStars("Preparing Migration");
		$prepStatus->initializeStatistics(3);
		$prepStatus->updateStatistics();
		
		$dbc = Services::getService("DatabaseManager");
		try {
			
			/*********************************************************
			 * Check for the old tables. They must exist for us to run
			 *********************************************************/
			$azTables = array(
					'az_authorization',
					'az_function',
					'hierarchy',
					'j_node_node',
					'node',
					'node_ancestry'
				);
			// Check for old tables
			$tables = $dbc->getTableList(IMPORTER_CONNECTION);
			foreach ($azTables as $table) {
				if (!in_array($table, $tables))
					throw new Exception ("Old AZ table, $table, is missing. Can not run Update.");
			}
			
			/*********************************************************
			 * Create the new tables
			 *********************************************************/
			$type = $dbc->getDatabaseType(IMPORTER_CONNECTION);
			switch ($type) {
				case MYSQL:
					SQLUtils::runSQLfile(HARMONI_BASE."/SQL/MySQL/AuthZ2.sql", IMPORTER_CONNECTION);
					break;
				case POSTGRESQL:
					SQLUtils::runSQLfile(HARMONI_BASE."/SQL/PostgreSQL/AuthZ2.sql", IMPORTER_CONNECTION);
					break;
				case ORACLE:
					SQLUtils::runSQLfile(HARMONI_BASE."/SQL/PostgreSQL/AuthZ2.sql", IMPORTER_CONNECTION);
					break;
				default:
					throw new Exception("Database schemas are not defined for specified database type.");
			}
			
			/*********************************************************
			 * Hierarchy
			 *********************************************************/
			$hierarchyMgr1 = Services::getService("Hierarchy");
			if (get_class($hierarchyMgr1) == "AuthZ2_HierarchyManager")
				throw new OperationFailedException("Original HierarchyManager not configured.");
			
			$hierarchyMgr2 = new AuthZ2_HierarchyManager();
			$azMgr2 = new AuthZ2_AuthorizationManager();
			$azMgr2->setHierarchyManager($hierarchyMgr2);
			
			
			$hierarchyMgr2->assignConfiguration($hierarchyMgr1->_configuration);
			
			/*********************************************************
			 * Authorization
			 *********************************************************/
			$azMgr1 = Services::getService("AuthZ");
			
			if (get_class($hierarchyMgr1) == "AuthZ2_AuthorizationManager")
				throw new OperationFailedException("Original HierarchyManager not configured.");
			
			$azMgr2->assignConfiguration($azMgr1->_configuration);
			
			
			$prepStatus->updateStatistics();
			
			/*********************************************************
			 * Hierarchies
			 *********************************************************/
			
			
			$hierarchies = $hierarchyMgr1->getHierarchies();
			$prepStatus->updateStatistics();
			while ($hierarchies->hasNext()) {
				$hierarchy = $hierarchies->next();
				
				try {
					$newHierarchy = $hierarchyMgr2->getHierarchy($hierarchy->getId());
				} 
				// Create a new hierarchy
				catch (UnknownIdException $e) {
					$newHierarchy = $hierarchyMgr2->createHierarchy(
						$hierarchy->getDisplayName(),
						array(),
						$hierarchy->getDescription(),
						$hierarchy->allowsMultipleParents(),
						$hierarchy->allowsRecursion(),
						$hierarchy->getId());
				}
				
				$query = new SelectQuery;
				$query->addTable("node");
				$query->addColumn("COUNT(*)", "num");
				$query->addWhereEqual("fk_hierarchy", $hierarchy->getId()->getIdString());
				$dbc = Services::getService("DatabaseManager");
				$result = $dbc->query($query);
				$this->nodeStatus = new StatusStars("Migrating nodes in the '".$hierarchy->getDisplayName()."' Hierarchy.");
				$this->nodeStatus->initializeStatistics($result->field("num"));
				
				// Add all of the nodes
				$nodes = $hierarchy->getRootNodes();
				while ($nodes->hasNext()) {
					$this->addNode($newHierarchy, $nodes->next());
				}
			
			}
			
			
			/*********************************************************
			 * Authorizations
			 *********************************************************/
			$azMgr1 = Services::getService("AuthZ");
			
			if (get_class($hierarchyMgr1) == "AuthZ2_AuthorizationManager")
				throw new OperationFailedException("Original HierarchyManager not configured.");
			
			
			// Add all of the Authorization functions
			$functionTypes = $azMgr1->getFunctionTypes();
			while ($functionTypes->hasNext()) {
				$oldFunctions = $azMgr1->getFunctions($functionTypes->next());
				while ($oldFunctions->hasNext()) {
					$oldFunction = $oldFunctions->next();
					
					// Get or create the function
					try {
						$newFunction = $azMgr2->getFunction($oldFunction->getId());
					} catch (UnknownIdException $e) {
						$newFunction = $azMgr2->createFunction(
							$oldFunction->getId(),
							$oldFunction->getReferenceName(),
							$oldFunction->getDescription(),
							$oldFunction->getFunctionType(),
							$oldFunction->getQualifierHierarchyId());
					}
					
					// Get all authorizations for this function.
					$oldAZs = $azMgr1->getExplicitAZs(null, $oldFunction->getId(), null, false);
					
					$status = new StatusStars("Migrating '".$newFunction->getReferenceName()."' Authorizations (".$oldAZs->count().")");
					$status->initializeStatistics($oldAZs->count());
					
					while ($oldAZs->hasNext()) {
						$oldAZ = $oldAZs->next();
						
						$status->updateStatistics();
						
						try {
							$oldQualifier = $oldAZ->getQualifier();
						} catch (UnknownIdException $e) {
							// continue if the qualifier no longer exists.
							continue;
						}
						
						// Add the new authorization
						try {
							$newAZ = $azMgr2->createAuthorization(
									$oldAZ->getAgentId(),
									$oldAZ->getFunction()->getId(),
									$oldQualifier->getId());
							
							if ($oldAZ->getExpirationDate())
								$newAZ->updateExpirationDate($oldAZ->getExpirationDate());
							
							if ($oldAZ->getEffectiveDate())
								$newAZ->updateEffectiveDate($oldAZ->getEffectiveDate());
						}
						// If it already exists, continue
						catch (OperationFailedException $e) {
						
						}
					}
				}
			}
		} catch (Exception $e) {
			printpre($e->getMessage());
			HarmoniErrorHandler::printDebugBacktrace($e->getTrace());
			
			printpre("An error has occurred. Removing new tables.");
			
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_implicit_az');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_explicit_az');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_node_ancestry');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_j_node_node');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_function');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_function_type');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_node');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_node_type');
			} catch (DatabaseException $e) {
			}
			try {
				$query = new GenericSQLQuery('TRUNCATE az2_hierarchy');
			} catch (DatabaseException $e) {
			}
			
			
			$query = new GenericSQLQuery('DROP TABLE az2_implicit_az, az2_explicit_az, az2_function, az2_function_type, az2_node_ancestry, az2_j_node_node, az2_node, az2_node_type,  az2_hierarchy;');
			$dbc->query($query, IMPORTER_CONNECTION);
			
			return false;
		}

		/*********************************************************
		 * If we have successfully gotten this far, drop the old 
		 * hierarchy and AuthZ tables to prevent confusion.
		 *********************************************************/
		$query = new GenericSQLQuery('DROP TABLE az_authorization, az_function, hierarchy, j_node_node, node, node_ancestry;');
		$dbc->query($query, IMPORTER_CONNECTION);
		
		return true;
	}
	
	/**
	 * Create a new node and its children
	 * 
	 * @param object Hierarchy $newHierarchy
	 * @param object Node $oldNode
	 * @param optional object Id $parentId 
	 * @return void
	 * @access protected
	 * @since 4/17/08
	 */
	protected function addNode (Hierarchy $newHierarchy, Node $oldNode, Id $parentId = null) {
		// If it has already been created, get it and try to set its parent
		try {
			$newNode = $newHierarchy->getNode($oldNode->getId());
			if (!is_null($parentId)) {
				try {
					$newNode->addParent($parentId);
				} catch (Exception $e) {
					// Do nothing if the child already exists
					if ($e->getMessage() != "A child with the given id already exists!")
						throw $e;
				}
			}
				
		}
		// otherwise, create it
		catch (UnknownIdException $e) {
			if (is_null($parentId))
				$newNode = $newHierarchy->createRootNode(
					$oldNode->getId(),
					$oldNode->getType(),
					$oldNode->getDisplayName(),
					$oldNode->getDescription());
			else
				$newNode = $newHierarchy->createNode(
					$oldNode->getId(),
					$parentId,
					$oldNode->getType(),
					$oldNode->getDisplayName(),
					$oldNode->getDescription());
			
			$this->nodeStatus->updateStatistics();
		}		
		
		$oldChildren = $oldNode->getChildren();
		while ($oldChildren->hasNext()) {
			$oldChild = $oldChildren->next();
			$this->addNode($newHierarchy, $oldChild, $newNode->getId());
		}
	}	
}

?>