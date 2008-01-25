<?php
/**
 * @since 1/25/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UntrustedAgentAndTimeDomImportSiteVisitor.class.php,v 1.1 2008/01/25 20:50:53 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/UntrustedAgentDomImportSiteVisitor.class.php');

/**
 * This class imports a site similar to its parent class with the exception that
 * the created-by and modified-by agent ids are not trusted and are replaced with 
 * those of the user doing the import. History comments are updated to mention the
 * Agent who allegedly created that version.
 *
 * In this class timestamps ARE trusted.
 * 
 * @since 1/25/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UntrustedAgentAndTimeDomImportSiteVisitor.class.php,v 1.1 2008/01/25 20:50:53 adamfranco Exp $
 */
class UntrustedAgentAndTimeDomImportSiteVisitor
	extends UntrustedAgentDomImportSiteVisitor
{
	
	/**
	 * Answer true if a comment should be added to the history to indicate a change in
	 * author/time.
	 * 
	 * @param object Id $origAgentId
	 * @param object Id $newAgentId
	 * @return boolean
	 * @access protected
	 * @since 1/25/08
	 */
	protected function addChangedAgentComment (Id $origAgentId, Id $newAgentId) {
		return true;
	}
	
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return object DateAndTime
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryTimestamp (DOMElement $element) {
		return DateAndTime::now();
	}
	
	/**
	 * Set the creation and modification dates of an asset based on the dates listed 
	 * in its corresponding element. Extensions of this class may wish to override 
	 * this method to do nothing, there-by leaving the dates to be the time of the import.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function setAssetDates (Asset $asset, DOMElement $element) {
		// Do nothing. Leave automatically recorded dates alone.
	}
}

?>