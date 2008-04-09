<?php
/**
 * @since 4/8/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueSingleTagAction.abstract.php,v 1.1 2008/04/09 21:27:23 achapin Exp $
 */ 
require_once(POLYPHONY."/main/modules/tags/TagAction.abstract.php");
require_once(dirname(__FILE__)."/SegueTagsAction.abstract.php");
require_once(POLYPHONY."/main/library/ResultPrinter/IteratorResultPrinter.class.php");

/**
 * This abstract class defines methods related to getting tags on nodes in Segue
 * 
 * @since 4/8/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueSingleTagAction.abstract.php,v 1.1 2008/04/09 21:27:23 achapin Exp $
 */
abstract class SegueSingleTagAction
	extends SegueTagsAction
{

	/**
	 * @var object Tag $_tag;  
	 * @access private
	 * @since 4/8/08
	 */
	private $_tag;
		
	/**
	 * Answer tag cloud of related tags
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/8/08
	 */
	public function getRelatedTagsResult (Component $mainScreen) {
		ob_start();
		print "<h3 style='margin-top: 0px; margin-bottom: 0px;'>"._("Related Tags:")."</h3>";
		
		$tag = $this->getTag();
		print TagAction::getTagCloudDiv($tag->getRelatedTags(TAG_SORT_FREQ), 'view', 100);
				
		$mainScreen->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
	}
	
	/**
	 * Answer the Tag for this action
	 * 
	 * @return object Tag
	 * @access public
	 * @since 12/8/06
	 */
	function getTag () {
		if (!isset($this->_tag)) {
			$this->_tag = new Tag(RequestContext::value('tag'));
		}
		return $this->_tag;
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
	 * Answer the tagged item GUI component
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 11/14/06
	 */
	function getTaggedItemComponent ( TaggedItem $item, $viewAction) {
		ob_start();
		
		$this->printTaggedItem($item, $viewAction);
		
		$component =  new Block(ob_get_clean(), EMPHASIZED_BLOCK);
		return $component;
	}
	
	/**
	 * Print out an Item
	 * 
	 * @param object $item
	 * @param string $viewAction The action to choose when clicking on a tag.
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function printTaggedItem ( TaggedItem $item, $viewAction) {	
		print "\n\t<a href='".$item->getUrl()."'>";
		if ($item->getThumbnailUrl())
			print "\n\t\t<img src='".$item->getThumbnailUrl()."' style=' float: right;' class='thumbnail_image' />";
		if ($item->getDisplayName())
			print "\n\t\t<strong>".$item->getDisplayName()."</strong>";
		else
			print "\n\t\t<strong>"._('untitled')."</strong>";
		print "\n\t</a>";
		print "\n\t<p>".$item->getDescription()."</p>";
		
		// Tags
		print "\n\t<p style='text-align: justify;'>";
		print "\n\t<strong>"._('Tags').":</strong> ";
		print TagAction::getTagCloudForItem($item, $viewAction,
				array(	'font-size: 90%;',
						'font-size: 100%;',
						'font-size: 110%;',
				));
		print "\n\t</p>";
		
		print "</p>";
		print "\n\t<p><strong>"._('System').":</strong> ";
		if ($item->getSystem() == ARBITRARY_URL)
			print _("The Internet");
		else
			print ucFirst($item->getSystem());
		print "</p>";
	}
		
	// Callback function for checking authorizations
	function canViewItem( TaggedItem $item ) {
		if ($item->getSystem() == ARBITRARY_URL)
			return true;
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $item->getId())
			|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $item->getId()))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
	

}