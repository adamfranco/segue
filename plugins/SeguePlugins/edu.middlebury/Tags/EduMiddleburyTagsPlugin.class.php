<?php
/**
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.7 2008/04/11 19:48:28 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");
require_once(dirname(__FILE__)."/TaggableItemVisitor.class.php");
require_once(dirname(__FILE__)."/TagCloudNavParentVisitor.class.php");

/**
 * A simple plugin for including links in a site
 * 
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.7 2008/04/11 19:48:28 achapin Exp $
 */
class EduMiddleburyTagsPlugin 
	extends SegueAjaxPlugin
//	extends SeguePlugin

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
 		 return _("The Tags plugin allows users to add a block that displays all the tags for a given node and its children"); 	
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
 		return _("Tags");
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
				
		if ($this->canView()) {
			$items = array();
 			$director = SiteDispatcher::getSiteDirector();
 			$node = $director->getSiteComponentById($this->getId());
 			
 			// Determine the navigational node above this tag cloud.
 			$visitor = new TagCloudNavParentVisitor;
 			$parentNavNode = $node->acceptVisitor($visitor);
 			 			
 			$visitor = new TaggableItemVisitor;
 			$items = $parentNavNode->acceptVisitor($visitor);
 			
 			SiteDispatcher::passthroughContext();

 			print TagAction::getReadOnlyTagCloudForItems($items, 'sitetag', null);				
 			SiteDispatcher::forgetContext();
 			
 			print "\n<div class='breadcrumbs' style='height: auto; margin-top: 10px; margin-bottom: 5px;'>";
 			print str_replace('%1', $parentNavNode->acceptVisitor(new BreadCrumbsVisitor($parentNavNode)),
 				_("Tags within %1"));
 			print "</div>";
 		}
		
		return ob_get_clean();
 	} 
}

?>