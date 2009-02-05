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
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		
		$nodeType = explode("::", HarmoniType::typeToString($node->getContentType()));
		
		if ($nodeType[2] != "NavBlock" && $this->_showRootNode == FALSE) {
		$url = "<a href='".SiteDispatcher::quickUrl('view','html',
				array('node' => $node->getId()))."'";
		$url .= " onclick=\"if (window.opener) { window.opener.location = this.href;";
		$url .=	"return false; }\" title='"._("View this node")."'>".$node->getDisplayName()."</a>";				
				
		} else {
			$url = "<a href='".SiteDispatcher::quickUrl("participation","actions",
				array('node' => $node->getId()))."'>".$node->getDisplayName()."</a>";			
		}
		
		$this->_links[] = $url;
		$harmoni->request->endNamespace();
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
		if ($this->_showRootNode == TRUE) $this->addLink($siteNavBlock);
		
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