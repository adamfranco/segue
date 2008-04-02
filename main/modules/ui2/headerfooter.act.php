<?php
/**
 * @since 3/25/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: headerfooter.act.php,v 1.5 2008/04/02 21:15:22 achapin Exp $
 */ 
require_once(dirname(__FILE__)."/../view/html.act.php");
require_once(dirname(__FILE__)."/Rendering/DetailEditHeaderFooterSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/EditHeaderFooterSiteVisitor.class.php");

/**
 * An action for editing the header and footer in UI2
 * 
 * @since 3/25/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: headerfooter.act.php,v 1.5 2008/04/02 21:15:22 achapin Exp $
 */
class headerFooterAction 
	extends htmlAction
{
		
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function getSiteVisitor () {
		if (!isset($this->visitor)) {
			
			$requestedNode = SiteDispatcher::getCurrentNode();
			
			if ($requestedNode->acceptVisitor(new IsBlockVisitor))
				$this->visitor = new DetailEditHeaderFooterSiteVisitor($requestedNode);
			else
				$this->visitor = new EditHeaderFooterSiteVisitor();
		}
		return $this->visitor;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function getCommandsComponent () {
		$harmoni = Harmoni::instance();
		//printpre("module: ".$_SESSION['UI_MODULE']);
		ob_start();
		print "<div class='commands'>";
		
		print "<a href='";
		$url = $harmoni->request->mkURLWithPassthrough('view', 'html');
		print $url->write();
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | <a href='";
		$url = $harmoni->request->mkURLWithPassthrough('ui2', 'editview');
		print $url->write();
		print "' title='"._("Go to Edit-Mode")."'>";
		print _("edit")."</a>";
		
		print " | "._("header/footer");
		
		print " | <a href='";
		$url = $harmoni->request->mkURLWithPassthrough('ui2', 'arrangeview');
		print $url->write();
		print "' title='"._("Go to Arrange-Mode")."'>";
		print _("arrange")."</a>";
		print " | ".self::getUiSwitchForm();
		print "</div>";
				
		$ret = new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
	
}

?>