<?php
/**
 * @since 4/7/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id:
 */ 

// require_once(POLYPHONY."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/SegueSingleTagAction.abstract.php");
require_once(dirname(__FILE__)."/TagModeSiteVisitor.class.php");
require_once(MYDIR."/plugins-dist/SeguePlugins/edu.middlebury/Tags/TaggableItemVisitor.class.php");

/**
 * This action gets all nodes in a given Segue node with a given tag.
 * 
 * @since 4/7/08
 * @package segue.module.tags
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id:
 */
 
class nodetagAction 
	extends SegueSingleTagAction
{	

	/**
	 * Answer the title of this result set
	 * 
	 * @return string
	 * @access public
	 * @since 4/8/08
	 */
	public function getResultTitle () {
		$tag = RequestContext::value('tag');
		$title = str_replace('%1', "<strong>".$tag."</strong>",
			_("'%1' tag added by everyone within %2 "));

		$node = SiteDispatcher::getCurrentNode();
 		$title = str_replace('%2', 
 			$node->acceptVisitor(new BreadCrumbsVisitor($node)),
 			$title);
 				
		return new Heading($title, 2);
	}
	
	/**
	 * Answer the items with given tag for a given item
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getItems () {		
		$tag = $this->getTag();		
		$SiteComponent = SiteDispatcher::getCurrentNode();
		
		$visitor = new TaggableItemVisitor;
		$items = $SiteComponent->acceptVisitor($visitor);
		$tagIds = $tag->getItemsInList($items);
		return $tag->getItemsWithIdsInSystem($tagIds, "segue");
	}	
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'nodetag';
	}

}

?>