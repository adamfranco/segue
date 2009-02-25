<?php
/**
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditHeaderFooterSiteVisitor.class.php,v 1.1 2008/03/25 15:40:50 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/EditModeSiteVisitor.class.php");

/**
 * This SiteVisitor enables editing of the header footer, but not the rest of the site.
 * 
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditHeaderFooterSiteVisitor.class.php,v 1.1 2008/03/25 15:40:50 adamfranco Exp $
 */
class EditHeaderFooterSiteVisitor
	extends ViewModeSiteVisitor
{

	/**
	 * @var object EditModeSiteVisitor $editModeVisitor;  
	 * @access private
	 * @since 9/24/07
	 */
	private $editModeVisitor;

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 9/24/07
	 */
	public function __construct () {
		parent::__construct();
		
		$this->editModeVisitor = new EditModeSiteVisitor;
		
		$this->editModeVisitor->_action = 'headerfooter';
		$this->editModeVisitor->_controlsVisitor->setReturnAction('headerfooter');
	}
	
	/**
	 * Visit a Fixed organizer
	 * 
	 * @param FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 9/24/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		if ($this->editModeVisitor->isHeaderOrFooter($organizer)) {
			return $organizer->acceptVisitor($this->editModeVisitor);
		} else {
			return parent::visitFixedOrganizer($organizer);
		}
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 1/15/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		if ($this->editModeVisitor->isHeaderOrFooter($organizer)) {
			return $organizer->acceptVisitor($this->editModeVisitor);
		} else {
			return parent::visitFlowOrganizer($organizer);
		}
	}	
}

?>