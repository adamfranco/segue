<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.2 2007/07/27 17:20:22 adamfranco Exp $
 */ 

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.2 2007/07/27 17:20:22 adamfranco Exp $
 */
class AssetSiteNavBlockSiteComponent
	extends AssetNavBlockSiteComponent
	// implements SiteNavBlockSiteComponent
{	

	/**
	 * Answers nothing because this is a top level nav
	 * 
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		// don't ask me for this... my destination is hard-coded.
		throwError( new Error("SiteNavBlocks do not have self-defined target_id's", "SiteComponents"));
	}

	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitSiteNavBlock($this);
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleDestinationsForPossibleAddition () {
		$results = array();
		return $results;
	}
	
	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 7/26/07
	 */
	function populateWithDefaults () {
		parent::populateWithDefaults();
		
		$authNManager =& Services::getService('AuthN');
		$this->setOwnerId($authNManager->getFirstUserId());
	}
	
/*********************************************************
 * Owner Methods:
 * These allow for the setting and retreval of the owner
 * id of a site.
 *********************************************************/

	
	/**
	 * Answer the id of the owner agent.
	 * 
	 * @return object Id
	 * @access public
	 * @since 7/26/07
	 */
	function &getOwnerId () {
		$idManager =& Services::getService("Id");
		$ownerIdString = $this->_getOwnerIdString();
		if (!is_null($ownerIdString))
			return $idManager->getId($ownerIdString);
		else
			return $idManager->getId("edu.middlebury.agents.anonymous");
	}
	
	/**
	 * Set a new agent id as the owner of this site.
	 * 
	 * @param object Id $agentId
	 * @return void
	 * @access public
	 * @since 7/26/07
	 */
	function setOwnerId ( &$agentId ) {
		// Update the database table
		$query =& new SelectQuery;
		$query->addTable('segue_site_owner');
		$query->addColumn('owner_id');
		$query->addWhereEqual('site_id', $this->getId());
		
		$dbc =& Services::getService('DBHandler');
		$result =& $dbc->query($query, IMPORTER_CONNECTION);
		
		if ($result->getNumberOfRows()) {
			$query =& new UpdateQuery;
			$query->addWhereEqual('site_id', $this->getId());
		} else {
			$query =& new InsertQuery;
			$query->addValue('site_id', $this->getId());
		}
		
		$query->setTable('segue_site_owner');
		$query->addValue('owner_id', $agentId->getIdString());
		
		$dbc =& Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Answer the owner Id string
	 * 
	 * @return string OR null if not found
	 * @access private
	 * @since 7/26/07
	 */
	function _getOwnerIdString () {
		$query =& new SelectQuery;
		$query->addTable('segue_site_owner');
		$query->addColumn('owner_id');
		$query->addWhereEqual('site_id', $this->getId());
		
		$dbc =& Services::getService('DBHandler');
		$result =& $dbc->query($query, IMPORTER_CONNECTION);
		if ($result->getNumberOfRows()) {
			if ($result->field('owner_id') !== '') {
				return $result->field('owner_id');
			}
		}
		
		return null;
	}
}

?>