<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.9 2007/01/17 21:21:57 adamfranco Exp $
 */ 

/**
 * Returns the controls strings for each component type
 * 
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.9 2007/01/17 21:21:57 adamfranco Exp $
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
		
		print "\n\t\t\t<form method='post'";
		print " action='";
		print $harmoni->request->quickURL('site', 'modifyComponent',
				array('node' => $siteComponent->getId(),
					"returnNode" => RequestContext::value('node'),
					'returnAction' => $this->_action));
		print "'";
		print " class='controls_form'";
		print ">";
		
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
		print "\n\t\t\t\t<div style='text-align: right;'>";
		print "<input type='submit' value='"._("Apply Changes")."'/>";
		print "</div>";
		print "\n\t\t\t</form>";
		
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
		$harmoni =& Harmoni::instance();
		$message = _("Are you sure that you wish to delete this component and all of its children?");
		$url = str_replace('&amp;', '&', 
				$harmoni->request->quickURL('site', 'deleteComponent', array(
					'node' => $siteComponent->getId(),
					'returnNode' => RequestContext::value('node'),
					'returnAction' => $this->_action
					)));
		
		print "\n\t\t\t\t<div style='margin-top: 5px; margin-bottom: 5px;'>";
		print "\n\t\t\t\t\t<button onclick='";
		print 	"if (confirm(\"".$message."\")) ";
		print 		"window.location = \"".$url."\";";
		print "'>";
		print _("delete");
		print "</button>";
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
	function printAddSubMenu ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='font-weight: bold;'>";
		print _("Sub-Menu: ");
		
		if ($siteComponent->subMenuExists()) {
			print _("created");
		} else {
			$parentMenuOrganizer =& $siteComponent->getMenuOrganizer();
			
			$harmoni =& Harmoni::instance();
			$message = _("Are you sure that you wish to create a submenu?");
			$url = str_replace('&amp;', '&', 
					$harmoni->request->quickURL('site', 'createSubMenu', array(
						'parent' => $siteComponent->getId(),
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->_action,
						'direction' => urlencode($parentMenuOrganizer->getDirection()))));
			
			print "\n\t\t\t\t\t<a href='Javascript:";
			print 	"if (confirm(\"".$message."\")) ";
			print 		"window.location = \"".$url."\";";
			print "'>";
			print _("create");
			print "</a>";
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
	function printDisplayName ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='white-space: nowrap; font-weight: bold;'>";
		print _('Title: ');
		print "<input type='text' size='25' ";
		print " name='".RequestContext::name('displayName')."'";
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
	function printShowDisplayNames ( &$siteComponent ) {
		print "\n\t\t\t\t<table cellspacing='0' cellpadding='0'>\n\t\t\t\t\t<tr><td style='white-space: nowrap;' valign='top' rowspan='3'>";
		print "<strong>"._('Display Block Titles: ')."</strong>";
		
		print "</td>\n\t\t\t\t\t<td>";
		print " <input type='radio' ";
		print " name='".RequestContext::name('showDisplayNames')."'";
		print " value='default'";
		print (($siteComponent->showDisplayNames() == 'default')?" checked='checked'":"");
		print "'/>"._(" use default");
		
		print "</td></tr>\n\t\t\t\t\t<tr><td>";
		print " <input type='radio' ";
		print " name='".RequestContext::name('showDisplayNames')."'";
		print " value='true'";
		print (($siteComponent->showDisplayNames() === true)?" checked='checked'":"");
		print "'/>"._("override-yes");
		
		print "</td></tr>\n\t\t\t\t\t<tr><td>";
		print " <input type='radio' ";
		print " name='".RequestContext::name('showDisplayNames')."'";
		print " value='false'";
		print (($siteComponent->showDisplayNames() === false)?" checked='checked'":"");
		print "'/>"._("override-no");
		
		print "</td></tr>";
		print "\n\t\t\t\t</table>";
	}
	
	/**
	 * Print description controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 1/16/07
	 */
	function printDescription ( &$siteComponent ) {
		print "\n\t\t\t\t<table cellpadding='0' cellspacing='0'><tr><td valign='top'>";
		print "<div style='font-weight: bold;'>"._('Description: ')."</div>";
		print "<div style='font-size: smaller; width: 125px;'>"
			._("The description will be included in RSS feeds, title attributes, and other external references to this item.")."</div>";
		print "\n\t\t\t\t\t</td><td valign='top'><textarea rows='5' cols='25'";
		print " name='".RequestContext::name('description')."'";
		print " value='".$siteComponent->getDescription()."'/>";
		print "</textarea>";
		print "\n\t\t\t\t</td></tr></table>";
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printRowsColumns ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='white-space: nowrap; font-weight: bold;'>";
		$minCells = $siteComponent->getMinNumCells();
		print "\n\t\t\t\t\t"._('Rows: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('rows')."'";
		print " onchange='updateMinCells(this, this.nextSibling.nextSibling.nextSibling.nextSibling, $minCells);'>";
		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print (($i * $siteComponent->getNumColumns() < $minCells)?" disabled='disabled'":"");
			print "/>";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t\t<br/>"._('Columns: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('columns')."'";
		print " onchange='updateMinCells(this.previousSibling.previousSibling.previousSibling.previousSibling, this, $minCells);'>";

		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumColumns())?" selected='selected'":"");
			print (($i * $siteComponent->getNumRows() < $minCells)?" disabled='disabled'":"");
			print "/>";
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
	function printFlowRowsColumns ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='white-space: nowrap; font-weight: bold;'>";
		$numRows = $siteComponent->getNumRows();
		$numColumns = $siteComponent->getNumColumns();
		print "\n\t\t\t\t\t"._('Columns: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('columns')."'>";

		for ($i = 1; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumColumns())?" selected='selected'":"");
			print "/>";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t\t<br/>"._('Rows: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('rows')."'>";
		for ($i = 0; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print "/>";
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
	function printDirection ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='white-space: nowrap; font-weight: bold;'>";
		print "\n\t\t\t\t\t"._('Index Direction: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('direction')."'>";
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
			print "/>";
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
	function &visitBlock ( &$siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printDisplayName($siteComponent);
		$this->printDescription($siteComponent);
		$this->printDelete($siteComponent);
		
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
		
		$this->printShowDisplayNames($siteComponent);
		$this->printDisplayName($siteComponent);		
		$this->printDescription($siteComponent);
		$this->printAddSubMenu($siteComponent);
		$this->printDelete($siteComponent);
		
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
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printFlowRowsColumns($siteComponent);
		$this->printDirection($siteComponent);
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
	function &visitMenuOrganizer ( &$siteComponent ) {
		$this->controlsStart($siteComponent);
		
		$this->printShowDisplayNames($siteComponent);
		$this->printDirection($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
}

?>