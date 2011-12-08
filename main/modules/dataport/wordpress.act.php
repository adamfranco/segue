<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(dirname(__FILE__)."/Rendering/WordpressExportSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This action will export a site to an xml file
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.8 2008/04/09 21:12:02 adamfranco Exp $
 */
class wordpressAction
	extends Action
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.view'),
			SiteDispatcher::getCurrentRootNode()->getQualifierId());
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 1/17/08
	 */
	public function execute () {
		$harmoni = Harmoni::instance();
				
		$component = SiteDispatcher::getCurrentNode();
		$site = SiteDispatcher::getCurrentRootNode();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($site->getId());
		
		try {
			// Do the export
			$visitor = new WordpressExportSiteVisitor();
			$component->acceptVisitor($visitor);
			
			// Validate the result
// 			printpre(htmlentities($visitor->doc->saveXMLWithWhitespace()));
// 			$tmp = new Harmoni_DomDocument;
// 			$tmp->loadXML($visitor->doc->saveXMLWithWhitespace());
// 			$tmp->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
// 			$visitor->doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			
			header("Content-Type: text/plain");
// 			header('Content-Disposition: attachment; filename="'
// 								.basename($slot->getShortname().".xml").'"');
			print $visitor->doc->saveXMLWithWhitespace();		
		} catch (PermissionDeniedException $e) {
			return new Block(
				_("You are not authorized to export this component."),
				ALERT_BLOCK);
		} catch (Exception $e) {
			throw $e;
		}
		
		error_reporting(0);
		exit;
	}
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getNodeId () {
		return SiteDispatcher::getCurrentNodeId();
	}
}

?>