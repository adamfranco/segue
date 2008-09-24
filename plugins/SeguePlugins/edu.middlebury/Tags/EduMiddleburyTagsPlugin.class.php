<?php
/**
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.9 2008/04/11 21:51:37 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");
require_once(dirname(__FILE__)."/TaggableItemVisitor.class.php");
require_once(dirname(__FILE__)."/TagCloudNavParentVisitor.class.php");
require_once(dirname(__FILE__)."/ContainerInfoVisitor.class.php");
require_once(dirname(__FILE__)."/UmbrellaVisitor.class.php");

/**
 * A simple plugin for including links in a site
 * 
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.9 2008/04/11 21:51:37 achapin Exp $
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
	 * Update plugin data from requests
 	 * In our situation, update the options used
	 * by this particular instance of the plugin
	 * 
	 * @param array $request
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */

	public function update( $request ) {
		if($this->getFieldValue('tagNode')){
			$this->setContent($this->getFieldValue('tagNode'));	
		} 
	}


	/**
	 * Helper methods, writes out options for a selection box given 
	 * the variety of tree that the UmbrellaVisitor generates
	 *
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */	
	public function writeUmbrellaSelect($node, $target, $depth = 0){
		$toWrite = $node[0];
		echo "<!-- ${target} -->\n";
		echo "<option value='".$toWrite[1]."'";
		if($target == $toWrite[1]){
			echo " selected";
		}
		echo ">";
		if($depth > 0){
			echo str_repeat("&nbsp;",($depth*5));	
		}
		echo $toWrite[0]."</option>\n";
		for($i = 1; $i < sizeof($node); $i++){
			$this->writeUmbrellaSelect($node[$i], $target, $depth+1);	
		} 
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
	

		if($this->getFieldValue('edit') && $this->canModify()){
			$director = SiteDispatcher::getSiteDirector();
			$node = $director->getSiteComponentById($this->getId());	

			$currentTarget = $this->getId();
			if($this->getContent()){
				$currentTarget = $this->getContent();
			} else {
				$visitor = new TagCloudNavParentVisitor;
				$parentNavNode = $node->acceptVisitor($visitor);
				$currentTarget = $parentNavNode->getId();
			}		
 			$node = $director->getSiteComponentById($this->getId());
			print "\n".$this->formStartTagWithAction();
			$visitor = new UmbrellaVisitor;
			$node->acceptVisitor($visitor);
			print "<div>Select the node: ";
			print "<select name='".$this->getFieldName('tagNode')."'>";
			$this->writeUmbrellaSelect($visitor->getNodeData(),$currentTarget);
			print "</select>";
			print "<input type='submit' value='Update' name='".$this->getFieldName('submit')."'>\n";
			print "</div>";
			print "</form>";
		} else if ($this->canView()) {
			$items = array();
	 		$director = SiteDispatcher::getSiteDirector();
			$id = $this->getId();
	 		
			if(!$this->getContent()){  
				$node = $director->getSiteComponentById($this->getId());	
			} else {
				$node = $director->getSiteComponentById($this->getContent());
			}
	
 			// Determine the navigational node above this tag cloud.
	 		$visitor = new TagCloudNavParentVisitor;
 			$parentNavNode = $node->acceptVisitor($visitor);
 			 			
 			$visitor = new TaggableItemVisitor;
	 		$items = $parentNavNode->acceptVisitor($visitor);
 		
 			//SiteDispatcher::passthroughContext();
 	

			print "\n<div class='breadcrumbs' style='height: auto; margin-top: 1px; margin-bottom: 5px; border-bottom: 1px dotted; padding-bottom: 2px;'>";
 			print str_replace('%1', $parentNavNode->acceptVisitor(new BreadCrumbsVisitor($parentNavNode)),
 			_("Tags within: %1"));

 			print "</div>";
			print "\n<div style='text-align: justify;'>";
// 			print TagAction::getReadOnlyTagCloudForItems($items, 'sitetag', null);
			$tags = TagAction::getTagsFromItems($items);
			print TagAction::getTagCloudDiv($tags, 'viewuser', null);
			print "</div>";
			if($this->shouldShowControls()){
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">".("Configure Tag Cloud")."</a>";
				print "\n</div>";
			}
		
			/* 	
			$visitor = new UmbrellaVisitor;
			$node->acceptVisitor($visitor);
			print "<br/><pre>";
			print_r($visitor->getNodeData());
			print "</pre><br/>\n";
			*/


 			SiteDispatcher::forgetContext();

 		}
				
		return ob_get_clean();
 	} 

}

?>
