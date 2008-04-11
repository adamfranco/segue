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
 
class nodeAction 
	extends SegueAllTagAction
{	

	/**
	 * Answer the title of this result set
	 * 
	 * @return string
	 * @access public
	 * @since 4/8/08
	 */
	public function getResultTitle () {
		$node = SiteDispatcher::getCurrentNode();
 		$title = str_replace('%2', 
 			$node->acceptVisitor(new BreadCrumbsVisitor($node)),
 			_("All tags added by everyone within %2 "));

		return new Heading($title, 2);

	}
	
	/**
	 * Answer the tags for given item by everyone 
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getTags () {	
		$harmoni = Harmoni::instance();
		$tagManager = Services::getService("Tagging");
		
		$SiteComponent = SiteDispatcher::getCurrentNode();

		$visitor = new TaggableItemVisitor;
		$items = $SiteComponent->acceptVisitor($visitor);
		SiteDispatcher::passthroughContext();
		$tags =$tagManager->getTagsForItems($items, TAG_SORT_ALFA, $this->getNumTags());

		return $tags;
	}	

	/**
	 * Answer the number of tags to show
	 * 
	 * @return integer
	 * @access public
	 * @since 12/5/06
	 */
	function getNumTags () {
		if (RequestContext::value('num_tags') !== null)
			$_SESSION['__NUM_TAGS'] = intval(RequestContext::value('num_tags'));
		else if (!isset($_SESSION['__NUM_TAGS']))
			$_SESSION['__NUM_TAGS'] = 100;
		
		return $_SESSION['__NUM_TAGS'];
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