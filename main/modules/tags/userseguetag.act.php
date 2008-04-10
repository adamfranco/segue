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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/07/06
	 */
	function execute () {
		$mainScreen = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);	
		
		// implemented in parent class htmlAction
		$allWrapper = $this->addHeaderControls($mainScreen);
		
		// implemented by this class
	//	$this->addSiteHeader($mainScreen);
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('polyphony-tags');
		
		$this->addTagsMenu($mainScreen);
	
		// implemented by child classes
		SiteDispatcher::passthroughContext();
		$this->getResult($mainScreen);
				
		$harmoni->request->endNamespace();
		
		//not sure why output buffer needs to be started here...
		ob_start();
		//implemented in parent class htmlAction
		$this->addFooterControls($mainScreen);
		$this->mainScreen = $mainScreen;
		return $allWrapper;
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
		return str_replace('%1', $tag,
			_("items tagged with '%1' in all of Segue by you"));
	}
	
	/**
	 * Answer the items with given tag in a given Segue site
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
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function getResult (Component $mainScreen) {
	 
		$harmoni = Harmoni::instance();
		
		$items = $this->getItems();
		$resultPrinter = new IteratorResultPrinter($items, 1, 5, 
									array($this, 'getTaggedItemComponent'), $this->getViewAction());
		$resultLayout = $resultPrinter->getLayout(array($this, "canViewItem"));	
		
				
		$mainScreen->add($resultLayout, "100%", null, LEFT, CENTER);		
		$mainScreen->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
				
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