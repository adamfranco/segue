<?php
/**
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyRsslinksPlugin.class.php,v 1.1 2008/03/13 19:02:53 achapin Exp $
 */ 


require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");

/**
 * A simple plugin for displaying links to a site's RSS feeds
 * (this plugin can not be used outside of Segue as it getting information
 * about a given Segue site's context)
 * 
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyRsslinksPlugin.class.php,v 1.1 2008/03/13 19:02:53 achapin Exp $
 */
class EduMiddleburyRsslinksPlugin 
	extends SeguePlugin

{
			

	/**
 	 * Answer a description of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	public static function getPluginDescription () {
 		 return _("The RSS links plugin allows users to add a block with links the current nodes RSS feed"); 	
 	}
 	
 	/**
 	 * Answer a display name for the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginDisplayName () {
 		return _("RSS Links");
 	}
 	
 	/**
 	 * Answer an array of the creators of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return array of strings
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginCreators () {
 		return array("Alex Chapin");
 	}
 	
 	/**
 	 * Answer the version of the plugin.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersion () {
 		return '1.0';
 	}
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function getMarkup () {
		ob_start();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));			
		
		if (!$nodeId = $this->getNodeId())
			throwError(new Error('No site node specified.', 'SiteDisplay'));
		
		$node = $director->getSiteComponentById(
				$this->getNodeId());
		
		$node = $director->getSiteComponentById($nodeId);
		
		$RssLinks = RssLinkPrinter::getLinkBlock($node);
		print "<div class='breadcrumbs'>".$RssLinks."</div>";	
		
		$harmoni->request->endNamespace();
		
		return ob_get_clean();
 	}
 	
	/**
	 * Answer the bread crumbs for the current node
	 * 
	 * @return string
	 * @access public
	 * @since 5/31/07
	 */
	function getBreadCrumbs () {
		$node = $this->_director->getSiteComponentById(
				$this->getId());
		
		return $node->acceptVisitor(new BreadCrumbsVisitor($node));
	}
	
 	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getNodeId () {
		if (RequestContext::value("site")) {
			$slotManager = SlotManager::instance();
			$slot = $slotManager->getSlotByShortname(RequestContext::value("site"));
			if ($slot->siteExists())
				$nodeId = $slot->getSiteId()->getIdString();
			else
				throw new UnknownIdException("A Site has not been created for the slotname '".$slot->getShortname()."'.");
		} else if (RequestContext::value("node")) {
			$nodeId = RequestContext::value("node");
		}
		
		if (!isset($nodeId) || !strlen($nodeId))
			throw new NullArgumentException('No site node specified.');
		
		return $nodeId;
	}
 
}

?>