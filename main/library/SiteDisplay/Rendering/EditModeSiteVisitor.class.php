<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.36 2007/01/15 17:57:15 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/ControlsSiteVisitor.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.36 2007/01/15 17:57:15 adamfranco Exp $
 */
class EditModeSiteVisitor
	extends ViewModeSiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/14/06
	 */
	function EditModeSiteVisitor () {
		$this->ViewModeSiteVisitor();
		$this->_controlsVisitor =& new ControlsSiteVisitor();
		$this->_classNames = array(
			'Block' => _('Block'),
			'NavBlock' => _('Link'),
			'MenuOrganizer' => _('Menu'),
			'FlowOrganizer' => _('ContentOrganizer'),
			'FixedOrganizer' => _('Organizer')
		);
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlock ( &$block ) {
		$guiContainer =& new Container (	new YLayout, BLOCK, 1);
		
		$pluginManager =& Services::getService('PluginManager');
		
		$guiContainer->add(
			new Heading(
				$pluginManager->getPluginTitleMarkup($block->getAsset(), true), 
				2),
		null, null, null, TOP);
		$guiContainer->add(
			new Block(
				$pluginManager->getPluginText($block->getAsset(), true),
				STANDARD_BLOCK), 
			null, null, null, TOP);
		
		return $guiContainer;
	}
}

?>