<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.2 2007/04/27 15:13:31 adamfranco Exp $
 */

/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.2 2007/04/27 15:13:31 adamfranco Exp $
 */
class EduMiddleburyDownloadPlugin
	extends SeguePluginsAjaxPlugin
// 	extends SeguePluginsPlugin
{
		
	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function initialize () {
		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function update ( $request ) {
 		if ($this->getFieldValue('submit')) { 			
 			$this->saveIds(
 				$this->getFieldValue('repository_id'),
 				$this->getFieldValue('asset_id'),
 				$this->getFieldValue('record_id'));
 			$this->setDescription($this->getFieldValue('description'));
 			$this->logEvent('Modify Content', 'File for download updated');
 		}
 	}
 	 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function getMarkup () {
 		ob_start();
 		
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();
			
			if ($this->getContent()) {
				$info = explode("\n",$this->getContent());
			} else {
				$info = array("", "", "");
			}
 			
 			print "\n\t<input name='".$this->getFieldName('repository_id')."' type='hidden' value=\"".$info[0]."\"/>";
 			print "\n\t<input name='".$this->getFieldName('asset_id')."' type='hidden' value=\"".$info[1]."\"/>";
 			print "\n\t<input name='".$this->getFieldName('record_id')."' type='hidden' value=\"".$info[2]."\"/>";
 			
 			// Description
 			print "\n\t<textarea name='".$this->getFieldName('description')."' rows='5' cols='40'>".$this->getDescription()."</textarea>";
 			
 			// Select File button
 			print "\n\t<br/><br/><input type='button' value='"._('Select File')."' onclick=\"";
 			print "this.onUse = function (mediaFile) { ";
 			
 			print 		"this.form.elements['".$this->getFieldName('repository_id')."'].value = mediaFile.asset.repositoryId; ";
 			print 		"this.form.elements['".$this->getFieldName('asset_id')."'].value = mediaFile.asset.id; ";
 			print 		"this.form.elements['".$this->getFieldName('record_id')."'].value = mediaFile.id; ";
 			
 			print		"var downloadBar = document.createElement('div'); ";
 			print 		"var link = downloadBar.appendChild(document.createElement('a')); ";
 			print 		"link.href = mediaFile.url.escapeHTML(); ";
 			print		"link.title = mediaFile.name.escapeHTML(); ";
 			
 			print		"var img = link.appendChild(document.createElement('img')); ";
 			print		"img.src = mediaFile.thumbnailUrl; ";
 			print		"img.align = 'left'; ";
 			print		"img.border = '0'; ";
 			
 			print		"var title = downloadBar.appendChild(document.createElement('div')); ";
 			print 		"title.innerHTML = mediaFile.asset.displayName; ";
 			print		"title.fontWeight = 'bold'; ";
 			
 			print		"var citation = downloadBar.appendChild(document.createElement('div')); ";
 			print 		"mediaFile.asset.writeCitation(citation); ";
 			
 			print 		"this.nextSibling.innerHTML = '<div>' + downloadBar.innerHTML + '<div style=\\'clear: both;\\'></div></div>'; ";
 			print "}; "; 
 			print "MediaLibrary.run('".$this->getId()."', this); ";
 			print "\"/>";
 			
 			// Container for example download bar.
 			print "<div>".$this->getDownloadBar()."</div>";
 			
 			
 			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";

 			
			print "\n</form>";
 		} else if ($this->canView()) {
//  			if ($this->shouldShowControls()) {
// 				print "\n<div onclick=".$this->url(array('edit' => 'true')).">";
//  			}
 			
 			if ($this->getDescription()) {
				print "\n<p>".$this->getDescription()."</p>";
				print "\n<hr/>";
			}
 			
 			// DownLoad bar
	 		print "\n<div>";
	 		print $this->getDownloadBar();
	 		print "</div>";
	 		
	 		if ($this->shouldShowControls()) {
// 				print "\n</div>";
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a href=".$this->url(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
				
 		}
 		
 		return ob_get_clean();
 	}
 	
 	
	
	/**
	 * Answer the download bar.
	 * 
	 * @return string
	 * @access public
	 * @since 4/25/07
	 */
	function getDownloadBar () {
		ob_start();
// 		printpre($this->getContent());
		
		$asset =& $this->getMediaAsset();
		if ($asset) {
			$harmoni =& Harmoni::instance();
			$harmoni->request->StartNamespace('polyphony-repository');
			$thumbnailUrl =  $harmoni->request->quickURL("repository", "viewthumbnail", 
				array(
				"repository_id" => $this->getMediaRepositoryId(),
				"asset_id" => $this->getMediaAssetId(),
				"record_id" => $this->getMediaRecordId()));
			$fileUrl = $harmoni->request->quickURL("repository", "viewfile", 
				array(
				"repository_id" => $this->getMediaRepositoryId(),
				"asset_id" => $this->getMediaAssetId(),
				"record_id" => $this->getMediaRecordId()));
			$harmoni->request->endNamespace();
				
			print "\n";			
			
			print "\n<div>";
			
			print "\n\t<a href='".$fileUrl."'>";
			print "\n\t\t<img src='";
			print $thumbnailUrl;
			print "' align='left' border='0'/>";
			print "\n\t</a>";
			
							
// 			print "\n\t<div>";
// 			print "\n\t\t<strong>".$asset->getDisplayName()."</strong>";
			
			print "\n\t\t<p style='text-align: center;'>";
			print "\n\t\t\t<a href='";
			print $fileUrl;
			print "'>";
			print "<strong>"._("Download this file")."</strong>";
			print "</a>";
// 			print "\n\t\t</p>";
			
			$idManager =& Services::getService("Id");
			$record =& $asset->getRecord($idManager->getId($this->getMediaRecordId()));
			$parts =& $record->getPartsByPartStructure(
				$idManager->getId('FILE_SIZE'));		
			if ($parts->hasNext()) {
				$part =& $parts->next();
				$size = ByteSize::withValue($part->getValue());
				$size = $size->asString();
			} else {
				$size = _("unknown size");
			}
			print "\n\t\t<br/>".$size."</p>";
			
// 			print "\n\t</div>";
			
			print "\n</div>";
			print "\n<div style='clear: both;'>";
			print $this->getCitation($asset);
			print "</div>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer a HTML formatted Citation
	 * 
	 * @param object Asset $asset
	 * @return string
	 * @access public
	 * @since 4/25/07
	 */
	function getCitation (&$asset) {
		ob_start();
		
		$val = $this->getDcValue($asset, "dc.creator");
		if ($val) {			
			print $val->asString();
			print '. ';
		}
		
		$val = $this->getDcValue($asset, "dc.title");
		if ($val) {
			print '"';
			print $val->asString();
			print '" ';
		}
		
		$val = $this->getDcValue($asset, "dc.source");
		if ($val) {
			print '<em>';
			print $val->asString();
			print '</em>. ';
		}
		
		$val = $this->getDcValue($asset, "dc.publisher");
		if ($val) {
			print '';
			print $val->asString();
			print ', ';
		}
		
		$val = $this->getDcValue($asset, "dc.date");
		if ($val) {
			print '';
			print $val->year();
			print ' ';
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the first value from a Dublin Core record for an asset
	 * 
	 * @param object Record $dcRecord
	 * @param string $partId
	 * @return object SObject The primitive object for the value
	 * @access public
	 * @since 4/25/07
	 */
	function getDcValue (&$asset, $partIdString) {
		$idManager =& Services::getService("Id");
		
		$values =& $asset->getPartValuesByPartStructure(
			$idManager->getId($partIdString));
		
		if ($values->hasNext()) {
			return $values->next();
		}
		
		return null;
	}
	
	/**
	 * Answer the media Asset
	 * 
	 * @return object Asset
	 * @access public
	 * @since 4/25/07
	 */
	function &getMediaAsset () {
		if ($this->getContent()) {
			$idManager =& Services::getService("Id");
			$repositoryManager =& Services::getService("Repository");
						
			$repository =& $repositoryManager->getRepository(
				$idManager->getId($this->getMediaRepositoryId()));
			$assetId =& $idManager->getId($this->getMediaAssetId());
			
			if ($repository->assetExists($assetId)) {
				$asset =& $repository->getAsset($assetId);
				return $asset;
			}	
		}
		
		$null = null;
		return $null;
	}
	
/*********************************************************
 * Methods for data encoding
 *********************************************************/
	
	/**
 	 * Store the repository, asset, and record ids
 	 * 
 	 * @param string $repositoryId
 	 * @param string $assetId
 	 * @param string $recordId
 	 * @return void
 	 * @access public
 	 * @since 4/25/07
 	 */
 	function saveIds ($repositoryId, $assetId, $recordId) {
 		$this->setContent($repositoryId."\n".$assetId."\n".$recordId);
 	}
 	
 	/**
 	 * Answer the repository id
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 4/25/07
 	 */
 	function getMediaRepositoryId () {
 		if ($this->getContent()) {
 			$info = explode("\n", $this->getContent());
 			return $info[0];
 		} else {
 			return "";
 		}
 	}
 	
 	/**
 	 * Answer the asset id
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 4/25/07
 	 */
 	function getMediaAssetId () {
 		if ($this->getContent()) {
 			$info = explode("\n", $this->getContent());
 			return $info[1];
 		} else {
 			return "";
 		}
 	}
 	
 	/**
 	 * Answer the record id
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 4/25/07
 	 */
 	function getMediaRecordId () {
 		if ($this->getContent()) {
 			$info = explode("\n", $this->getContent());
 			return $info[2];
 		} else {
 			return "";
 		}
 	}
}

?>