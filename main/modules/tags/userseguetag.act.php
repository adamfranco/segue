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
require_once(dirname(__FILE__)."/SegueAllTagAction.abstract.php");
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
 
class userseguetagAction 
	extends SegueAllTagAction
{	
	/**
	 * Add the site header gui components
	 * 
	 * @return Component
	 * @access public
	 * @since 4/7/08
	 */
	public function getSiteHeader () {
		throw new UnimplementedException("No site header for this action.");
	}
	
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
			_("items tagged with '%1' in all of Segue by you"));
		return new Block($title, STANDARD_BLOCK);
	}
	
	/**
	 * Answer the items with given tag in a Segue site by a given user
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getItems () {	
		$harmoni = Harmoni::instance();
		$tag = $this->getTag();		
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		
		$tagManager = Services::getService("Tagging");		
		$agentId = $tagManager->getCurrentUserId();
		
		$visitor = new TaggableItemVisitor;
		$items = $rootSiteComponent->acceptVisitor($visitor);
		$tagIds = $tag->getItemsInList($items);
		//printpre($tagIds);
		return $tag->getItemsForAgentInListinSystem($tagIds, $agentId, "segue");
	}

	/**
	 * Add display of tags
	 * 
	 * @return Component
	 * @access public
	 * @since 4/7/08
	 */
	public function getResult () {	 
		$harmoni = Harmoni::instance();
		
		$items = $this->getItems();
		$resultPrinter = new IteratorResultPrinter($items, 1, 5, 
									array($this, 'getTaggedItemComponent'), $this->getViewAction());
		$resultLayout = $resultPrinter->getLayout(array($this, "canViewItem"));	
		
			
		return $resultLayout;
	}

	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'userseguetag';
	}

}

?>