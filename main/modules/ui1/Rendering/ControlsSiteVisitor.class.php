<?php
/**
 * @since 4/17/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.11 2007/09/20 20:52:15 adamfranco Exp $
 */ 

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
 * @version $Id: ControlsSiteVisitor.class.php,v 1.11 2007/09/20 20:52:15 adamfranco Exp $
 */
class ControlsSiteVisitor 
	implements SiteVisitor
{
	
	var $_action = 'editview';
	
	/**
	 * @var boolean $reorderJsPrinted;  
	 * @access private
	 * @since 9/20/07
	 */
	private $reorderJsPrinted = false;
		
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
	function controlsStart ( $siteComponent ) {
		$harmoni = Harmoni::instance();
		ob_start();
		
		print "\n<div class='ui1_controls'>";
		
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

		if (!$this->reorderJsPrinted) {
			$js = <<<END
		<script type='text/javascript'>
		// <![CDATA[
		
			/**
			 * Show all of the reorder forms for the children of an organizer.
			 * 
			 * @param string organizerId
			 * @return void
			 * @access public
			 * @since 9/20/07
			 */
			function showReorder (organizerId) {
				var links = document.getElementsByClassName('reorder_link_' + organizerId);
				var forms = document.getElementsByClassName('reorder_form_' + organizerId);
				
				for (var i = 0; i < links.length; i++) {
					links[i].style.display = 'none';
				}
				
				for (var i = 0; i < forms.length; i++) {
					forms[i].style.display = 'block';
				}
			}
			
			/**
			 * Hide all of the reorder forms for the children of an organizer.
			 * 
			 * @param string organizerId
			 * @return void
			 * @access public
			 * @since 9/20/07
			 */
			function hideReorder (organizerId) {
				var links = document.getElementsByClassName('reorder_link_' + organizerId);
				var forms = document.getElementsByClassName('reorder_form_' + organizerId);
				
				for (var i = 0; i < links.length; i++) {
					links[i].style.display = 'inline';
				}
				
				for (var i = 0; i < forms.length; i++) {
					forms[i].style.display = 'none';
				}
			}
		
		// ]]>
		</script>
		
END;
			$output = $harmoni->getOutputHandler();
			$output->setHead($output->getHead().$js);
		
		}
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
	
	function printDelimiter ( $siteComponent ) {
		print " | ";
	}

	
	/**
	 * Print delete controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printDelete ( $siteComponent ) {
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
	function printEdit ( $siteComponent, $action ) {
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
	function printReorder ( $siteComponent ) {
		$this->printReorderLink($siteComponent);
		$this->printReorderForm($siteComponent);
	}
	
	/**
	 * Print the reorder link
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/20/07
	 */
	public function printReorderLink (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		$parent = $siteComponent->getParentComponent();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$parent->getQualifierId()))
		{			
			print "\n\t\t\t\t\t<a href='#' class='reorder_link_".$parent->getId()."' onclick=\"";
			print 	"showReorder('".$parent->getId()."'); ";
			print 	"return false; ";
			print	"\">";
			print _("reorder");
			print "</a>";
		}
	}
	
	/**
	 * Print the reorder form
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/20/07
	 */
	public function printReorderForm (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		$parent = $siteComponent->getParentComponent();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$parent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL('ui1', 'reorder', array(
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action
						));
			
			$harmoni->request->startNamespace('reorder');
			
			$organizer = $siteComponent->getParentComponent();
			$myCell = $organizer->getCellForSubcomponent($siteComponent);
			
			print "\n\t\t\t\t\t<form class='ui1_controls reorder_form_".$parent->getId()."' action='".$url."' method='post' style='display: none'>";
			print "\n\t<input type='hidden' name='".RequestContext::name('node')."' value='".$siteComponent->getId()."' />";
			
			print "\n\t<select name='".RequestContext::name('position')."' onchange='this.form.submit();'>";
			for ($i = 0; $i < $organizer->getTotalNumberOfCells(); $i++) {
				print "\n\t\t<option value='$i'";
				if ($myCell == $i)
					print " selected='selected'";
				print ">".($i+1)."</option>";
			}
			print "\n\t</select>";
			print "\n\t<input type='button' onclick=\"";
			print 	"hideReorder('".$parent->getId()."'); ";
			print "\" value='"._("Cancel")."'/>";
			print "\n\t\t\t\t\t</form>";
			
			$harmoni->request->endNamespace();
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
	function printMove ( $siteComponent ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
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
	function printVersions ( $siteComponent ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
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
	function printAddSubMenu ( $siteComponent ) {
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
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$this->printReorder($siteComponent);
		$this->printDelimiter($siteComponent);
		$this->printMove($siteComponent);
		$this->printDelimiter($siteComponent);
		$this->printEdit($siteComponent, 'editContent');
		$this->printDelimiter($siteComponent);
		$this->printDelete($siteComponent);
		$this->printDelimiter($siteComponent);
		$this->printVersions($siteComponent);
		
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
		
		$this->printReorder($siteComponent);
		$this->printDelimiter($siteComponent);
		$this->printMove($siteComponent);
		$this->printDelimiter($siteComponent);
		$this->printEdit($siteComponent, 'editNav');
		$this->printDelimiter($siteComponent);
		$this->printDelete($siteComponent);
// 		$this->printVersions($siteComponent);
// 		$this->printAddSubMenu($siteComponent);
		
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
						'returnAction' => $this->_action
						));
		
			ob_start();
			
			print "\n\t\t\t\t<div style='text-align: center;'>";
			print "\n\t\t\t\t\t<a href='".$url."'>";
			print _("[ page display options ]");
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
						'returnAction' => $this->_action
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