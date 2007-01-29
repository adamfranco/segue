<?php
/**
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: upload.act.php,v 1.1 2007/01/29 21:26:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaAction.abstract.php");

/**
 * Handle the uploading of a new file to the media library
 * 
 * @since 1/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: upload.act.php,v 1.1 2007/01/29 21:26:09 adamfranco Exp $
 */
class uploadAction
	extends MediaAction
{
		
	/**
	 * Process the changes and build the output
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	function buildContent () {		
		if (!isset($_FILES['media_file']))
			$this->error('No Form Submitted');
		
		if ($_FILES['media_file']['error'])
			$this->error('An error has occured, no file uploaded.');
			
		if (!$_FILES['media_file']['size'])
			$this->error('Uploaded file is empty');
		
		ob_start();
		$newFileAsset =& $this->createFileAsset();
		if ($error = ob_get_clean())
			$this->error($error);
		
		$this->start();
		print $this->getAssetXml($newFileAsset);
		$this->end();
	}
	
	
	/**
	 * Create a new file asset
	 * 
	 * @return object Asset
	 * @access public
	 * @since 1/26/07
	 */
	function &createFileAsset () {
		$contentAsset =& $this->getContentAsset();
		$repository =& $contentAsset->getRepository();
		
		if (!($displayName = RequestContext::value('displayName')))
			$diplayName = 'Untitled Media File';
		
		if (!($description = RequestContext::value('description')))
			$description = '';
		
		$asset =& $repository->createAsset(
					$displayName,
					$description,
					$this->mediaFileType);
		
		$contentAsset->addAsset($asset->getId());		
		
		return $asset;
	}
	
}

?>