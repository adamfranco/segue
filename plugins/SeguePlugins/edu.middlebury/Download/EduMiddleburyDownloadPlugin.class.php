<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.19 2008/03/18 17:32:12 adamfranco Exp $
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
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.19 2008/03/18 17:32:12 adamfranco Exp $
 */
class EduMiddleburyDownloadPlugin
	extends SegueAjaxPlugin
// 	extends SeguePlugin
{
	/**
 	 * Answer a description of the the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	static function getPluginDescription () {
 		return _("The Download plugin allows you to chose a file-for-download and have a link to it displayed in a bar with a citation and a custom description. Use this plugin with audio files for creating podcasts.");
 	}
 	
 	/**
 	 * Answer a display name for the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginDisplayName () {
 		return _("File For Download");
 	}
 	
 	/**
 	 * Answer an array of the creators of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return array of strings
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginCreators () {
 		return array("Adam Franco");
 	}
 	
 	/**
 	 * Answer the version of the plugin.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersion () {
 		return '1.0';
 	}
 	
 	/**
 	 * Answer the latest version of the plugin available. Null if no version information
 	 * is available.
 	 * 
 	 * @return mixed a string or null
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersionAvailable () {
 		return null;
 	}
		
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
 			$this->setContent($this->getFieldValue('file_id'));
 			$this->setRawDescription($this->tokenizeLocalUrls($this->getFieldValue('description')));
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
 			
 			print "\n\t<input name='".$this->getFieldName('file_id')."' type='hidden' value=\"".$this->getContent()."\"/>";
 			 			
 			// Description
 			print "\n\t<textarea name='".$this->getFieldName('description')."' rows='5' cols='40'>".$this->cleanHTML($this->untokenizeLocalUrls($this->getRawDescription()))."</textarea>";
 			
 			print $this->getWikiHelp();
 			
 			// Select File button
 			print "\n\t<br/><br/><input type='button' value='"._('Select File')."' onclick=\"";
 			print "this.onUse = function (mediaFile) { ";
 			
 			print 		"this.form.elements['".$this->getFieldName('file_id')."'].value = mediaFile.getId(); ";
 			
 			print		"var downloadBar = document.createElement('div'); ";
 			print 		"var link = downloadBar.appendChild(document.createElement('a')); ";
 			print 		"link.href = mediaFile.getUrl().escapeHTML(); ";
 			print		"link.title = mediaFile.getFilename().escapeHTML(); ";
 			
 			print		"var img = link.appendChild(document.createElement('img')); ";
 			print		"img.src = mediaFile.getThumbnailUrl(); ";
 			print		"img.align = 'left'; ";
 			print		"img.border = '0'; ";
 			print 		"img.alt = mediaFile.getTitles()[0]; ";
 			
 			print		"var downloadDiv = downloadBar.appendChild(document.createElement('div')); ";
 			print		"downloadDiv.style.textAlign = 'center'; ";
 			print		"var download = downloadDiv.appendChild(document.createElement('a')); ";
 			print 		"download.innerHTML = '"._("Download this file")."'; ";
 			print		"download.style.fontWeight = 'bold'; ";
 			print		"download.href = mediaFile.getUrl(); ";
 			print		"downloadDiv.appendChild(document.createElement('br')); ";
 			print		"downloadDiv.appendChild(document.createTextNode(mediaFile.getSize())); ";
 			
 			print		"var citation = downloadBar.appendChild(document.createElement('div')); ";
 			print		"citation.style.clear = 'both'; ";
 			print 		"mediaFile.writeCitation(citation); ";
 			
 			print 		"this.nextSibling.innerHTML = '<div>' + downloadBar.innerHTML + '<div style=\\'clear: both;\\'></div></div>'; ";
 			print "}; "; 
 			print "MediaLibrary.run('".$this->getId()."', this); ";
 			print "\"/>";
 			
 			// Container for example download bar.
 			print "<div>".$this->getDownloadBar()."</div>";
 			
 			
 			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";

 			
			print "\n</form>";
 		} else if ($this->canView()) {
//  			if ($this->shouldShowControls()) {
// 				print "\n<div onclick=".$this->url(array('edit' => 'true')).">";
//  			}
 			
 			if ($this->getRawDescription()) {
				print "\n<p>".$this->cleanHTML($this->parseWikiText($this->untokenizeLocalUrls($this->getRawDescription())))."</p>";
				print "\n<hr/>";
			}
 			
 			// DownLoad bar
	 		print "\n<div>";
	 		print $this->getDownloadBar();
	 		print "</div>";
	 		
	 		if ($this->shouldShowControls()) {
// 				print "\n</div>";
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._("edit")."</a>";
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
		
		$file = $this->getMediaFile();
		if ($file) {
			print "\n";	

			print "\n\t\t<div style='float: right; margin-top: 10px;'>";
			print "\n\t\t<img src='".MYPATH."/images/downarrow.gif' align='top' width='15' height='15' alt='"._('download')."'/>";
			print "\n\t\t\t<a href='";
			print $file->getUrl();
			print "'>";
			print "<strong>"._("Download")."</strong>";
			print "</a>";
			
			$size = $file->getSize();
			
			if ($size->value()) {
				$sizeString = $size->asString();
			} else {
				$sizeString = _("unknown size");
			}
			print "\n\t\t(".$sizeString.")";
			print "\n\t</div>";	


			print "\n<div style='float: left;'>";			
			print "\n\t<a href='".$file->getUrl()."'>";
			print "\n\t\t<img src='";
			print $file->getThumbnailUrl();
			print "' align='bottom' border='0' width='32' height='32' alt=\""._("Download '");
			print str_replace('"', "'", strip_tags($file->getTitle()));
			print "'\"/>";
			print "\n\t</a>";
			print "\n</div>";
			
			print "<div style='clear: both; margin-bottom: 6px;'>";
			print $this->getCitation($file);
			print "\n</div>";
			
			

			
		} else {
			print "\n<div class='plugin_empty'>";
			print _("No file has been selected yet. ");
			if ($this->shouldShowControls()) {
				print "<br/>"._("Click the 'edit' link to choose a file. ");
			}
			print "</div>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer a HTML formatted Citation
	 * 
	 * @param object MediaFile $mediaFile
	 * @return string
	 * @access public
	 * @since 4/25/07
	 */
	function getCitation ($mediaFile) {
		ob_start();

		if ($mediaFile->getCreator()) {			
			print $mediaFile->getCreator();
			print '. ';
		}
		
		if ($mediaFile->getTitle()) {			
			print '"';
			print $mediaFile->getTitle();
			print '" ';
		}
		
		if ($mediaFile->getSource()) {			
			print '<em>';
			print $mediaFile->getSource();
			print '</em>. ';
		}
		
		if ($mediaFile->getPublisher()) {
			print '';
			print $mediaFile->getPublisher();
			print ', ';
		}
		
		if ($mediaFile->getDate()) {
			$date = $mediaFile->getDate();
			print '';
			print $date->year();
			print ' ';
		}
		
		return $this->cleanHTML(ob_get_clean());
	}
	
	/**
	 * Answer the media file
	 * 
	 * @return object MediaFile
	 * @access public
	 * @since 4/25/07
	 */
	function getMediaFile () {
		if (!isset($this->_mediaFile)) {
			try {
				if ($this->getContent())
					$this->_mediaFile = MediaFile::withIdString($this->getContent());				
				else
					return null;
			} catch (InvalidArgumentException $e) {
				HarmoniErrorHandler::logException($e, 'Segue');
				return null;
			} catch (UnknownIdException $e) {
				HarmoniErrorHandler::logException($e, 'Segue');
				return null;
			}
		}
		
		return $this->_mediaFile;
	}
	
	/**
 	 * Answer a block of HTML with help about WikiLinking
 	 *
 	 * @return string
 	 * @access private
 	 * @since 12/4/07
 	 */
 	private function getWikiHelp () {
 		ob_start();
 		print "\n<div class='help_text'>";
 		$message = _('<strong>Wiki linking (%1) :</strong> To link to a page on your site whose title is "Introduction" use &#91;&#91;Introduction&#93;&#93;. If no content with the title "Introduction" exists a link to create such content will be made. To see all titles used in this site, see: %2');
 		$message = str_replace('%1', Help::link('wiki linking'), $message);
//  		$message = str_replace('%2', SiteMap::link($this->getId()), $message);
		$message = str_replace('%2', 'Site Map', $message);
 		print $message;
 		print "\n</div>";
 		return ob_get_clean();
 	}
 	
 	/*********************************************************
 	 * The following methods are needed to support restoring
 	 * from backups and importing/exporting plugin data.
 	 *********************************************************/
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings.
 	 * Update any of the old Ids that this plugin instance recognizes to their
 	 * new value.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
 	public function replaceIds (array $idMap) {
 		// Update the media-file mapping
 		$this->setContent(MediaFile::getMappedIdString($idMap, $this->getContent()));
 		unset($this->_mediaFile);
 		
 		// Update any ids in the description HTML.
 		$this->setRawDescription($this->replaceIdsInHtml($idMap, $this->getRawDescription()));
 	}
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings.
 	 * Update any of the old Ids in ther version XML to their new value.
 	 * This method is only needed if versioning is supported.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @param object DOMDocument $version
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
 	public function replaceIdsInVersion (array $idMap, DOMDocument $version) {
 		throw new UnimplementedException();
 	}
}

?>