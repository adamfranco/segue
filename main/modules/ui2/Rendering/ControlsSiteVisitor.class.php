<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.22 2008/04/09 21:12:03 adamfranco Exp $
 */ 
 
 require_once(MYDIR."/main/modules/ui1/Rendering/GeneralControlsSiteVisitor.abstract.php");
 require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * Returns the controls strings for each component type
 * 
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.22 2008/04/09 21:12:03 adamfranco Exp $
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
		$this->module = "ui2";
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
		
		print "\n\t\t\t<form method='post'";
		print " action='";
		print $harmoni->request->quickURL('ui2', 'modifyComponent',
				array('node' => $siteComponent->getId(),
					"returnNode" => SiteDispatcher::getCurrentNodeId(),
					'returnAction' => $this->action));
		print "'";
		print " class='controls_form'";
		print ">";
		print "\n\t\t\t<div style='float: left;'>";
		print "\n\t\t\t<table align='right' cellspacing='0' cellpadding='0'>";
		
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
		
		print "\n\t\t\t</table>";
		print "\n\t\t\t</div>";
		print "\n\t\t\t\t<div style='text-align: right;'>";
		print "<input type='submit' class='ui2_button' value='"._("Apply Changes")."'/>";
		print "</div>";
		print "\n\t\t\t</form>";
		
		$controls = ob_get_clean();
