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
 
class segueAction 
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
		$harmoni = Harmoni::instance();
		ob_start();
		$harmoni->request->startNamespace('polyphony-tags');
		print "\n<form action='".SiteDispatcher::quickURL(null, null, array('tag' => RequestContext::value('tag')))."' method='post' style='display: inline;'>";
		print "\n\t<select name='".RequestContext::name('num_tags')."'";
		print " onchange='this.form.submit()'>";
		$options = array(50, 100, 200, 400, 600, 1000, 0);
		foreach ($options as $option)
			print "\n\t\t<option value='".$option."' ".(($option == $this->getNumTags())?" selected='selected'":"").">".(($option)?$option:_('all'))."</option>";
		print "\n\t</select>";
		print "\n</form>";
		$harmoni->request->endNamespace();
		return new Block(str_replace('%1', ob_get_clean(), _("Showing top %1 tags")), STANDARD_BLOCK);
	}
	
	/**
	 * Answer the title of this result set
	 * 
	 * @return string
	 * @access public
	 * @since 4/8/08
	 */
	public function getResultTitle () {
 		$title = _("All tags added by you within Segue");
		return new Heading($title, 2);
	}
	
	/**
	 * Answer all the tags in Segue by everyon 
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getTags () {	
		$harmoni = Harmoni::instance();
		$tagManager = Services::getService("Tagging");
		$tags =$tagManager->getTags(TAG_SORT_ALFA, $this->getNumTags());
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
		return 'seguetag';
	}

}

?>