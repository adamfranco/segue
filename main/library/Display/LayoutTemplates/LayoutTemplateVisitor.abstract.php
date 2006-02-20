<?php
/**
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LayoutTemplateVisitor.abstract.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */ 

/**
 * Apply a layout-template to a tree of nodes.
 * 
 * @since 2/20/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: LayoutTemplateVisitor.abstract.php,v 1.1 2006/02/20 21:53:09 adamfranco Exp $
 */
class LayoutTemplateVisitor {
		
	/**
	 * Apply the template to a Site Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitSiteNode ( &$nodeRenderer ) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class."); 
	}
	
	/**
	 * Apply the template to a Generic Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitGenericNode ( &$nodeRenderer ) { }
	
	/**
	 * Apply the template to a Plugin Node
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitPluginNode ( &$nodeRenderer ) { }
	
	/**
	 * Traverse through children
	 * 
	 * @param object NodeRenderer $nodeRenderer
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function visitChildren ( &$nodeRenderer ) {
		$children =& $nodeRenderer->getOrderedChildren();
		foreach (array_keys($children) as $key) {
			$children[$key]->acceptVisitor($this);
		}
	}
}

?>