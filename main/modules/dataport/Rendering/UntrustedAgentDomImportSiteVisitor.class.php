<?php
/**
 * @since 1/25/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UntrustedAgentDomImportSiteVisitor.class.php,v 1.1 2008/01/25 20:50:53 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/DomImportSiteVisitor.class.php');

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
 * @version $Id: UntrustedAgentDomImportSiteVisitor.class.php,v 1.1 2008/01/25 20:50:53 adamfranco Exp $
 */
class UntrustedAgentDomImportSiteVisitor
	extends DomImportSiteVisitor
{
		
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return string
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryComment (DOMElement $element) {
		$agentMgr = Services::getService('Agent');
		$origComment = parent::getPluginHistoryComment($element);
		$origAgentId = parent::getPluginHistoryAgentId($element);
		$origAgent = $agentMgr->getAgent($origAgentId);
		$origTstamp = parent::getPluginHistoryTimestamp($element);
		
		$agent = $agentMgr->getAgent($this->getPluginHistoryAgentId($element));
		$tstamp = $this->getPluginHistoryTimestamp($element);
		
		// If there is no change in authoriship, just return the original comment.
		if (!$this->addChangedAgentComment($origAgentId, $agent->getId()))
			return $origComment;
		
		// Otherwise, note the change.
		$time = $tstamp->asTime();
		$tstampString = $tstamp->ymdString()." ".$time->string12(false);
		
		$time = $origTstamp->asTime();
		$origTstampString = $origTstamp->ymdString()." ".$time->string12(false);
		
		$additional = _("Imported by %agent% on %tstamp%, marked as being created by %origAgent% on %origTstamp%.");		
		$additional = str_replace("%agent%", $agent->getDisplayName(), $additional);
		$additional = str_replace("%tstamp%", $tstampString, $additional);
		$additional = str_replace("%origAgent%", $origAgent->getDisplayName(), $additional);
		$additional = str_replace("%origTstamp%", $origTstampString, $additional);
		
		return $origComment." ".$additional;
	}
	
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
		if ($origAgentId->isEqual($newAgentId))
			return false;
		else
			return true;
	}
	
	/**
	 * Answer a Plugin history comment for an element. Extensions of this
	 * class may wish to override this method to modify the comment and/or store
	 * the import date/user instead of that listed in the history.
	 * 
	 * @param object DOMElement $element
	 * @return object Id
	 * @access protected
	 * @since 1/25/08
	 */
	protected function getPluginHistoryAgentId (DOMElement $element) {
		$authN = Services::getService("AuthN");
		return $authN->getFirstUserId();
	}
	
	/**
	 * Set the Authorship of an asset based on the agent id listed in its corresponding
	 * element. Extensions of this class may wish to override this method to do nothing,
	 * there-by making the authorship that of the user doing the import.
	 * 
	 * @param object Asset $asset
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/25/08
	 */
	protected function setAssetAuthorship (Asset $asset, DOMElement $element) {
		// Do nothing. Leave automatically recorded agents alone.
	}
	
}

?>