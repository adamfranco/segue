<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GenericNodeRenderer.class.php,v 1.2 2006/01/20 20:53:25 adamfranco Exp $
 */ 

/**
 * The NodeRenderer class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GenericNodeRenderer.class.php,v 1.2 2006/01/20 20:53:25 adamfranco Exp $
 */
class GenericNodeRenderer
	extends NodeRenderer
{
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		$component =& new MenuItem(
						$this->getText(),
						$level);
						
		return $component;
	}
	
	/**
	 * Answer the GUI component for target area
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderTargetComponent ($level = 1) {		
		$component =& new Block(
						$this->getText(),
						STANDARD_BLOCK);
						
		return $component;
	}
	
	/**
	 * Answer the text for this node
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getText () {
		ob_start();
		
		print "<table>";
		
		print "\n\t<tr>";
		print "\n\t\t<th>"._("Title: ")."</th>";
		print "\n\t\t<td>";
		print $this->_asset->getDisplayName();
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n<th>"._("Description: ")."</th>";
		print "\n\t\t<td>";
		print $this->_asset->getDescription();
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n<th>"._("Type: ")."</th>";
		print "\n\t\t<td>";
		$type =& $this->_asset->getAssetType();
		print Type::TypeToString($type);
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n<th>"._("Content: ")."</th>";
		print "\n\t\t<td>";
		$content =& $this->_asset->getContent();
		print $content->asString();
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "</table>";
		
		return ob_get_clean();
	}
}