<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ModifySettingsSiteVisitor.class.php,v 1.3 2007/01/17 21:21:57 adamfranco Exp $
 */ 

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
 * @version $Id: ModifySettingsSiteVisitor.class.php,v 1.3 2007/01/17 21:21:57 adamfranco Exp $
 */
class ModifySettingsSiteVisitor {
		
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
	function &visitBlock ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDisplayName($siteComponent);
		$this->applyDescription($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
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
	function &visitNavBlock ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDisplayName($siteComponent);
		$this->applyDescription($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
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
	function &visitFixedOrganizer ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
// 		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
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
	function &visitNavOrganizer ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
// 		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
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
	function &visitFlowOrganizer ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyRowsColumns($siteComponent);
		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
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
	function &visitMenuOrganizer ( &$siteComponent ) {
		$this->modifyStart($siteComponent);
		
		$this->applyDirection($siteComponent);
		$this->applyShowDisplayNames($siteComponent);
		
		return $this->modifyEnd($siteComponent);
	}
	
}

?>