<?php
/**
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistorySiteVisitor.class.php,v 1.6 2008/04/09 21:12:03 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/view/DetailViewModeSiteVisitor.class.php");

/**
 * Rendering visitor for the history browser.
 * 
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistorySiteVisitor.class.php,v 1.6 2008/04/09 21:12:03 adamfranco Exp $
 */
class HistorySiteVisitor
	extends DetailViewModeSiteVisitor
{
		
	/**
	 * Answer the title of a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	function getBlockTitle ( $block ) {
		if ($block->getId() == $this->_node->getId())
			return $block->getDisplayName()." &raquo; "._("History");
		else
			return parent::getBlockTitle($block);
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 1/7/08
	 */
	function visitTargetBlock () {
		$block = $this->_node;
		$guiContainer = parent::visitBlock($block);		
		return $guiContainer;
	}
	
	
	/**
	 * Answer the plugin content for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	function getPluginContent ( $block ) {
		if ($block->getId() != $this->_node->getId())
			return parent::getPluginContent($block);
		
		$harmoni = Harmoni::instance();
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($block->getAsset());
		
		ob_start();
		print "\n<form action='".SiteDispatcher::quickURL('versioning', 'compare_versions', array('node' => SiteDispatcher::getCurrentNodeId()))."' method='get'>";
		print "\n\t<div style='float: right;'>";
		print "\n\t<input type='submit' value='"._("Compare Selected Revisions &raquo;")."'";
		if (count($plugin->getVersions()) <= 1) {
			print " disabled='disabled'";
		}
		print "/>";
		print "\n\t</div>";
		print "\n\t<div style='float: left;'>\n\t\t<a href='".$harmoni->history->getReturnURL('view_history_'.$block->getId())."'>";
		print "\n\t\t\t<input type='button' value='"._("&laquo; Go Back")."'/>\n\t\t</a>\n\t</div>";
		print "\n\t\t<input type='hidden' name='module' value='versioning'/>";
		print "\n\t<input type='hidden' name='action' value='compare_versions'/>";
		print "\n\t<input type='hidden' name='node' value='".SiteDispatcher::getCurrentNodeId()."'/>";
		
		print $this->getVersionTable($plugin);
		print "\n</form>";
		print $this->getVersionChoiceJS();
		
		return ob_get_clean();
	}
	
	/**
	 * Answer a table of available versions.
	 * 
	 * @param object SeguePluginsDriverAPI $plugin
	 * @return string XHTML markup
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersionTable (SeguePluginsDriverAPI $plugin) {
		ob_start();
		print "\n<table class='history_list'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th colspan='2'>"._("Select")."</th>";
		print "\n\t\t\t<th>"._("Revision")."</th>";
		print "\n\t\t\t<th>"._("Revision Date")."</th>";
		print "\n\t\t\t<th>"._("Revision Author")."</th>";
		print "\n\t\t\t<th>"._("Revision Comment")."</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		
		$versions = $plugin->getVersions();
		$numVersions = count($versions);
		$i = $numVersions;
		
		if (RequestContext::value('early_rev')) {
			$earlyVersion = $plugin->getVersion(RequestContext::value('early_rev'));
			$early = $earlyVersion->getNumber();
		}
		
		if (!isset($early))
			$early = $numVersions - 1;
			
		if (RequestContext::value('late_rev')) {
			$lateVersion = $plugin->getVersion(RequestContext::value('late_rev'));
			$late = $lateVersion->getNumber();
		}
		
		if (!isset($late))
			$late = $numVersions;
		
		
		$colorKey = 0;
		foreach ($versions as $version) {
			print "\n\t\t<tr class='color".$colorKey."'>";
			
			// Selection
			print "\n\t\t\t<td>";
			if ($i < $numVersions) {
				print "\n\t\t\t\t<input type='radio' name='".RequestContext::name('early_rev')."'";
				print " value='".$version->getVersionId()."'";
				print " onclick='updateVersionSelection(this);'";
				if ($i == $early)
					print " checked='checked'";
				if ($i >= $late)
					print " style='visibility: hidden;'";
				print "/>";
			}
			print "\n\t\t\t</td>";
			print "\n\t\t\t<td>";
			if ($i > 1) {
				print "\n\t\t\t\t<input type='radio' name='".RequestContext::name('late_rev')."'";
				print " value='".$version->getVersionId()."'";
				print " onclick='updateVersionSelection(this);'";
				if ($i == $late)
					print " checked='checked'";
				if ($i <= $early)
					print " style='visibility: hidden;'";
				print "/>";
			}
			print "\n\t\t\t</td>";
			
			// Number
			print "\n\t\t\t<td>";
			print str_replace('%1', $i, _("Revision %1"));
			if ($i == $numVersions)
				print " "._("(current)");
			print "</td>";
			
			// Date
			print "\n\t\t\t<td>";
			print $version->getTimestamp()->ymdString();
			$time = $version->getTimestamp()->asTime();
			print " ".$time->string12(false);
			print "\n\t\t\t</td>";
			
			// Author
			print "\n\t\t\t<td>";
			print htmlspecialchars($version->getAgent()->getDisplayName());
			print "\n\t\t\t</td>";
			
			// Comment
			print "\n\t\t\t<td>";
			print htmlspecialchars($version->getComment());
			print "\n\t\t\t</td>";
			
			
			print "\n\t\t</tr>";
			
			$i--;
			$colorKey = intval(!$colorKey);
		}
		
		print "\n\t</tbody>";
		print "\n</table>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the Url for this component id.
	 *
	 * Note: this is clunky that this object has to know about harmoni and 
	 * what action to target. Maybe rewrite...
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 4/4/06
	 */
	function getUrlForComponent ( $id ) {
		$harmoni = Harmoni::instance();
		$origUrl = $harmoni->history->getReturnURL('view_history_'.$this->_node->getId());
		$module = $harmoni->request->getModuleFromUrl($origUrl);
		if ($module == false)
			$module = 'ui1';
		$action = $harmoni->request->getActionFromUrl($origUrl);
		if ($action == false)
			$action = 'view';
		return SiteDispatcher::quickURL(
			$module, 
			$action,
			array("node" => $id));
	}
	
	/**
	 * Answer the javascript for hiding and showing choices.
	 * 
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersionChoiceJS () {
		$oldName = RequestContext::name('early_rev');
		$newName = RequestContext::name('late_rev');
		return <<< END
		
	<script type='text/javascript'>
	// <![CDATA[
	
	if (!Array.prototype.push) {

		/**
		 * IE 5.01 does not implement the push method, so it needs to be
		 * added
		 * 
		 * @param mixed element
		 * @return int
		 * @access public
		 * @since 1/31/07
		 */
		Array.prototype.push = function ( element ) {
			var key = this.length;
			this[key] = element;
			return key;
		}
		
	}
	
	/**
	 * Update the radio buttons checked if needed to preven un-allowed situations:
	 * 		- comparing a version to itself
	 *		- mixing up new and old versions
	 * 
	 * @param object RadioButton button
	 * @return void
	 * @access public
	 * @since 2/2/07
	 */
	function updateVersionSelection (button) {
		var oldButtons = new Array();
		oldButtons.push(null);
		var newButtons = new Array();
		
		for (var i = 0; i < button.form.elements.length; i++) {
			// Sort the elements into old and new arrays and record
			// what row in each is selected
			var element = button.form.elements[i];
			if (element.name == '$oldName') {
				oldButtons.push(element);
				if (element.checked)
					var oldRow = oldButtons.length - 1;
			} else if (element.name == '$newName') {
				newButtons.push(element);
				if (element.checked)
					var newRow = newButtons.length - 1;
			}
		}
						
		// If a new version was selected make sure that the old version is older
		if (button.name == '$newName') {
			if (oldRow <= newRow) {
				oldButtons[oldRow].checked = '';
				oldButtons[newRow + 1].checked = 'checked';
			}
			
			for (var i = 1; i < oldButtons.length; i++) {
				if (i <= newRow)
					oldButtons[i].style.visibility = 'hidden';
				else
					oldButtons[i].style.visibility = 'visible';
			}
		} 
		// If an old version was selected make sure that the new version is newer
		else {
			if (newRow >= oldRow) {
				newButtons[newRow].checked = '';
				newButtons[oldRow - 1].checked = 'checked';
			}
			
			for (var i = 1; i < newButtons.length; i++) {
				if (i >= oldRow)
					newButtons[i].style.visibility = 'hidden';
				else
					newButtons[i].style.visibility = 'visible';
			}
		}
	}
	
	// ]]>
	</script>
		
END;
	}
}

?>