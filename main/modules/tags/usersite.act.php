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
 
class usersiteAction 
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
		$node = SiteDispatcher::getCurrentRootNode();
 		$title = str_replace('%2', 
 			$node->acceptVisitor(new BreadCrumbsVisitor($node)),
 			_("All tags added by you within %2 "));

		return new Heading($title, 2);

	}
	
	/**
	 * Answer all the tags on this site by given user 
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getTags () {	
		$harmoni = Harmoni::instance();
		$tag = $this->getTag();		
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		$agentManager = Services::getService("Agent");
		$tagManager = Services::getService("Tagging");
		
		$agentId = $tagManager->getCurrentUserId();
		
		$visitor = new TaggableItemVisitor;
		$items = $rootSiteComponent->acceptVisitor($visitor);
		SiteDispatcher::passthroughContext();
		$tags = $tagManager->getTagsForItemsByAgent($items, $agentId);
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
		return 'usersitetag';
	}
		

}

?>