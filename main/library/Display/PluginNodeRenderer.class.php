<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginNodeRenderer.class.php,v 1.13 2006/02/22 19:40:45 adamfranco Exp $
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
 * @version $Id: PluginNodeRenderer.class.php,v 1.13 2006/02/22 19:40:45 adamfranco Exp $
 */
class PluginNodeRenderer
	extends NodeRenderer
{
	/**
	 * @var object Plugin $_plugin;  
	 * @access private
	 * @since 1/20/06
	 */
	var $_plugin;
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		if ($this->getTitle())
			$title = "\n<div style='font-size: larger; font-weight: bold; border-bottom: 1px solid; margin-bottom: 5px;'>".$this->getTitle()."</div>";
		else
			$title = "";
		$plugs =& Services::getService("Plugs");
		$component =& new MenuItem(
						$title
							.$plugs->getPluginText($this->_asset,
								$this->shouldShowControls())
							.$this->getSettingsForm(),
						$level);
		
		$id =& $this->getId();
		$component->setId($id->getIdString()."-nav");
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
		$plugs =& Services::getService("Plugs");

		$component =& new Block(
			$plugs->getPluginText($this->_asset, $this->shouldShowControls())
				.$this->getSettingsForm(),
			STANDARD_BLOCK);
			
		$id =& $this->getId();
		$component->setId($id->getIdString()."-target");
		return $component;
	}
		
	/**
	 * Answer the title that should be displayed for this node.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getTitle () {
		$plugs =& Services::getService("Plugs");
		return $plugs->getPluginTitleMarkup($this->_asset, $this->shouldShowControls());
	}
}