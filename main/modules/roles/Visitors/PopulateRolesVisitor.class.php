<?php
/**
 * @since 11/14/07
 * @package segue.modules.permissions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PopulateRolesVisitor.class.php,v 1.7 2007/11/27 22:06:46 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * <##>
 * 
 * @since 11/14/07
 * @package segue.modules.permissions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PopulateRolesVisitor.class.php,v 1.7 2007/11/27 22:06:46 adamfranco Exp $
 */
class PopulateRolesVisitor
	implements SiteVisitor
{
	
	/**
	 * @var object HierarchicalRadioMatrix $property
	 * @access private
	 * @since 11/14/07
	 */
	private $property;
	
	/**
	 * @var array $qualifierIdsAdded;  
	 * @access private
	 * @since 11/14/07
	 */
	private $qualifierIdsAdded = array();
	
	/**
	 * @var object Id $agentId;  
	 * @access private
	 * @since 11/14/07
	 */
	private $agentId;
	
	/**
	 * Constructor
	 * 
	 * @param object HierarchicalRadioMatrix $property
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	public function __construct (HierarchicalRadioMatrix $property, Id $agentId) {
		$this->property = $property;
		
		$this->agentId = $agentId;
	}
	
	/**
	 * Add a qualifierId
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access private
	 * @since 11/14/07
	 */
	private function addQualifierForSiteComponent (SiteComponent $siteComponent) {
		$qualifierId = $siteComponent->getQualifierId();
		
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		// Skip if we've added it already
		if (in_array($qualifierId->getIdString(), $this->qualifierIdsAdded))
			return;
		$this->qualifierIdsAdded[] = $qualifierId->getIdString();
		
		// Skip any printing of the node if the current user has no authorization 
		// to view the node or any descendents.
		if (!$authZ->isUserAuthorizedBelow($idMgr->getId("edu.middlebury.authorization.view"), $qualifierId)
			&& !$authZ->isUserAuthorizedBelow($idMgr->getId("edu.middlebury.authorization.view_authorizations"), $qualifierId)) 
		{
			return;
		}
		
		// Create the property with the current role
		$parentQualifierId = $siteComponent->getParentComponent()->getQualifierId();
		
		$roleMgr = SegueRoleManager::instance();
		$valuesHidden = false;
		try {
			$role = $roleMgr->getAgentsRole($this->agentId, $qualifierId);
		} catch (PermissionDeniedException $e) {
			$role = $roleMgr->getAgentsRole($this->agentId, $qualifierId, true);
			$valuesHidden = true;
		}
		
		$this->property->addChildField(
			$parentQualifierId->getIdString(), 
			$qualifierId->getIdString(), 
			$siteComponent->getDisplayName(), 
			$role->getIdString(),
			">=");
		
		// Make the values hidden if the current user has no authorization 
		// to view the authorizations of the node.
		if ($valuesHidden) {
			$this->property->makeValuesHidden($qualifierId->getIdString());
		}
		
		// Disable options that are precluded by implicit authorizations
		// coming from group membership.
		$groupRole = $roleMgr->getGroupImplictRole($this->agentId, $qualifierId, true);
		foreach ($roleMgr->getRoles() as $role) {
			if ($role->isLessThan($groupRole)) {
				$this->property->makeDisabled($qualifierId->getIdString(), $role->getIdString());
			}
		}
		
		// Disable options where modify_authorization is not allowed.
		if (!$authZ->isUserAuthorized(
			$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
			$qualifierId)) 
		{
			foreach($roleMgr->getRoles() as $role)
				$this->property->makeDisabled($qualifierId->getIdString(), $role->getIdString());
		}
		
		// Disable the Administrator role for everyone and institute.
		$nonAdminAgents = array();
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.everyone');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.anonymous');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.users');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.institute');
		foreach ($nonAdminAgents as $agentId) {
			if ($agentId->isEqual($this->agentId)) {
				$this->property->makeDisabled($qualifierId->getIdString(), 'admin');
				break;
			}
		}
		
		
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$qualifierId = $siteComponent->getQualifierId();
		
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		// Skip if we've added it already
		if (in_array($qualifierId->getIdString(), $this->qualifierIdsAdded))
			return;
		$this->qualifierIdsAdded[] = $qualifierId->getIdString();
		
		// Skip any printing of the node if the current user has no authorization 
		// to view the node or any descendents.
		if (!$authZ->isUserAuthorizedBelow($idMgr->getId("edu.middlebury.authorization.view"), $qualifierId)
			&& !$authZ->isUserAuthorizedBelow($idMgr->getId("edu.middlebury.authorization.view_authorizations"), $qualifierId)) 
		{
			return;
		}
		
		$roleMgr = SegueRoleManager::instance();
		$valuesHidden = false;
		try {
			$role = $roleMgr->getAgentsRole($this->agentId, $qualifierId);
		} catch (PermissionDeniedException $e) {
			$role = $roleMgr->getAgentsRole($this->agentId, $qualifierId, true);
			$valuesHidden = true;
		}
		
		$this->property->addField(
					$qualifierId->getIdString(), 
					$siteComponent->getDisplayName(), 
					$role->getIdString(),
					">=");
		
		// Make the values hidden if the current user has no authorization 
		// to view the authorizations of the node.
		if ($valuesHidden) {
			$this->property->makeValuesHidden($qualifierId->getIdString());
		}
		
		// Disable options that are precluded by implicit authorizations
		// coming from group membership.
		$groupRole = $roleMgr->getGroupImplictRole($this->agentId, $qualifierId, true);
		foreach ($roleMgr->getRoles() as $role) {
			if ($role->isLessThan($groupRole)) {
				$this->property->makeDisabled($qualifierId->getIdString(), $role->getIdString());
			}
		}
		
		// Disable options where modify_authorization is not allowed.
		if (!$authZ->isUserAuthorized(
			$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
			$qualifierId)) 
		{
			foreach($roleMgr->getRoles() as $role)
				$this->property->makeDisabled($qualifierId->getIdString(), $role->getIdString());
		}
		
		// Disable the Administrator role for everyone and institute.
		$nonAdminAgents = array();
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.everyone');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.anonymous');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.agents.users');
		$nonAdminAgents[] = $idMgr->getId('edu.middlebury.institute');
		foreach ($nonAdminAgents as $agentId) {
			if ($agentId->isEqual($this->agentId)) {
				$this->property->makeDisabled($qualifierId->getIdString(), 'admin');
				break;
			}
		}
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$this->addQualifierForSiteComponent($siteComponent);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object OrganizerSiteComponent $organizer
	 * @return object Component
	 * @access private
	 * @since 4/3/06
	 */
	private function visitOrganizer ( OrganizerSiteComponent $organizer ) {		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child))
				$child->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return $this->visitOrganizer($siteComponent);
	}
	
}

?>