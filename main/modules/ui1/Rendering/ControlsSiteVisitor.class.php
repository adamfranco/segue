<?php
/**
 * @since 4/17/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.3 2007/05/15 16:48:24 adamfranco Exp $
 */ 

/**
 * Returns the controls strings for each component type
 * 
 * @since 4/17/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.3 2007/05/15 16:48:24 adamfranco Exp $
 */
class ControlsSiteVisitor {
	
	var $_action = 'editview';
		
	/**
	 * Set the action to return to
	 * 
	 * @param string $returnAction
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function setReturnAction ($returnAction) {
		$this->_action = $returnAction;
	}
	
	
	/**
	 * print common controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function controlsStart ( &$siteComponent ) {
		$harmoni =& Harmoni::instance();
		ob_start();
		
		print "\n<div style='text-align: right;'>";
		
// 		print "\n\t\t\t<form method='post'";
// 		print " action='";
// 		print $harmoni->request->quickURL('ui1', 'modifyComponent',
// 				array('node' => $siteComponent->getId(),
// 					"returnNode" => RequestContext::value('node'),
// 					'returnAction' => $this->_action));
// 		print "'";
// 		print " class='controls_form'";
// 		print ">";
		
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
	function &controlsEnd ( &$siteComponent ) {
// 		print "\n\t\t\t\t<div style='text-align: right;'>";
// 		print "<input type='submit' value='"._("Apply Changes")."'/>";
		print "</div>";
// 		print "\n\t\t\t</form>";
		
		$controls = ob_get_clean();
// 		$harmoni =& Harmoni::instance();
// 		$harmoni->request->endNamespace();
		return $controls;
	}
	
	/**
	 * Print delete controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printDelete ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"), 
			$siteComponent->getQualifierId()))
		{
		
			$message = _("Are you sure that you wish to delete this component and all of its children?");
			$url = 	$harmoni->request->quickURL('ui1', 'deleteComponent', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
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
	}
	
	/**
	 * Print the edit controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function printEdit ( &$siteComponent, $action ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', $action, array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
			
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("edit");
			print "</a>";
		}
	}
	
	/**
	 * Print the reorder controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function printReorder ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		$parent =& $siteComponent->getParentComponent();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$parent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'editContent', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
			$url = "#";
			
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("reorder");
			print "</a>";
		}
	}
	
	/**
	 * Print the edit controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function printMove ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		$url = 	$harmoni->request->quickURL('ui1', 'editContentWizard', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
		$url = "#";
			
		print "\n\t\t\t\t\t<a href='".$url."'>";
		print _("move");
		print "</a>";
	}
	
	
	/**
	 * Print the versions link
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function printVersions ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		$url = 	$harmoni->request->quickURL('ui1', 'versions', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
			
		print "\n\t\t\t\t\t<a href='".$url."'>";
		print _("versions");
		print "</a>";
	}
	
	/**
	 * Print the form to add a submenu
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/22/06
	 */
	function printAddSubMenu ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		print "\n\t\t\t\t<div style='font-weight: bold;'>";
		print _("Sub-Menu: ");
		
		if ($siteComponent->subMenuExists()) {
			print _("created");
		} else {
			$parentMenuOrganizer =& $siteComponent->getMenuOrganizer();
			
			$harmoni =& Harmoni::instance();
			$message = _("Are you sure that you wish to create a submenu?");
			$url = str_replace('&amp;', '&', 
					$harmoni->request->quickURL('ui1', 'createSubMenu', array(
						'parent' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action,
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
		$this->controlsStart($siteComponent);
		
		$this->printReorder($siteComponent);
		$this->printMove($siteComponent);
		$this->printEdit($siteComponent, 'editContent');
		$this->printDelete($siteComponent);
		$this->printVersions($siteComponent);
		
		return $this->controlsEnd($siteComponent);
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
		$this->controlsStart($siteComponent);
		
		$this->printReorder($siteComponent);
		$this->printMove($siteComponent);
		$this->printEdit($siteComponent, 'editNav');
		$this->printDelete($siteComponent);
// 		$this->printVersions($siteComponent);
// 		$this->printAddSubMenu($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
	function &visitSiteNavBlock ( &$siteComponent ) {
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
	function &visitFixedOrganizer ( &$siteComponent ) {
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
	function &visitNavOrganizer ( &$siteComponent ) {
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
	function &visitFlowOrganizer ( &$siteComponent ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'editFlowOrg', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
		
			ob_start();
			
			print "\n\t\t\t\t<div style='text-align: center;'>";
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("page display options");
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
	function &visitMenuOrganizer ( &$siteComponent ) {
// 		$this->controlsStart($siteComponent);
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$harmoni =& Harmoni::instance();
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'editMenu', array(
						'node' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
		
			ob_start();
			
			print "\n\t\t\t\t<div style='text-align: center;'>";
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("menu display options");
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