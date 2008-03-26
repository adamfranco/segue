<?php
/**
 * @since 2/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: site_list.act.php,v 1.3 2008/03/26 18:23:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MediaAction.abstract.php");

/**
 * Return a list of media assets attached to a content asset
 * 
 * @since 2/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: site_list.act.php,v 1.3 2008/03/26 18:23:18 adamfranco Exp $
 */
class site_listAction
	extends MediaAction
{
	
	/**
	 * Process the changes and build the output
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	function buildContent () {
		$contentAsset = $this->getContentAsset();
		
		$this->start();
		$mediaAssets = $this->getAllMediaAssets(
			$this->getSiteAsset($contentAsset), $contentAsset->getId());
		while ($mediaAssets && $mediaAssets->hasNext()) {
			$child = $mediaAssets->next();
			if ($this->mediaFileType->isEqual($child->getAssetType()))
				print $this->getAssetXml($child);
		}
		$this->end();
	}
}



?>