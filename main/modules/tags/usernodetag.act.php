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
require_once(MYDIR."/plugins/SeguePlugins/edu.middlebury/Tags/TaggableItemVisitor.class.php");

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
 
class usernodetagAction 
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
		$title = str_replace('%1', $tag,
			_("items tagged with '%1' for this node and its subnodes by you"));
		return new Block($title, STANDARD_BLOCK);
	}
	
	/**
	 * Answer the items with given tag for a given user 
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getItems () {	
		$harmoni = Harmoni::instance();
		$tag = $this->getTag();		
		$SiteComponent = SiteDispatcher::getCurrentNode();

		$tagManager = Services::getService("Tagging");		
		$agentId = $tagManager->getCurrentUserId();
		
		$visitor = new TaggableItemVisitor;
		$items = $SiteComponent->acceptVisitor($visitor);
		
		$tagIds = $tag->getItemsInList($items);
		return $tag->getItemsForAgentInListinSystem($tagIds, $agentId, "segue");
	}	
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'usernodetag';
	}

}

?>