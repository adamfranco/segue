<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.19 2008/03/21 21:01:11 achapin Exp $
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
 * @version $Id: ControlsSiteVisitor.class.php,v 1.19 2008/03/21 21:01:11 achapin Exp $
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
					"returnNode" => RequestContext::value('node'),
					'returnAction' => $this->action));
		print "'";
		print " class='controls_form'";
		print ">";
		
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
					'returnNode' => RequestContext::value('node'),
					'returnAction' => $this->action
					));
		
		print "\n\t\t\t\t<div style='margin-top: 5px; margin-bottom: 5px;'>";
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
		print "\n\t\t\t\t</div>";
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
			$url = $harmoni->request->quickURL('ui2', 'createSubMenu', array(
						'parent' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
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
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print _('Title: ');
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
		print "</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Display Block Titles: ')."</strong>";
		
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
		print " name='".RequestContext::name('showDisplayNames')."'>";
		
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->showDisplayNames() === 'default')?" selected='selected'":"");
			print ">"._(" Use default");
			print "</option>";
		}
		
		print "\n\t\t\t\t\t\t<option value='true'";
		print (($siteComponent->showDisplayNames() === true)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("show");
		else
			print _("override-show");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		print (($siteComponent->showDisplayNames() === false)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("hide");
		else
			print _("override-hide");
		print "</option>";
		
		print "\n\t\t\t\t\t</select> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print (($parent->showDisplayName() === true)?_("show"):_("hide"));
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Display History Link: ')."</strong>";
		
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
		
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->showHistorySetting() === 'default')?" selected='selected'":"");
			print ">"._(" Use default");
			print "</option>";
		}
		
		print "\n\t\t\t\t\t\t<option value='true'";
		print (($siteComponent->showHistorySetting() === true)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("show");
		else
			print _("override-show");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		print (($siteComponent->showHistorySetting() === false)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("hide");
		else
			print _("override-hide");
		print "</option>";
		
		print "\n\t\t\t\t\t</select> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print (($parent->showHistory() === true)?_("show"):_("hide"));
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Display Dates: ')."</strong>";
		
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
		print " name='".RequestContext::name('showDates')."'>";
		
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->showDatesSetting() === 'default')?" selected='selected'":"");
			print ">"._(" Use default");
			print "</option>";
		}
		

		$dates = array(
			'none' => _('No dates'), 
			'creation_date' => _('Date created'), 
			'modification_date' => _('Date last modified'),
			'both' => _("Date created and last modified"));
			
		foreach ($dates as $date => $display) {
			print "\n\t\t\t\t\t\t<option value='".$date."'";
			print (($siteComponent->showDatesSetting() == $date)?" selected='selected'":"");
			print ">";
			if ($isSite)
				print $display;
			else
				print _("Override")." - ".$display;
			print "</option>";
		}
		
		print "\n\t\t\t\t\t</select><br/> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print $dates[$parent->showDates()];
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Display Attribution: ')."</strong>";
		
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
		print " name='".RequestContext::name('showAttribution')."'>";
		
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->showAttributionSetting() === 'default')?" selected='selected'":"");
			print ">"._(" Use default");
			print "</option>";
		}
		

		$attributions = array(
			'none' => _('No attribution'), 
			'creator' => _('Original author'), 
			'last_editor' => _('Last editor'),
			'both' => _('Both author and last editor'),
			'all_editors' => _("All editors"));
			
		foreach ($attributions as $attribution => $display) {
			print "\n\t\t\t\t\t\t<option value='".$attribution."'";
			print (($siteComponent->showAttributionSetting() == $attribution)?" selected='selected'":"");
			print ">";
			if ($isSite)
				print $display;
			else
				print _("Override")." - ".$display;
			print "</option>";
		}
		
		print "\n\t\t\t\t\t</select><br/> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print $attributions[$parent->showAttribution()];
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Sort Content: ')."</strong>";
		
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
		print " name='".RequestContext::name('sortMethod')."'>";
		
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->sortMethodSetting() === 'default')?" selected='selected'":"");
			print ">"._("Use default");
			print "</option>";
		}
		
