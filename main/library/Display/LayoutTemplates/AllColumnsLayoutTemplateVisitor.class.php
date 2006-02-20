<?php
/**
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllColumnsLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
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
 * @version $Id: AllColumnsLayoutTemplateVisitor.class.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */
class AllColumnsLayoutTemplateVisitor
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
		$part->updateValue(String::withValue('columns'));
		
		$this->visitChildren($nodeRenderer);
	}
}

?>