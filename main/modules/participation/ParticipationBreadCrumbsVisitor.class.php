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
 
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");

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
class ParticipationBreadCrumbsVisitor 
	extends BreadCrumbsVisitor
{

	/**
	 * Constructor
	 * 
	 * @param object SiteComponent $currentSiteComponent
	 * @return void
	 * @access public
	 * @since 5/31/07
	 */
	public function __construct (SiteComponent $currentSiteComponent, $showRootNode = FALSE) {
		
		$this->_links = array();
		$this->_separator = " &raquo; ";
		$this->currentSiteComponent = $currentSiteComponent;
		
		$this->allowedModules = array('view', 'ui1', 'ui2');
		$this->defaultModule = 'view';
		$this->defaultAction = 'html';
		$this->_showRootNode = $showRootNode;
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
		$this->_links[] = "<a href='".SiteDispatcher::quickUrl("participation","actions",
				array('node' => $node->getId()))."'>".$node->getDisplayName()."</a>";	
	}
	
	/**
	 * Add a link to the opener window
	 * 
	 * @param object SiteComponent $node
	 * @return void
	 * @access protected
	 * @since 2/6/09
	 */
	protected function addOpenerLink (SiteComponent $node) {
		$url = "<a href='".SiteDispatcher::quickUrl('view','html',
				array('node' => $node->getId()))."'";
		$url .= " onclick=\"if (window.opener) { window.opener.location = this.href;";
		$url .=	"return false; }\" title='"._("View this node")."'>".$node->getDisplayName()."</a>";
		$this->_links[] = $url;
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
		$this->addOpenerLink($block);
		
		$parent = $block->getParentComponent();
		return $parent->acceptVisitor($this);
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
		$this->addLink($navBlock);
		
		$parent = $navBlock->getParentComponent();
		return $parent->acceptVisitor($this);
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
		if ($this->_showRootNode) {
			$this->addLink($siteNavBlock);
		}
		
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

	
}

?>