<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.1 2007/01/29 21:26:09 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");


/**
 * This Abstract class provides access to media assets within a parent.
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MediaAction.abstract.php,v 1.1 2007/01/29 21:26:09 adamfranco Exp $
 */
class MediaAction
	extends XmlAction
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/29/07
	 */
	function MediaAction () {
		$this->mediaFileType =& new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		if (method_exists($this, 'XmlAction'))
			$this->XmlAction();	
	}

	/**
	 * Check authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/26/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access the media library
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		$contentAsset =& $this->getContentAsset();
		
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"),
			$contentAsset->getId());
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to access this <em>Node's</em> media.");
	}
		
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->error($this->getUnauthorizedMessage());
		
		$this->buildContent();
		
		$this->start();
		$this->end();
	}
	
	/**
	 * Answer the asset that the media library belongs to.
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/26/07
	 */
	function &getContentAsset () {
		if (!isset($this->_contentAsset)) {
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
			$repository =& $repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository'));
			$this->_contentAsset =& $repository->getAsset(
				$idManager->getId(RequestContext::value('assetId')));
		}
		
		return $this->_contentAsset;
	}
	
	/**
	 * Answer out an XML block representing the given asset, its Dublin Core, and
	 * its file records
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function getAssetXml (&$asset) {
		ob_start();
		
		$assetId =& $asset->getId();
		print "\n\t<asset id=\"".$assetId->getIdString()."\">";
		
		print "\n\t\t<displayName><![CDATA[";
		print $asset->getDisplayName();		
		print "]]></displayName>";
		
		print "\n\t\t<description><![CDATA[";
		print $asset->getDescription();
		print "]]></description>";
		
// 		$fileRecords =&
// 		print "\n\t\t<fileRecord id=\"".$asset->getIdString()."\">";
		
		print "\n\t</asset>";
		
		return ob_get_clean();
	}
}

?>