// 		$methods = array(
// 			'custom' => _('Custom'), 
// 			'title_asc' => _('Alphabetic by Title - Ascending'), 
// 			'title_desc' => _('Alphabetic by Title - Descending'),
// 			'create_date_asc' => _("Chronologically by Create Date - Ascending"),
// 			'create_date_desc' => _("Chronologically by Create Date - Descending"),
// 			'mod_date_asc' => _("Chronologically by Modification Date - Ascending"),
// 			'mod_date_desc' => _("Chronologically by Modification Date - Descending"));

		$methods = array(
			'custom' => _('Custom'), 
			'title_asc' => _('Title: A-Z'), 
			'title_desc' => _('Title: Z-A'),
			'create_date_asc' => _("Creation Date: Recent Last"),
			'create_date_desc' => _("Creation Date: Recent First"),
			'mod_date_asc' => _("Modification Date: Recent Last"),
			'mod_date_desc' => _("Modification Date: Recent First"));
			
		foreach ($methods as $method => $display) {
			print "\n\t\t\t\t\t\t<option value='".$method."'";
			print (($siteComponent->sortMethodSetting() == $method)?" selected='selected'":"");
			print ">";
			if ($isSite)
				print $display;
			else
				print _("Override")." - ".$display;
			print "</option>";
		}
		
		print "\n\t\t\t\t\t</select><br/> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print $methods[$parent->sortMethod()];
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "<strong>"._('Enable Comments: ')."</strong>";
		
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
				
		if (!$isSite) {
			print "\n\t\t\t\t\t\t<option value='default'";
			print (($siteComponent->commentsEnabled() === 'default')?" selected='selected'":"");
			print ">"._(" Use default");
			print "</option>";
		}
		
		print "\n\t\t\t\t\t\t<option value='true'";
		print (($siteComponent->commentsEnabled() === true)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("yes");
		else
			print _("override-yes");
		print "</option>";
		
		print "\n\t\t\t\t\t\t<option value='false'";
		print (($siteComponent->commentsEnabled() === false)?" selected='selected'":"");
		print ">";
		if ($isSite)
			print _("no");
		else
			print _("override-no");
		print "</option>";
		
		print "\n\t\t\t\t\t</select> ";
		
		$parent = $siteComponent->getParentComponent();
		if ($parent) {
			print "\n<span class='ui2_text'>("._("default").": ";
			print (($parent->showComments() === true)?_("yes"):_("no"));
			print ")</span>";
		}
		
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<table cellpadding='0' cellspacing='0'><tr><td valign='top'>";
		print "<div style='font-weight: bold;'>"._('Description: ')."</div>";
		print "<div style='font-size: smaller; width: 125px;'>"
			._("The description will be included in RSS feeds, title attributes, and other external references to this item.")."</div>";
		print "\n\t\t\t\t\t</td><td valign='top'><textarea rows='5' cols='25' class='ui2_field'";
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
		print "\n\t\t\t\t</td></tr></table>";
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
		print "<div style='font-weight: bold;'>"._('Maximum Width Guideline: ');
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
		print "</div>";
		print "<div style='font-size: smaller;'>"
			._("If desired, enter a width in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc.<br/><strong>Note:</strong> This width is a guideline and is not guarenteed to be enforced. Content will fill the page, using this guideline where possible. Content inside of this container may stretch it beyond the specified width.")."</div>";		
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		$minCells = $siteComponent->getMinNumCells();
		print "\n\t\t\t\t\t"._('Rows: ');
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
		print "\n\t\t\t\t\t"._('Columns: ');
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
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		$numRows = $siteComponent->getNumRows();
		$numColumns = $siteComponent->getNumColumns();
		print "\n\t\t\t\t\t"._('Columns: ');
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
		print "\n\t\t\t\t\t"._('Rows: ');
		print "\n\t\t\t\t\t<select class='ui2_field' name='".RequestContext::name('rows')."'>";
		for ($i = 0; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print ">";
			print (($i == 0)?_("unlimited"):$i);
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t</div>";
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
		print "\n\t\t\t\t<div class='ui2_setting'>";
		print "\n\t\t\t\t\t"._('Flow Content: ');
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
		$this->printDisplayName($siteComponent);
		$this->printShowDisplayNames($siteComponent);		
// 		$this->printDescription($siteComponent);
// 		$this->printWidth($siteComponent);
		$this->printCommentSettings($siteComponent);
		$this->printShowHistory($siteComponent);
		$this->printShowDates($siteComponent);
		$this->printShowAttribution($siteComponent);
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
		$this->printShowHistory($siteComponent, true);
		$this->printCommentSettings($siteComponent, true);
		$this->printShowDates($siteComponent, true);
		$this->printShowAttribution($siteComponent, true);
		$this->printSortMethod($siteComponent, true);
		$this->printWidth($siteComponent);
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
		$this->printFlowRowsColumns($siteComponent);
		$this->printDirection($siteComponent);
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
		$this->printWidth($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
}

?>