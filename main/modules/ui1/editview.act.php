<?php
/**
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.2 2007/05/21 20:09:00 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/Rendering/EditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/view.act.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.2 2007/05/21 20:09:00 adamfranco Exp $
 */
class editviewAction
	extends viewAction {
	
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 4/6/06
	 */
	function &getSiteVisitor () {
		if (!isset($this->visitor)) 
			$this->visitor =& new EditModeSiteVisitor();
		return $this->visitor;
	}
	
	/**
	 * Execute the action
	 * 
	 * @return object
	 * @access public
	 * @since 1/18/07
	 */
	function &execute () {
		$mainScreen =& parent::execute();
		
		// Add controls bar and border
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		$siteId =& $this->rootSiteComponent->getQualifierId();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$siteId))
		{
			ob_start();
			$harmoni =& Harmoni::instance();
			
			print "\n<a href='".$harmoni->request->quickURL("ui1", "editSite", 
				array("node" => $siteId->getIdString()))."'>";
			print "\n\t<input type='button' value='"._("Edit Site Settings")."'/>";
			print "\n</a>";
			
			$mainScreen->add(new UnstyledBlock(ob_get_clean()), null, null, RIGHT, BOTTOM);
		}
		
		return $mainScreen;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 1/12/07
	 */
	function &getCommandsComponent () {
		$harmoni =& Harmoni::instance();
		
		ob_start();
		print "<a href='";
		print $harmoni->request->quickURL('ui1', 'view', array(
				'node' => RequestContext::value("node")));
		print "' title='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | "._("edit");
				
		$ret =& new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>