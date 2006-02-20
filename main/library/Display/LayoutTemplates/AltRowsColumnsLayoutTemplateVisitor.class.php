<?php
/**
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AltRowsColumnsLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
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
 * @version $Id: AltRowsColumnsLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */
class AltRowsColumnsLayoutTemplateVisitor
	extends LayoutTemplateVisitor
{
	
	/**
	 * @var string $_lastArrangement; The last arrangement used 
	 * @access private
	 * @since 2/20/06
	 */
	var $_arrangement;
	
	/**
	 * Apply the template to a Site Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitSiteNode ( &$nodeRenderer ) {		
		$this->visitNavigationNode($nodeRenderer);
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
		$part =& $nodeRenderer->getLayoutArrangementPart();
		
		// set an initial value
		if (!$this->_arrangement) {
			$value =& $part->getValue();
			if ($value->asString() == 'rows')
				$this->_arrangement = 'rows';
			else
				$this->_arrangement = 'columns';
		} 
		// Apply the opposite of the last value
		else {
			$part->updateValue(String::withValue($this->_arrangement));
		}
		
		$this->swapArrangement();
		$this->visitChildren($nodeRenderer);
		$this->swapArrangement();
	}
	
	/**
	 * Swap the arrangement
	 * 
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function swapArrangement () {
		if ($this->_arrangement == 'columns')
			$this->_arrangement = 'rows';
		else
			$this->_arrangement = 'columns';
	}
}

?>