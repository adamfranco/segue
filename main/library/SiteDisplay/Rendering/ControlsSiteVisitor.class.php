<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.2 2006/04/17 18:09:37 adamfranco Exp $
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
 * @version $Id: ControlsSiteVisitor.class.php,v 1.2 2006/04/17 18:09:37 adamfranco Exp $
 */
class ControlsSiteVisitor {
		
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
					"returnNode" => RequestContext::value('node')));
		print "'>";
		
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
					'returnNode' => RequestContext::value('node')
					)));
		
		print "\n\t\t\t\t<div>";
		print "\n\t\t\t\t\t<a href='Javascript:";
		print 	"if (confirm(\"".$message."\")) ";
		print 		"window.location = \"".$url."\";";
		print "'>";
		print _("delete");
		print "</a>";
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
		print "\n\t\t\t\t<div style='white-space: nowrap;'>";
		print _('Title: ');
		print "<input type='text' size='10' value='".$siteComponent->getDisplayName()."'/>";
		print "</div>";
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
		print "\n\t\t\t\t<div style='white-space: nowrap;'>";
		print "\n\t\t\t\t\t"._('Rows: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('rows')."'>";
		for ($i = 0; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumRows())?" selected='selected'":"");
			print "/>";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t\t<br/>"._('Columns: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('columns')."'>";
		for ($i = 0; $i <= 10; $i++) {
			print "\n\t\t\t\t\t\t<option value='".$i."'";
			print (($i == $siteComponent->getNumColumns())?" selected='selected'":"");
			print "/>";
			print $i;
			print "</option>";
		}
		print "\n\t\t\t\t\t</select>";
		print "\n\t\t\t\t</div>";
	}
	
	/**
	 * Print rows/columns controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printDirection ( &$siteComponent ) {
		print "\n\t\t\t\t<div style='white-space: nowrap;'>";
		print "\n\t\t\t\t\t"._('Index Direction: ');
		print "\n\t\t\t\t\t<select name='".RequestContext::name('direction')."'>";
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
		
		$this->printDisplayName($siteComponent);
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
		
		$this->printDisplayName($siteComponent);
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
		$this->printDirection($siteComponent);
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
		$this->printDirection($siteComponent);
		
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
		
		$this->printDirection($siteComponent);
		$this->printDelete($siteComponent);
		
		return $this->controlsEnd($siteComponent);
	}
	
}

?>