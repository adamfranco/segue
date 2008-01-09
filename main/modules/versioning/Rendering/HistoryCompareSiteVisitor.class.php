<?php
/**
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistoryCompareSiteVisitor.class.php,v 1.3 2008/01/09 17:28:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/HistorySiteVisitor.class.php");

/**
 * Rendering visitor for displaying the differences between two versions.
 * 
 * @since 1/7/08
 * @package segue.modules.versioning
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistoryCompareSiteVisitor.class.php,v 1.3 2008/01/09 17:28:18 adamfranco Exp $
 */
class HistoryCompareSiteVisitor
	extends HistorySiteVisitor
{	
	
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
		
		$earlyVersion = $plugin->getVersion(RequestContext::value('early_rev'));
		$lateVersion = $plugin->getVersion(RequestContext::value('late_rev'));
		
		ob_start();
// 		print "\n<h3 class='diff_title'>"._("Selected Versions")."</h3>";
		print "\n<a href='";
		$browseHistoryUrl = $harmoni->request->quickURL(
			$harmoni->request->getRequestedModule(), 'view_history', 
			array('node' => RequestContext::value('node'), 
				'early_rev' => RequestContext::value('early_rev'),
				'late_rev' => RequestContext::value('late_rev')));
		print $browseHistoryUrl;
		$harmoni->history->markReturnUrl('revert_'.$block->getId(), $browseHistoryUrl);
		print "'>";
		print "\n<input type='button' value='"._('&laquo; Choose Versions')."'/>";
		print "</a>";
		print "\n<table class='version_compare'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>";
		print $this->getHeadingBlock($earlyVersion);
		print "\n\t\t\t</th>";
		print "\n\t\t\t<th>";
		print $this->getHeadingBlock($lateVersion);
		print "\n\t\t\t</th>";
		print "\n\t\t</tr>";
		print "\n\t</thead>";
		print "\n\t<tbody>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<td>";
		print $earlyVersion->getMarkup();
		print "\n\t\t\t</td>";
		print "\n\t\t\t<td>";
		print $lateVersion->getMarkup();
		print "\n\t\t\t</td>";
		print "\n\t\t</tr>";
		print "\n\t</tbody>";
		print "\n</table>";
		
		print "\n<h3 class='diff_title'>"._("Changes")."</h3>";
		print $plugin->getVersionDiff($earlyVersion->getVersionXml(), $lateVersion->getVersionXml());
		return ob_get_clean();
	}
	
	/**
	 * Answer the heading block for a version
	 * 
	 * @param object SeguePluginVersion $version
	 * @return string
	 * @access private
	 * @since 1/9/08
	 */
	private function getHeadingBlock (SeguePluginVersion $version) {
		$harmoni = Harmoni::instance();
		ob_start();
		$headingText = _("Revision %1 <br/>%2 <br/>(%3)");
		$heading = str_replace('%1', $version->getNumber(), $headingText);
		$heading = str_replace('%2', $version->getTimestamp()->ymdString()." ".$version->getTimestamp()->asTime()->string12(), $heading);
		$heading = str_replace('%3', htmlspecialchars($version->getAgent()->getDisplayName()), $heading);
		print "\n\t\t\t\t<div style='float: left;'>";
		print $heading;
		print "</div>";
		print "\n\t\t\t\t<div style='float: right;'>";
		if ($version->isCurrent()) {
			print "\n\t\t\t\t\t<input type='button' value='"._("Current Version")."'";
			print " disabled='disabled'/>";
// 			print _("(Current Version)");
		} else {
			print "\n\t\t\t\t\t<input type='button' value='"._("Revert to this Version")."'";
			print " onclick=\"";
			print "if (confirm('"._("Are you sure that you wish to revert to this version?")."')) { ";
			print 		"var commentText = window.prompt('"._("Why are you reverting to this revision?")."'); ";
			print 		"var url = Harmoni.quickUrl('versioning', 'revert', ";
			print 			"{node_id:'".$version->getPluginInstance()->getId()."', ";
			print 			"version_id:'".$version->getVersionId()."', ";
			print 			"comment:escape(commentText)}); ";
			print 		"window.location = url; ";
			print "} else { return false; }";
			print "\"/>";
		}
		print "\n\t\t\t\t</div>";
		
		if ($version->getComment()) {
			print "\n\t\t\t\t<div class='version_comment' style='clear: both;'>";
			print '"'.htmlspecialchars($version->getComment()).'"';
			print "</div>";
		}
		
		return ob_get_clean();
	}

}