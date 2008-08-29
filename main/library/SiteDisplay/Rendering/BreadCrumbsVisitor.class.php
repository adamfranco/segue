<?php
/**
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.9 2008/04/10 17:42:39 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/SiteVisitor.interface.php");
require_once(MYDIR."/main/modules/rss/RssLinkPrinter.class.php");

/**
 * Return a bread-crumbs string
 * 
 * @since 5/31/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BreadCrumbsVisitor.class.php,v 1.9 2008/04/10 17:42:39 adamfranco Exp $
 */
class BreadCrumbsVisitor 
	implements SiteVisitor
{
	
	/**
	 * @var object SiteComponent $currentSiteComponent;  
	 * @access private
	 * @since 3/11/08
	 */
	private $currentSiteComponent;

	/**
	 * Constructor
	 * 
	 * @param object SiteComponent $currentSiteComponent
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function BreadCrumbsVisitor (SiteComponent $currentSiteComponent) {
		
		$this->_links = array();
		$this->_separator = " &raquo; ";
		$this->currentSiteComponent = $currentSiteComponent;
		
		$this->allowedModules = array('view', 'ui1', 'ui2');
		$this->defaultModule = 'view';
		$this->defaultAction = 'html';
	}
	
	/**
	 * Add a link for a node
	 * 
	 * @param object SiteComponent $node
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	function addLink ( $node ) {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$this->_links[] = "<a href='"
							.SiteDispatcher::quickUrl(
								$this->getModule(),
								$this->getAction(),
								array('node' => $node->getId()))
							."'>".$node->getDisplayName()."</a>";
		$harmoni->request->endNamespace();
	}
	
	/**
	 * Answer the module to use in the links
	 * 
	 * @return string
	 * @access private
	 * @since 4/10/08
	 */
	private function getModule () {
		$harmoni = Harmoni::instance();
		if (in_array($harmoni->request->getRequestedModule(), $this->allowedModules))
			return $harmoni->request->getRequestedModule();
		else
			return $this->defaultModule;
	}
	
	/**
	 * Answer the action to use in the links
	 * 
	 * @return string
	 * @access private
	 * @since 4/10/08
	 */
	private function getAction () {
		$harmoni = Harmoni::instance();
		if (in_array($harmoni->request->getRequestedModule(), $this->allowedModules))
			return $harmoni->request->getRequestedAction();
		else
			return $this->defaultAction;
	}
		
	/**
	 * Visit a block 
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitBlock ( BlockSiteComponent $block ) {
		$this->addLink($block);
		
		$parent = $block->getParentComponent();
		return $parent->acceptVisitor($this);
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
	 * Visit a nav block
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {		
		return $this->visitBlock($navBlock);
	}
	
	/**
	 * Visit a SiteNavBlock
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		$this->addLink($siteNavBlock);
		
		ob_start();
		$links = array_reverse($this->_links);
		for ($i = 0; $i < count($links); $i++) {
			print "\n<span style='white-space: nowrap;'>";
			if ($i > 0)
				print $this->_separator;
			print $links[$i];
			print "</span>";
		}
		return ob_get_clean();
	}

	/**
	 * Visit a fixed organizer
	 *
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {		
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a fixed organizer 
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a menu organizer
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		$parent = $organizer->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
}

?>