<?php
/**
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllNestedLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/LayoutTemplateVisitor.abstract.php");


/**
 * Apply a layout-template to a tree of nodes.
 * 
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllNestedLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */
class AllNestedLayoutTemplateVisitor
	extends LayoutTemplateVisitor
{
		
	/**
	 * Apply the template to a Site Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitSiteNode ( &$nodeRenderer ) {
		$part =& $nodeRenderer->getNumCellsPart();
		$value =& $part->getValue();
		$minValue =& Integer::withValue(2);
		if ($value->isLessThan($minValue))
			$part->updateValue($minValue);
		
		$part =& $nodeRenderer->getLayoutArrangementPart();
		$part->updateValue(String::withValue('columns'));
		
		$part =& $nodeRenderer->getTargetOverridePart();
		$value =& $part->getValue();
		$minValue =& Integer::withValue(2);
		if ($value->isLessThan($minValue))
			$part->updateValue($minValue);
		
		$this->visitChildren($nodeRenderer);
	}
	
	/**
	 * Apply the template to a Navigation Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitNavigationNode ( &$nodeRenderer ) {
		$part =& $nodeRenderer->getNumCellsPart();
		$value =& $part->getValue();
		$minValue =& Integer::withValue(2);
		
		// do not convert single-celled layouts to nested.
		if (!$value->isLessThan($minValue)) {
			$part =& $nodeRenderer->getLayoutArrangementPart();
			$part->updateValue(String::withValue('nested'));
			
			$part =& $nodeRenderer->getTargetOverridePart();
			$value =& $part->getValue();
			$minValue =& Integer::withValue(2);
			if ($value->isLessThan($minValue))
				$part->updateValue($minValue);
		}
		
		$this->visitChildren($nodeRenderer);
	}
}

?>