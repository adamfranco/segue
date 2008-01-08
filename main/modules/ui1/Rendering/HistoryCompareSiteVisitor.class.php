<?php
/**
 * @since 1/7/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistoryCompareSiteVisitor.class.php,v 1.1 2008/01/08 16:22:56 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/HistorySiteVisitor.class.php");

/**
 * Rendering visitor for displaying the differences between two versions.
 * 
 * @since 1/7/08
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HistoryCompareSiteVisitor.class.php,v 1.1 2008/01/08 16:22:56 adamfranco Exp $
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
		print $harmoni->request->quickURL(
			$harmoni->request->getRequestedModule(), 'view_history', 
			array('node' => RequestContext::value('node'), 
				'early_rev' => RequestContext::value('early_rev'),
				'late_rev' => RequestContext::value('late_rev')));
		print "'>";
		print "\n<input type='button' value='"._('&laquo; Choose Versions')."'/>";
		print "</a>";
		print "\n<table class='version_compare'>";
		print "\n\t<thead>";
		print "\n\t\t<tr>";
		print "\n\t\t\t<th>";
		$headingText = _("Revision %1 <br/>(%2 - %3)");
		$heading = str_replace('%1', $earlyVersion->getNumber(), $headingText);
		$heading = str_replace('%2', $earlyVersion->getTimestamp()->ymdString()." ".$earlyVersion->getTimestamp()->asTime()->string12(), $heading);
		$heading = str_replace('%3', $earlyVersion->getAgent()->getDisplayName(), $heading);
		print $heading;
		print "\n\t\t\t</th>";
		print "\n\t\t\t<th>";
		$heading = str_replace('%1', $lateVersion->getNumber(), $headingText);
		$heading = str_replace('%2', $lateVersion->getTimestamp()->ymdString()." ".$lateVersion->getTimestamp()->asTime()->string12(), $heading);
		$heading = str_replace('%3', $lateVersion->getAgent()->getDisplayName(), $heading);
		print $heading;
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

}