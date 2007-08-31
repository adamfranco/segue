<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ModifySettingsSiteVisitor.class.php,v 1.7 2007/08/31 17:35:07 achapin Exp $
 */ 
 
 require_once(dirname(__FILE__)."/SiteVisitor.interface.php");

/**
 * This class works in conjunction with the ControlsSiteVisitor to apply changes
 * to components based on their controls.
 * 
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ModifySettingsSiteVisitor.class.php,v 1.7 2007/08/31 17:35:07 achapin Exp $
 */
class ModifySettingsSiteVisitor 
	implements SiteVisitor
{
		
	/**
	 * print common controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function modifyStart ( &$siteComponent ) {
// 		$harmoni->request->startNamespace('controls_form_'.$siteComponent->getId());
	}
	
	/**
	 * End the controls block
	 * 
	 * @param SiteComponent $siteComponent
	 * @return ref string
	 * @access public
	 * @since 4/17/06
	 */
	function &modifyEnd ( &$siteComponent ) {
// 		$harmoni =& Harmoni::instance();
// 		$harmoni->request->endNamespace();
		$null = null;
		return $null;
	}
	
	/**
	 * Print displayName controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function applyDisplayName ( &$siteComponent ) {
		if(RequestContext::value('displayName') 
			&& RequestContext::value('displayName') != $siteComponent->getDisplayName())
		{
			$siteComponent->updateDisplayName(RequestContext::value('displayName'));
		}
	}
	
	/**
	 * Apply the description changes
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function applyDescription ( &$siteComponent ) {
		if(RequestContext::value('description') 
			&& RequestContext::value('description') != $siteComponent->getDescription())
		{
			$siteComponent->updateDescription(RequestContext::value('description'));
		}
	}
	
	/**
	 * Apply the description changes
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function applyShowDisplayNames ( &$siteComponent ) {
		if(RequestContext::value('showDisplayNames') 
			&& RequestContext::value('showDisplayNames') !== $siteComponent->showDisplayNames())
		{
			$siteComponent->updateShowDisplayNames(RequestContext::value('showDisplayNames'));
		}
	}
	
	/**
	 * Apply the comments changes
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function applyCommentsEnabled ( &$siteComponent ) {
		if(RequestContext::value('commentsEnabled') 
			&& RequestContext::value('commentsEnabled') !== $siteComponent->commentsEnabled())
		{
			$siteComponent->updateCommentsEnabled(RequestContext::value('commentsEnabled'));
		}
	}
	
	/**
	 * Apply the description changes
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function applyWidth ( &$siteComponent ) {
		if(RequestContext::value('width') 
			&& RequestContext::value('width') !== $siteComponent->getWidth())
		{
			$siteComponent->updateWidth(RequestContext::value('width'));
		}
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function applyRowsColumns ( &$siteComponent ) {
		if(RequestContext::value('rows') != $siteComponent->getNumRows()) {
			$siteComponent->updateNumRows(RequestContext::value('rows'));
		}
		
		if(RequestContext::value('columns') != $siteComponent->getNumColumns()) {
			$siteComponent->updateNumColumns(RequestContext::value('columns'));
		}
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function applyDirection ( &$siteComponent ) {
		$directions = array(
			"Left-Right/Top-Bottom" => _("Left-Right/Top-Bottom"),
			"Top-Bottom/Left-Right" => _("Top-Bottom/Left-Right"),
			"Right-Left/Top-Bottom" => _("Right-Left/Top-Bottom"),
			"Top-Bottom/Right-Left" => _("Top-Bottom/Right-Left"),
			"Left-Right/Bottom-Top" => _("Left-Right/Bottom-Top"),
			"Bottom-Top/Left-Right" => _("Bottom-Top/Left-Right"),
			"Right-Left/Bottom-Top" => _("Right-Left/Bottom-Top"),
			"Bottom-Top/Right-Left" => _("Bottom-Top/Right-Left")
		);
		
		if(RequestContext::value('direction') 
			&& in_array(RequestContext::value('direction'), array_keys($directions))
			&& RequestContext::value('direction') != $siteComponent->getDirection())
		{
			$siteComponent->updateDirection(RequestContext::value('direction'));
		}
	}
	
	/**
	 * Answer controls for Block SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDisplayName($siteComponent);
		$this->applyDescription($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		$this->applyWidth($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}

	/**
	 * Visit a block and return the resulting GUI component. (A menu item)
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object MenuItem 
	 * @access public
	 * @since 4/3/06
	 */
	public function visitBlockInMenu ( BlockSiteComponent $block ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($block->getId())))
		{
			$false = false;
			return $false;
		}
	}

	
	/**
	 * Answer controls for NavBlock SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDisplayName($siteComponent);
		$this->applyDescription($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
	/**
	 * Answer controls for NavBlock SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDisplayName($siteComponent);
		$this->applyDescription($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
	/**
	 * Answer controls for FixedOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
// 		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		$this->applyWidth($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
	/**
	 * Answer controls for NavOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
// 		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		$this->applyWidth($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
	/**
	 * Answer controls for FlowOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		$this->applyWidth($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
	/**
	 * Answer controls for MenuOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		$this->applyCommentsEnabled($siteComponent);
		$this->applyWidth($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
}

?>