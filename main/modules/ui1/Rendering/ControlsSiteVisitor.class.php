<?php
/**
 * @since 4/17/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.16 2008/01/11 20:03:04 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/ui1/Rendering/GeneralControlsSiteVisitor.abstract.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * Returns the controls strings for each component type
 * 
 * @since 4/17/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.16 2008/01/11 20:03:04 adamfranco Exp $
 */
class ControlsSiteVisitor
	extends GeneralControlsSiteVisitor
	implements SiteVisitor
{
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 9/21/07
	 */
	public function __construct () {
		$this->module = "ui1";
		$this->action = "editview";
	}
		
	/**
	 * Set the action to return to
	 * 
	 * @param string $returnAction
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function setReturnAction ($returnAction) {
		$this->action = $returnAction;
	}
	
	
	/**
	 * print common controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function controlsStart ( $siteComponent ) {
		$harmoni = Harmoni::instance();
		ob_start();
		
		print "\n<div class='ui1_controls'>";
		
// 		print "\n\t\t\t<form method='post'";
// 		print " action='";
// 		print $harmoni->request->quickURL('ui1', 'modifyComponent',
// 				array('node' => $siteComponent->getId(),
// 					"returnNode" => RequestContext::value('node'),
// 					'returnAction' => $this->action));
// 		print "'";
// 		print " class='controls_form'";
// 		print ">";
		
// 		$harmoni->request->startNamespace('controls_form_'.$siteComponent->getId());

		$this->printReorderJS();
	}
	
	/**
	 * End the controls block
	 * 
	 * @param SiteComponent $siteComponent
	 * @return ref string
	 * @access public
	 * @since 4/17/06
	 */
	function controlsEnd ( $siteComponent ) {
// 		print "\n\t\t\t\t<div style='text-align: right;'>";
// 		print "<input type='submit' value='"._("Apply Changes")."'/>";
		print "</div>";
// 		print "\n\t\t\t</form>";
		
		$controls = ob_get_clean();
// 		$harmoni = Harmoni::instance();
// 		$harmoni->request->endNamespace();
		return $controls;
	}

	/**
	 * Prints delimiter between control items
	 * 
	 * @param SiteComponent $siteComponent
	 * @return ref string
	 * @access public
	 * @since 8/21/06
	 */
	
	function getDelimiter ( $siteComponent ) {
		return " | ";
	}

	
	/**
	 * Print delete controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function getDelete ( $siteComponent ) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"), 
			$siteComponent->getQualifierId()))
		{
		
			$message = _("Are you sure that you wish to delete this component and all of its children?");
			$url = 	$harmoni->request->quickURL('ui1', 'deleteComponent', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action
						));
			
			print "\n\t\t\t\t\t<a href='#' onclick='";
			
			print 	"if (confirm(\"".$message."\")) {";
			print 		" var url = \"".$url."\"; ";
			print 		"window.location = url.urlDecodeAmpersands(); ";
			print 	"} ";
			print "return false; ";
			
			print "'>";
			print _("delete");
			print "</a>";
		}
		return ob_get_clean();
	}
	
	/**
	 * Print the edit controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function getEdit ( $siteComponent, $action ) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', $action, array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action
						));
			
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("edit");
			print "</a>";
		}
		return ob_get_clean();
	}
	
	/**
	 * Answer the history control
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 1/10/08
	 */
	public function getHistory (SiteComponent $siteComponent) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$harmoni = Harmoni::instance();
			$harmoni->history->markReturnURL('view_history_'.$siteComponent->getId());
			$url =  $harmoni->request->quickURL('versioning', 'view_history',
					array("node" => $siteComponent->getId(), 
						'returnModule' => $harmoni->request->getRequestedModule(),
						'returnAction' => $harmoni->request->getRequestedAction()));
			
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("history");
			print "</a>";
		}
		return ob_get_clean();
	}
	
	/**
	 * Print the edit controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function getMove ( $siteComponent ) {
		// Moving is not yet implemented, when it is, remove this line to return the
		// control.
		return false;
		
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.remove_children"), 
			$siteComponent->getParentComponent()->getQualifierId()))
		{
			
			$url = 	$harmoni->request->quickURL('ui1', 'editContentWizard', array(
							'node' => $siteComponent->getId(),
							'returnNode' => RequestContext::value('node'),
							'returnAction' => $this->action
							));
			$url = "#";
				
			
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("move");
			print "</a>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Print the form to add a submenu
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/22/06
	 */
	function getAddSubMenu ( $siteComponent ) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		print "\n\t\t\t\t<div style='font-weight: bold;'>";
		print _("Sub-Menu: ");
		
		if ($siteComponent->subMenuExists()) {
			print _("created");
		} else {
			$parentMenuOrganizer = $siteComponent->getMenuOrganizer();
			
			$harmoni = Harmoni::instance();
			$message = _("Are you sure that you wish to create a submenu?");
			$url = str_replace('&amp;', '&', 
					$harmoni->request->quickURL('ui1', 'createSubMenu', array(
						'parent' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action,
						'direction' => urlencode($parentMenuOrganizer->getDirection()))));
			
			print "\n\t\t\t\t\t<button onclick='";
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), 
				$siteComponent->getQualifierId()))
			{
				print 	"if (confirm(\"".$message."\")) ";
				print 		"window.location = \"".$url."\";";
			} else {
				print "alert(\""._('You are not authorized to create a submenu.')."\"); return false;";
			}
			print "'>";
			print _("create");
			print "</button>";
		}
		
		print "\n\t\t\t\t</div>";
		return ob_get_clean();
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
		$this->controlsStart($siteComponent);
		
		$controls = array();
		if ($control = $this->getReorder($siteComponent))
			$controls[] = $control;
		if ($control = $this->getMove($siteComponent))
			$controls[] = $control;
		if ($control = $this->getEdit($siteComponent, 'editContent'))
			$controls[] = $control;
		if ($control = $this->getDelete($siteComponent))
			$controls[] = $control;
		if ($control = $this->getHistory($siteComponent))
			$controls[] = $control;
			
		print implode($this->getDelimiter($siteComponent), $controls);
		
		return $this->controlsEnd($siteComponent);
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
	 * Answer controls for NavBlock SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$controls = array();
		if ($control = $this->getReorder($siteComponent))
			$controls[] = $control;
		if ($control = $this->getMove($siteComponent))
			$controls[] = $control;
		if ($control = $this->getEdit($siteComponent, 'editNav'))
			$controls[] = $control;
		if ($control = $this->getDelete($siteComponent))
			$controls[] = $control;
