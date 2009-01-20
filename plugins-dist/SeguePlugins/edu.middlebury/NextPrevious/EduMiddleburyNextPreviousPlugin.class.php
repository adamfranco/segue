<?php
/**
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyBreadcrumbsPlugin.class.php,v 1.3 2008/04/07 19:25:28 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * A simple plugin for displaying links to the next/previous page in the current section.
 * (this plugin can not be used outside of Segue as it getting information
 * about a given Segue site's context)
 * 
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyBreadcrumbsPlugin.class.php,v 1.3 2008/04/07 19:25:28 achapin Exp $
 */
class EduMiddleburyNextPreviousPlugin 
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
 		 return _("The Next/Previous Links plugin prints links to the next and previous pages in the current section. It is best used when added to a section's sidebar or header/footer."); 	
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
 		return _("Next/Previous Links");
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
 		return array("Adam Franco");
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
		
		
		$director = SiteDispatcher::getSiteDirector();
		$myComponent = $director->getSiteComponentById($this->getId());
		$myNav = $this->getParentNavBlock($myComponent);
		
		if (!$myNav->isSection()) {
			return "<div style='text-align: center'>"._("This Next/Previous Links block is attached to a page, not a section. <br/>No links will be displayed unless it is attached to a section, such as in a side-bar.")."</div>";
		}
		$menu = $myNav->acceptVisitor(new GetMenuBelowSiteVisitor);
		
		if (!is_object($menu))
			throw new InvalidArgumentException("'".$menu."' is not a MenuOrganizerSiteComponent");
		
		if (!$menu instanceof MenuOrganizerSiteComponent)
			throw new InvalidArgumentException(get_class($menu)." is not a MenuOrganizerSiteComponent");
		
		ob_start();
		$numCells = $menu->getTotalNumberOfCells();
		
		for ($i = 0; $i < $numCells; $i++) {
			$child = $menu->getSubcomponentForCell($i);
			if (is_object($child) && $child->getComponentClass() == 'NavBlock' && $child->isActive()) {
				$activePos = $i;
				break;
			}
		}
		
		if (!isset($activePos))
			$activePos = 0;
		
		print "\n<div class='next_previous_links'>";
		$harmoni = Harmoni::instance();
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		$viewId = $idMgr->getId("edu.middlebury.authorization.view");
		for ($i = $activePos - 1; $i >= 0; $i--) {
			$child = $menu->getSubcomponentForCell($i);
			if (is_object($child) && $child->getComponentClass() == 'NavBlock'
				&& $authZ->isUserAuthorized($viewId, $child->getQualifierId())) 
			{
				// Found a previous nav
				print "\n\t<div style='float: left; padding-right: 5px;'>";
				print "\n\t\t<a href='".$this->getUrlForComponent($child->getId())."'>";
				print "&laquo; ".$this->cleanHTML($child->getDisplayName());
				print "</a>";
				print "\n\t</div>";
				break;
			}
		}
		
		for ($i = $activePos + 1; $i < $numCells; $i++) {
			$child = $menu->getSubcomponentForCell($i);
			if (is_object($child) && $child->getComponentClass() == 'NavBlock'
				&& $authZ->isUserAuthorized($viewId, $child->getQualifierId())) 
			{
				// Found a next nav
				print "\n\t<div style='float: right; padding-left: 5px;'>";
				print "\n\t\t<a href='".$this->getUrlForComponent($child->getId())."'>";
				print $this->cleanHTML($child->getDisplayName())." &raquo;";
				print "</a>";
				print "\n\t</div>";
				break;
			}
		}
		
		print "\n</div>";
		
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
	protected function getUrlForComponent ( $id ) {
		$harmoni = Harmoni::instance();
		if ($harmoni->request->getRequestedModule() == 'versioning') {
			$origUrl = $harmoni->history->getReturnURL('view_history_'.SiteDispatcher::getCurrentNodeId());
			$module = $harmoni->request->getModuleFromUrl($origUrl);
			if ($module == false)
				$module = 'ui1';
			$action = $harmoni->request->getActionFromUrl($origUrl);
			if ($action == false)
				$action = 'view';
		} else {
			$module = $harmoni->request->getRequestedModule();
			$action = $harmoni->request->getRequestedAction();
		}
		return SiteDispatcher::quickURL(
			$module, 
			$action,
			array("node" => $id));
	}
 	
 	/**
 	 * Answer the NavBlock above the node passed
 	 * 
 	 * @param SiteComponent $siteComponent
 	 * @return NavBlockSiteComponent
 	 * @access protected
 	 * @since 8/27/08
 	 */
 	protected function getParentNavBlock (SiteComponent $siteComponent) {
 		$parent = $siteComponent->getParentComponent();
 		if (!$parent)
 			throw new OperationFailedException("No Parent Component.");
 		
 		switch ($parent->getComponentClass()) {
 			case 'NavBlock':
 			case 'SiteNavBlock':
 				return $parent;
 			default:
 				return $this->getParentNavBlock($parent);
 		}
 	}
 
}

/**
 * Answer the menu below a site component
 * 
 * @since 8/27/08
 * @package segue.plugins
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class GetMenuBelowSiteVisitor 
	extends HasMenuBelowSiteVisitor
{
		
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if ($siteComponent->getNestedMenuOrganizer())
			return $siteComponent->getNestedMenuOrganizer();
		else
			return $siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$result = $child->acceptVisitor($this);
				if ($result)
					return $result;
			}
		}
		
		return false;
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return $siteComponent;
	}
}


?>