<?php
/**
 * @since 2/26/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: site_list.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
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
 * @version $Id: site_list.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
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
	
	/**
	 * Answer the Site asset for a content asset
	 * 
	 * @param object Asset $asset
	 * @return object Asset
	 * @access public
	 * @since 2/26/07
	 */
	function getSiteAsset ( $asset ) {
		$siteType = new Type ('segue', 'edu.middlebury', 'SiteNavBlock');
		if ($siteType->isEqual($asset->getAssetType())) {
			return $asset;
		} else {
			$parents = $asset->getParents();
			while ($parents->hasNext()) {
				$result = $this->getSiteAsset($parents->next());
				if ($result)
					return $result;
			}
		}
		
		$false = false;
		return $false;
	}
	
	/**
	 * Answer all media assets below the specified asset
	 * 
	 * @param object Asset $asset
	 * @param optional object Id $excludeId
	 * @return object Iterator
	 * @access public
	 * @since 2/26/07
	 */
	function getAllMediaAssets ( $asset, $excludeId = null ) {
		if ($excludeId && $excludeId->isEqual($asset->getId())) {
			$false = false;
			return $false;
		}
		
		if ($this->mediaFileType->isEqual($asset->getAssetType())) {
			$tmp = array();
			$tmp[] = $asset;
			$iterator = new HarmoniIterator($tmp);
			return $iterator;
		} else {
			$iterator = new MultiIteratorIterator();
			$children = $asset->getAssets();
			while ($children->hasNext()) {
				$result = $this->getAllMediaAssets($children->next(), $excludeId);
				if ($result) {
					$iterator->addIterator($result);
				}
			}
			
			if ($iterator->count())
				return $iterator;
			else {
				$false = false;
				return $false;
			}
		}
	}
}



?>