// 		if ($control = $this->getHistory($siteComponent))
// 			$controls[] = $control;
// 		if ($control = $this->getAddSubMenu($siteComponent))
// 			$controls[] = $control;
		
		print implode($this->getDelimiter($siteComponent), $controls);
		
		return $this->controlsEnd($siteComponent);
	}
	
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printDisplayName($siteComponent);		
		$this->printDescription($siteComponent);
		
		return $this->controlsEnd($siteComponent);
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
		$this->controlsStart($siteComponent);
		
		$this->printRowsColumns($siteComponent);
// 		$this->printDirection($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
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
		$this->controlsStart($siteComponent);
		
		$this->printRowsColumns($siteComponent);
// 		$this->printDirection($siteComponent);
		
		return $this->controlsEnd($siteComponent);
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'editFlowOrg', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action
						));
		
			ob_start();
			
			print "\n\t\t\t\t<div style='text-align: center;'>";
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("[ display options ]");
			print "</a>";
			print "\n\t\t\t\t</div>";
			
			$controls = ob_get_clean();
		} else {
			$controls = '';
		}
		
		
		return $controls;
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
// 		$this->controlsStart($siteComponent);
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'editMenu', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action
						));
		
			ob_start();
			
			print "\n\t\t\t\t<div style='text-align: center;'>";
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("[ menu display options ]");
			print "</a>";
			print "\n\t\t\t\t</div>";
			
			$controls = ob_get_clean();
		} else {
			$controls = '';
		}
		
		
		return $controls;
// 		return $this->controlsEnd($siteComponent);
	}
	
}

?>