// 		$harmoni = Harmoni::instance();
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
	function printDelete ( $siteComponent ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		$message = _("Are you sure that you wish to delete this component and all of its children?");
		$url = 	$harmoni->request->quickURL('ui2', 'deleteComponent', array(
					'node' => $siteComponent->getId(),
					'returnNode' => SiteDispatcher::getCurrentNodeId(),
					'returnAction' => $this->action
					));
		
		print "\n\t\t\t\t<tr><td colspan='3'>";
		print "\n\t\t\t\t\t<input type='button' class='ui2_button' onclick='";
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"), 
			$siteComponent->getQualifierId()))
		{
			print 	"if (confirm(\"".$message."\")) {";
			print 		" var url = \"".$url."\"; ";
			print 		"window.location = url.urlDecodeAmpersands(); return false;";
			print 	"} ";
		} else {
			print "alert(\""._('You are not authorized to delete this item.')."\"); return false;";
		}
		print "' value='";
		print _("Delete");
		print "'/>";
		print "\n\t\t\t\t</td></tr>";

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
		
		print "\n\t\t\t\t<tr><td colspan='3'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _("Sub-Menu: ")."\n\t\t\t\t</div>";
		
		if ($siteComponent->subMenuExists()) {
			print _("created");
		} else {
			$parentMenuOrganizer = $siteComponent->getMenuOrganizer();
			
			$harmoni = Harmoni::instance();
			$message = _("Are you sure that you wish to create a submenu?");
			$url = $harmoni->request->quickURL('ui2', 'createSubMenu', array(
						'parent' => $siteComponent->getId(),
						'returnNode' => SiteDispatcher::getCurrentNodeId(),
						'returnAction' => $this->action,
						'direction' => urlencode($parentMenuOrganizer->getDirection())));
			
			print "\n\t\t\t\t\t<button class='ui2_button' onclick='";
			if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), 
				$siteComponent->getQualifierId()))
			{
				print 	"if (confirm(\"".$message."\")) ";
				print 		"window.location = \"".$url."\".urlDecodeAmpersands(); return false;";
			} else {
				print "alert(\""._('You are not authorized to create a submenu.')."\"); return false;";
			}
			print "'>";
			print _("create");
			print "</button>";
			print "\n\t\t\t\t</td></tr>";
		}
		
		// print "\n\t\t\t\t</div>";
	}
	
	/**
	 * Print displayName controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printDisplayName ( $siteComponent ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Title: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		print "<input type='text' size='25' class='ui2_field' ";
		print " name='".RequestContext::name('displayName')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		
		print " value='".$siteComponent->getDisplayName()."'/>";
	//	print "</div>";

		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print the display title controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function printShowDisplayNames ( $siteComponent, $isSite = false ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Titles: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}
		
		// setting default select option
		
		print "\n\t\t\t\t\t<select class='ui2_field' ";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('showDisplayNames')."'>";
		$parent = $siteComponent->getParentComponent();
		
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->showDisplayNames() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
			if ($parent) {
				print " ("._("current").": ";
				if ($parent->showDisplayName() === true) {					
					print _("show");
				} else {
					print _("hide");
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		print "\n\t\t\t\t\t\t<option value='true'";
		if ($siteComponent->showDisplayNames() === true) 
			print " selected='selected'";
		print ">";
		print _("show");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		if ($siteComponent->showDisplayNames() === false)
			print " selected='selected'";
		print ">";	
		print _("hide");			
		print "</option>";		
		print "\n\t\t\t\t\t</select> ";

// 		$parent = $siteComponent->getParentComponent();
// 		if ($parent) {
// 			print "\n<span class='ui2_text'>("._("default").": ";
// 			print (($parent->showDisplayName() === true)?_("show"):_("hide"));
// 			print ")</span>";
// 		}
		

		print "\n\t\t\t\t</td></tr>";
	}

	/**
	 * Print block heading display options
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 6/05/08
	 */

	function printBlockHeadingStyleOptions ( SiteComponent $siteComponent ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Title Style: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
			
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}

		$methods = array(
			'Heading_1' => _('Heading - Biggest'), 
			'Heading_2' => _('Heading - Big'), 
			'Heading_3' => _('Heading - Normal'),
			'Heading_Sidebar' => _('Heading - For Sidebar')
			);
			
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('headingDisplayType')."'>";
			
		foreach ($methods as $method => $display) {	
			print "\n\t\t\t\t\t\t<option value='".$method."'";
			if ($siteComponent->getHeadingDisplayType() === $method) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
		print "\n\t\t\t\t\t</select><br/> ";
		print "\n\t\t\t\t</td></tr>";

	}

	
	/**
	 * Print the history link in view-mode
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/10/08
	 */
	function printShowHistory ( $siteComponent, $isSite = false ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('History: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}
		
		print "\n\t\t\t\t\t<select class='ui2_field' ";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('showHistory')."'>";
		$parent = $siteComponent->getParentComponent();
		
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->showHistorySetting() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
			if ($parent) {
				print " ("._("current").": ";
				if ($parent->showHistorySetting() === true) {					
					print _("show");
				} else {
					print _("hide");
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		print "\n\t\t\t\t\t\t<option value='true'";
		if ($siteComponent->showHistorySetting() === true) 
			print " selected='selected'";
		print ">";
		print _("show");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		if ($siteComponent->showHistorySetting() === false)
			print " selected='selected'";
		print ">";	
		print _("hide");			
		print "</option>";		
		print "\n\t\t\t\t\t</select> ";

		print "\n\t\t\t\t</td></tr>";
	}

	/**
	 * Print the block date display in view-mode
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/10/08
	 */
	function printShowDates ( $siteComponent, $isSite = false ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Dates: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}
	

		$dates = array(
			'none' => _('No dates'), 
			'creation_date' => _('Date created'), 
			'modification_date' => _('Date last modified'),
			'both' => _("Date created and last modified"));
		
		print "\n\t\t\t\t\t<select class='ui2_field' ";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('showDates')."'>";
		$parent = $siteComponent->getParentComponent();
		

		
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->showDatesSetting() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
			if ($parent) {
				print " ("._("current").": ";
			//	printpre ($parent->showDatesSetting());
				foreach ($dates as $date => $display) {	
					if ($parent->showDates() === $date) {					
						print $display;
					}
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		foreach ($dates as $date => $display) {
			print "\n\t\t\t\t\t\t<option value='".$date."'";
			if ($siteComponent->showDatesSetting() === $date) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
			
		print "\n\t\t\t\t\t</select> ";

		// print out default setting
// 		$parent = $siteComponent->getParentComponent();
// 		if ($parent) {
// 			print "\n\t\t\t\t\t<div class='ui2_text'>("._("default").": ";
// 			print $dates[$parent->showDatesSetting()];
// 			print ")</div>";
// 		}	

		print "\n\t\t\t\t</td></tr>";
	}
	
		/**
	 * Print the block attribution setting in view-mode
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/10/08
	 */
	function printShowAttribution ( $siteComponent, $isSite = false ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Attribution: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}

		$attributions = array(
			'none' => _('No attribution'), 
			'creator' => _('Original author'), 
			'last_editor' => _('Last editor'),
			'both' => _('Author and last editor'),
			'all_editors' => _("All contributors"));
		
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('showAttribution')."'>";
		$parent = $siteComponent->getParentComponent();
				
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->showAttributionSetting() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
		//	$parent = $siteComponent->getParentComponent();
			if ($parent) {
				print " ("._("current").": ";	
				foreach ($attributions as $attribution => $display) {
					if ($parent->showAttribution() == $attribution) {					
						print $display;
					}
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		foreach ($attributions as $attribution => $display) {
			print "\n\t\t\t\t\t\t<option value='".$attribution."'";
			if ($siteComponent->showAttributionSetting() === $attribution) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
		print "\n\t\t\t\t\t</select><br/> ";
		
		// print out default setting
// 		$parent = $siteComponent->getParentComponent();
// 		if ($parent) {
// 			print "\n\t\t\t\t\t<span class='ui2_text'>("._("default").": ";
// 			print $attributions[$parent->showAttribution()];
// 			print ")</span>";
// 		}
	
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print the sort method controls for flow organizers
	 * 
	 * @param SiteComponent $siteComponent
	 * @param optional boolean $isSite default false
	 * @return void
	 * @access public
	 * @since 1/10/08
	 */
	public function printSortMethod ( SiteComponent $siteComponent, $isSite = false ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Sort: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}

		$methods = array(
			'custom' => _('Custom'), 
			'title_asc' => _('Title: A-Z'), 
			'title_desc' => _('Title: Z-A'),
			'create_date_asc' => _("Creation Date: Recent Last"),
			'create_date_desc' => _("Creation Date: Recent First"),
			'mod_date_asc' => _("Modification Date: Recent Last"),
			'mod_date_desc' => _("Modification Date: Recent First"));
			
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('sortMethod')."'>";
		$parent = $siteComponent->getParentComponent();
				
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->sortMethodSetting() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
			$parent = $siteComponent->getParentComponent();
			if ($parent) {
				print " ("._("current").": ";
				foreach ($methods as $method => $display) {	
					if ($parent->sortMethod() == $method) {					
						print $display;
					}
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		foreach ($methods as $method => $display) {	
			print "\n\t\t\t\t\t\t<option value='".$method."'";
			if ($siteComponent->sortMethodSetting() === $method) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
		print "\n\t\t\t\t\t</select><br/> ";

		// print out default settings	
// 		$parent = $siteComponent->getParentComponent();
// 		if ($parent) {
// 			print "\n<span class='ui2_text'>("._("default").": ";
// 			print $methods[$parent->sortMethod()];
// 			print ")</span>";
// 		}
		
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print the discussion controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 7/16/07
	 */
	function printCommentSettings ( $siteComponent, $isSite = false ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Comments: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}
		
		
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('commentsEnabled')."'>";
		$parent = $siteComponent->getParentComponent();
		
		// if not site setting (i.e. root node of site), the include default option
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";			
			if ($siteComponent->commentsEnabled() === 'default') {
				print " selected='selected'>";
			} else {
				print ">";
			}
			print _("default");
			if ($parent) {
				print " ("._("current").": ";
				if ($parent->commentsEnabled() === true) {					
					print _("yes");
				} else {
					print _("no");
				}
				print ")";
			}		
			print "</option>";
		}
		
		// other setting select option
		print "\n\t\t\t\t\t\t<option value='true'";
		if ($siteComponent->commentsEnabled() === true) 
			print " selected='selected'";
		print ">";
		print _("yes");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		if ($siteComponent->commentsEnabled() === false)
			print " selected='selected'";
		print ">";	
		print _("no");			
		print "</option>";		
		print "\n\t\t\t\t\t</select> ";
		

		// print out default settings
// 		$parent = $siteComponent->getParentComponent();
// 		if ($parent) {
// 			print "\n<span class='ui2_text'>("._("default").": ";
// 			print (($parent->showComments() === true)?_("yes"):_("no"));
// 			print ")</span>";
// 		}
		
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print description controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function printDescription ( $siteComponent ) {
		print "\n\t\t\t\t<tr><td  class='ui2_settingborder' valign='top'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Description: ')."\n\t\t\t\t</div>";
			
		print "<div style='font-size: smaller; width: 100px; text-align: left;'>"
			._("The description will be included in RSS feeds, title attributes, and other external references to this item.")."</div>";

		print "\n\t\t\t\t</td><td class='ui2_settingborder' colspan='2'>";
		//print "\n\t\t\t\t<table cellpadding='0' cellspacing='0'><tr><td valign='top'>";
		print "<textarea rows='5' cols='25' class='ui2_field'";
		print " name='".RequestContext::name('description')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		
		print ">".$siteComponent->getDescription();
		print "</textarea>";
	//	print "\n\t\t\t\t</td></tr></table>";
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print width controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printWidth ( $siteComponent ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Width: ')."\n\t\t\t\t</div>";
		
		print "\n\t\t\t\t</td><td  class='ui2_settingborder' colspan='2'>";
		print "<input type='text' size='6' class='ui2_field' ";
		print " name='".RequestContext::name('width')."'";
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		print " value='".$siteComponent->getWidth()."'/>";		
		print "\n\t\t\t\t</td></tr>";
		
		print "\n\t\t\t\t<tr><td colspan='2' style='width: 500px;'>";
		print "<span class='ui2_text_smaller'>";
		print _("If desired, enter a width in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc. <strong>Note:</strong> This width is a guideline and is not guarenteed to be enforced. Content will fill the page, using this guideline where possible. Content inside of this container may stretch it beyond the specified width.");
		print "</span>";	
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printRowsColumns ( $siteComponent ) {
		
		$minCells = $siteComponent->getMinNumCells();
		
		// rows
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<span class='ui2_settingtitle'>";
		print "\n\t\t\t\t\t"._('Rows: ')."\n\t\t\t\t</span>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
		print "\n\t\t\t\t\t<select class='ui2_field' name='".RequestContext::name('rows')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		
		print " onchange='updateMinCells(this, this.nextSibling.nextSibling.nextSibling.nextSibling, $minCells);'>";
		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print (($i * $siteComponent->getNumColumns() < $minCells)?" disabled='disabled'":"");
			print ">";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		
		// columns
		print "\n\t\t\t\t<span class='ui2_settingtitle'>";
		print "\n\t\t\t\t\t"._('Columns: ')."\n\t\t\t\t</span>";
		
		print "\n\t\t\t\t\t<select class='ui2_field' name='".RequestContext::name('columns')."'";
		print " onchange='updateMinCells(this.previousSibling.previousSibling.previousSibling.previousSibling, this, $minCells);'>";

		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumColumns())?" selected='selected'":"");
			print (($i * $siteComponent->getNumRows() < $minCells)?" disabled='disabled'":"");
			print ">";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		//print "\n\t\t\t\t</div>";
		print<<<END
				<script type='text/javascript'>
				/* <![CDATA[ */
				
					function updateMinCells(rowsElement, colsElement, minCells) {						
						// update the disabled status of row options
						for (var i = 0; i < rowsElement.childNodes.length; i++) {
							if (rowsElement.childNodes[i].value * colsElement.value < minCells)
								rowsElement.childNodes[i].disabled = true;
							else
								rowsElement.childNodes[i].disabled = false;
						}
						
						// update the disabled status of column options
						for (var i = 0; i < colsElement.childNodes.length; i++) {
							if (colsElement.childNodes[i].value * rowsElement.value < minCells)
								colsElement.childNodes[i].disabled = true;
							else
								colsElement.childNodes[i].disabled = false;
						}
					}
				
				/* ]]> */
				</script>
END;
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print rows/columns controls for a flow organizer
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printFlowRowsColumns ( $siteComponent ) {

		$numRows = $siteComponent->getNumRows();
		$numColumns = $siteComponent->getNumColumns();
		
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print "\n\t\t\t\t\t"._('Layout: ')."\n\t\t\t\t</div>";
		
		print "\n\t\t\t\t</td><td class='ui2_settingborder' colspan='2'>";
		
		// columns setting
		print "\n\t\t\t\t\t<select class='ui2_field' name='".RequestContext::name('columns')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		
		print ">";
		
		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumColumns())?" selected='selected'":"");
			print ">";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		
		print "\n\t\t\t\t<span class='ui2_text'>";
		print "\n\t\t\t\t\t"._('Column(s) ');
		print "\n\t\t\t\t</span>";
		
		
		// rows setting
		print "\n\t\t\t\t\t<select class='ui2_field' name='".RequestContext::name('rows')."'>";
		for ($i = 0; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print ">";
			print (($i == 0)?_("unlimited"):$i);
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t<span class='ui2_text'>";
		print "\n\t\t\t\t\t"._('Row(s)');
		print "\n\t\t\t\t</span>";		
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print direction controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printDirection ( $siteComponent ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print "\n\t\t\t\t\t"._('Flow: ')."\n\t\t\t\t</div>";
		
		print "\n\t\t\t\t</td><td class='ui2_settingborder' colspan='2'>";
		print "\n\t\t\t\t\t<select class='ui2_field'  name='".RequestContext::name('direction')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		}
		
		print ">";
		
		$directions = array(
			"Left-Right/Top-Bottom" => _("Left-Right/Top-Bottom"),
			"Top-Bottom/Left-Right" => _("Top-Bottom/Left-Right"),
			"Right-Left/Top-Bottom" => _("Right-Left/Top-Bottom"),
			"Top-Bottom/Right-Left" => _("Top-Bottom/Right-Left"),
// 			"Left-Right/Bottom-Top" => _("Left-Right/Bottom-Top"),
// 			"Bottom-Top/Left-Right" => _("Bottom-Top/Left-Right"),
// 			"Right-Left/Bottom-Top" => _("Right-Left/Bottom-Top"),
// 			"Bottom-Top/Right-Left" => _("Bottom-Top/Right-Left")
		);
		foreach ($directions as $direction => $label) {
			print "\n\t\t\t\t\t\t<option value='".$direction."'";
			print (($direction == $siteComponent->getDirection())?" selected='selected'":"");
			print ">";
			print $label;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
	// 	print "\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td></tr>";
	}
	
	/**
	 * Print theme controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/20/08
	 */
	function printTheme ( $siteComponent ) {
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print "\n\t\t\t\t\t"._('Theme: ')."\n\t\t\t\t</div>";
		
		print "\n\t\t\t\t<div style='font-size: smaller; text-align: left;'>";
		print _("Current Theme").": ";
		$theme = $siteComponent->getTheme();
		print $theme->getDisplayName();
		print "\n\t\t\t\t</div>";
		
		print "\n\t\t\t\t</td><td class='ui2_settingborder' colspan='2'>";
		print "\n\t\t\t\t\t<button class='ui2_field'  name='".RequestContext::name('theme')."'";
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print " readonly='readonly'";
		} else {
			$url = $harmoni->request->quickURL('ui1', 'editSite', array(
						'node' => $siteComponent->getId(),
						'returnNode' => SiteDispatcher::getCurrentNodeId(),
						'returnModule' => $this->module,
						'returnAction' => $this->action,
						'wizardSkipToStep' => "theme"));
			print " onclick='";
			print 		"window.location = \"".$url."\".urlDecodeAmpersands(); return false;";
			print "'";
		}
		print ">";
		print _("Choose Theme");
		print "</button>";
		print "\n\t\t\t\t</td></tr>";
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
			print "\n\t\t\t\t<div class='ui2_settingtitle'>";
			print "\n\t\t\t\t\t"._('Theme Options: ')."\n\t\t\t\t</div>";
			
			print "\n\t\t\t\t<div style='font-size: smaller; text-align: left;'>";
			print _("Change the colors, fonts, etcetera, for this theme.")."";
			print "\n\t\t\t\t</div>";
			
			print "\n\t\t\t\t</td><td class='ui2_settingborder' colspan='2'>";
			print "\n\t\t\t\t\t<button class='ui2_field'  name='".RequestContext::name('theme')."'";
			
			$authZ = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			$harmoni = Harmoni::instance();
			$url = $harmoni->request->quickURL('ui1', 'theme_options', array(
						'node' => $siteComponent->getId(),
						'returnNode' => SiteDispatcher::getCurrentNodeId(),
						'returnModule' => $this->module,
						'returnAction' => $this->action));
			print " onclick='";
			print 		"window.location = \"".$url."\".urlDecodeAmpersands(); return false;";
			print "'";
			print ">";
			print _("Change Theme Options");
			print "</button>";
			print "\n\t\t\t\t</td></tr>";
		}
	}

	/**
	 * Print menu style options
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 6/04/08
	 */

	function printMenuStyleOptions ( SiteComponent $siteComponent ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Menu Style: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
			
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}

		$methods = array(
			'Menu_Left' => _('Left side menu'), 
			'Menu_Right' => _('Right side menu'), 
			'Menu_Top' => _('Top menu'),
			'Menu_Bottom' => _('Bottom menu'));
			
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('displayType')."'>";

		foreach ($methods as $method => $display) {	
			print "\n\t\t\t\t\t\t<option value='".$method."'";
			if ($siteComponent->getDisplayType() === $method) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
		print "\n\t\t\t\t\t</select><br/> ";
		print "\n\t\t\t\t</td></tr>";

	}
	
	/**
	 * Print block style options
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 6/04/08
	 */

	function printBlockStyleOptions ( SiteComponent $siteComponent ) {
	
		print "\n\t\t\t\t<tr><td class='ui2_settingborder'>";
		print "\n\t\t\t\t<div class='ui2_settingtitle'>";
		print _('Block Style: ')."\n\t\t\t\t</div>";
		print "\n\t\t\t\t</td><td class='ui2_settingborder'>";
			
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteComponent->getQualifierId()))
		{
			$canEdit = true;
		} else {
			$canEdit = false;
		}

		$methods = array(
			'Block_Standard' => _('Standard Block'), 
			'Block_Sidebar' => _('Sidebar Block'), 
			'Block_Alert' => _('Alert Block'),
			'Header' => _('Header'),
			'Footer' => _('Footer')
			);
			
		print "\n\t\t\t\t\t<select class='ui2_field'";
		print (($canEdit)?"":" disabled='disabled'");
		print " name='".RequestContext::name('displayType')."'>";
			
		foreach ($methods as $method => $display) {	
			print "\n\t\t\t\t\t\t<option value='".$method."'";
			if ($siteComponent->getDisplayType() === $method) 
				print " selected='selected'";		
			print ">";
			print $display;
			print "</option>";		
		
		}
		print "\n\t\t\t\t\t</select><br/> ";
		print "\n\t\t\t\t</td></tr>";

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
		$this->printDisplayName($siteComponent);
		$this->printShowDisplayNames($siteComponent);
		$this->printBlockHeadingStyleOptions($siteComponent);
		$this->printBlockStyleOptions($siteComponent);
		$this->printShowHistory($siteComponent);
		$this->printCommentSettings($siteComponent);		
		$this->printShowDates($siteComponent);
		$this->printShowAttribution($siteComponent);
// 		$this->printDescription($siteComponent);
// 		$this->printWidth($siteComponent);
		

		$this->printDelete($siteComponent);
		
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
		$this->printDisplayName($siteComponent);
		$this->printDescription($siteComponent);
		$this->printShowDisplayNames($siteComponent);		
		$this->printShowHistory($siteComponent);
		$this->printCommentSettings($siteComponent);
		$this->printShowDates($siteComponent);
		$this->printShowAttribution($siteComponent);
		$this->printSortMethod($siteComponent);
// 		$this->printAddSubMenu($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->controlsStart($siteComponent);
		$this->printDisplayName($siteComponent);			
		$this->printShowDisplayNames($siteComponent, true);	
		$this->printDescription($siteComponent);
		$this->printShowDates($siteComponent, true);
		$this->printSortMethod($siteComponent, true);	
		$this->printShowAttribution($siteComponent, true);			
		$this->printShowHistory($siteComponent, true);
		$this->printCommentSettings($siteComponent, true);

		$this->printWidth($siteComponent);
		
		$this->printTheme($siteComponent);
		
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
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printShowHistory($siteComponent);
		$this->printCommentSettings($siteComponent);
		$this->printShowDates($siteComponent);
		$this->printShowAttribution($siteComponent);
		$this->printSortMethod($siteComponent);
		$this->printDirection($siteComponent);
		$this->printFlowRowsColumns($siteComponent);
		$this->printWidth($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
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
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printShowHistory($siteComponent);
		$this->printCommentSettings($siteComponent);
		$this->printShowDates($siteComponent);
		$this->printShowAttribution($siteComponent);
		$this->printSortMethod($siteComponent);
		$this->printDirection($siteComponent);
		$this->printMenuStyleOptions($siteComponent);	
		$this->printWidth($siteComponent);
		
// 		if (!$siteComponent->isRootMenu())
// 			$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
}

?>