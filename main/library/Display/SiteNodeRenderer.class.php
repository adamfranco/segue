<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNodeRenderer.class.php,v 1.5 2006/02/22 19:40:45 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavigationNodeRenderer.class.php");

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
 * @version $Id: SiteNodeRenderer.class.php,v 1.5 2006/02/22 19:40:45 adamfranco Exp $
 */
class SiteNodeRenderer
	extends NavigationNodeRenderer
{
	
	/**
	 * If true, editing controls will be displayed
	 * @var boolean $_showControls;  
	 * @access private
	 * @since 2/22/06
	 */
	var $_showControls = false;
	
	/**
	 * Add parents to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveUp () {
		$this->setActive();
		return;
	}
	
	/**
	 * Accept a NodeVisitor. This method is part of the "Visitor" design pattern.
	 * It allows sets of "visitors" to traverse the object tree, acting on each node.
	 * 
	 * @param object NodeVisitor $nodeVisitor
	 * @return void
	 * @access public
	 * @since 2/20/06
	 */
	function acceptVisitor ( &$nodeVisitor ) {
		$nodeVisitor->visitSiteNode($this);
	}
	
	/**
	 * Answer the top-level NodeRenderer
	 * 
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/26/06
	 */
	function &getSiteRenderer () {
		return $this; 
	}
	
	/**
	 * Answer the GUI component for the contents of the site that this node
	 * is in.
	 * 
	 * @param optional boolean $showControls
	 * @return object Component
	 * @access public
	 * @since 1/26/06
	 */
	function &renderSite ( $showControls = false ) {
		$this->setShowControls($showControls);
		return $this->renderTargetComponent();
	}
	
	/**
	 * Answer true if this component should display controls.
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/22/06
	 */
	function shouldShowControls () {
		return $this->_showControls;
	}
	
	/**
	 * Set the status of showControls
	 * 
	 * @param boolean $showControls
	 * @return void
	 * @access public
	 * @since 2/22/06
	 */
	function setShowControls ( $showControls = false ) {
		ArgumentValidator::validate($showControls, BooleanValidatorRule::getRule());
		$this->_showControls = $showControls;
	}
	
	/**
	 * Answer the site title is in.
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function getSiteTitle () {
		return $this->_asset->getDisplayName();
	}
	
	/**
	 * Anwser the link for the delete action
	 * 
	 * @return string
	 * @access public
	 * @since 2/20/06
	 */
	function getDeleteLink () {
		return '';
	}
	
	/**
	 * print the order form elements
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionOrderForm () {}
	
	/**
	 * print the cell position form elements
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionCellPositionForm () {}
}