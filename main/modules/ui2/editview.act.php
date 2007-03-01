<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.1 2007/03/01 20:12:59 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/XmlSiteComponents/XmlSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/ViewModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/EditModeSiteVisitor.class.php");
require_once(dirname(__FILE__)."/view.act.php");

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editview.act.php,v 1.1 2007/03/01 20:12:59 adamfranco Exp $
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
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$this->rootSiteComponent->getQualifierId()))
		{
			$visitor =& $this->getSiteVisitor();
			$controlsHTML = $visitor->getBarPreHTML('#090')
				.$visitor->getControlsHTML(
					"<em>"._("Site")."</em>", 
					$this->rootSiteComponent->acceptVisitor($visitor->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, false);
			$mainScreen->setPreHTML($controlsHTML.$mainScreen->getPreHTML($null = null));
			
			$mainScreen->setPostHTML($visitor->getBarPostHTML());
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
		print $harmoni->request->quickURL('ui2', 'view', array(
				'node' => RequestContext::value("node")));
		print "' alt='"._("Go to View-Mode")."'>";
		print _("view")."</a>";
		
		print " | "._("edit");
		
		print " | <a href='";
		print $harmoni->request->quickURL('ui2', 'arrangeview', array(
				'node' => RequestContext::value("node")));
		print "' alt='"._("Go to Arrange-Mode")."'>";
		print _("arrange")."</a>";
				
		$ret =& new Component(ob_get_clean(), BLANK, 2);
		return $ret;
	}
}

?>