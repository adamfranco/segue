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

/**
 * This action displays all the Segue nodes with a given tag.
 * 
 * @since 4/7/08
 * @package segue.module.tags
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id:
 */
 
class seguetagAction 
	extends SegueSingleTagAction
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
			_("items tagged with '%1' in all of Segue by everyone"));
	}
	
	/**
	 * Answer the items with given tag in all of Segue
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getItems () {
		$tag = $this->getTag();
		return $tag->getItems();
	}	
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'seguetag';
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


}

?>