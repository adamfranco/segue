<?php
/**
 * @since 8/1/2008
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyAudioPlayerPlugin.class.php,v 1.19 2008/03/18 17:32:12 adamfranco Exp $
 */

/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 8/1/2008
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyAudioPlayerPlugin.class.php,v 0.5 2008/03/18 17:32:12 davidfouhey Exp $
 */
class EduMiddleburyAudioPlayerPlugin
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
 		return _("The audio player plugin allows you to upload a clip that can be played in a user's browser.");
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
 		return _("Audio Clip");
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
 		return array("David Fouhey");
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
 		return '0.1';
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
 		$this->doc = new Harmoni_DOMDocument;
		$this->doc->preserveWhiteSpace = false;
		if (strlen($this->getContent())) {
			try {
				$this->doc->loadXML($this->getContent());
			} catch (DOMException $e) {
				$this->doc->loadXML("<AudioPlayerPlugin></AudioPlayerPlugin>");
			}
	 	} else
	 		$this->doc->loadXML("<AudioPlayerPlugin></AudioPlayerPlugin>");
	 			
 		$this->xpath = new DOMXPath($this->doc);
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
 			$this->setFileId($this->getFieldValue('file_id'));
 			$this->setRawDescription($this->tokenizeLocalUrls($this->getFieldValue('description')));
 			$this->setShowDownloadLink(($this->getFieldValue('show_download_link') == 'true')?true:false);
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
 			
 			print "\n\t<input name='".$this->getFieldName('file_id')."' type='hidden' value=\"".$this->getFileId()."\"/>";
 			
 			// Select File button
 			print "\n\t<h3>"._("File:")."</h3>";
 			print "\n\t<input type='button' value='"._('Select MP3 File')."' onclick=\"";
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
 			print		"downloadDiv.style.textAlign = 'right'; ";
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
 			print "MediaLibrary.run('".$this->getId()."', this, ['audio/mpeg', 'audio/mpg']); ";
 			print "\"/>";
 			
 			// Container for example download bar.
 			print "<div style='margin-top: 10px;'>".$this->getDownloadBar()."</div>";
 			
 			// Download link
 			print "\n\t<h3>"._("Options:")."</h3>";
 			print "\n\t<div>";
 			print "\n\t\t<input name='".$this->getFieldName('show_download_link')."' type='checkbox' value='true'";
 			if ($this->showDownloadLink()) 
 				print " checked='checked'";
 			print "/> "._("Show Download Link");
 			print "\n\t</div>";
 			
 			// Description
 			print "\n\t<h3>"._("Caption:")."</h3>";
 			$this->printFckEditor($this->getFieldName('description'), 
 					$this->applyEditorSafeTextTemplates(
 						$this->cleanHTML($this->untokenizeLocalUrls(
 							$this->getRawDescription()))));
 			
 			print $this->getWikiHelp();
 			
 			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Save')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";

 			
			print "\n</form>";
 		} else if ($this->canView()) {
//  			if ($this->shouldShowControls()) {
// 				print "\n<div onclick=".$this->url(array('edit' => 'true')).">";
//  			}
 			
 			// DownLoad bar
	 		print "\n<div>";
	 		print $this->getDownloadBar();
	 		print "</div>";
	 		
	 		if ($this->getRawDescription()) {
// 				print "\n<hr/>";
				print "\n<div style='margin-top: 10px;'>".$this->cleanHTML($this->parseWikiText($this->untokenizeLocalUrls($this->getRawDescription())))."</div>";
			}
	 		
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
 	 * Answer an array of MediaFiles that should be referenced along with the plugin
 	 * representation in RSS feed enclosures or other similar uses.
 	 *
 	 * Throw an UnimplementedException if not implemented.
 	 * 
 	 * @return array of MediaFile objects
 	 * @access public
 	 * @since 8/27/08
 	 */
 	public function getRelatedMediaFiles () {
 		if ($this->getFileId())
	 		return array(MediaFile::withIdString($this->getFileId()));
	 	else
	 		return array();
 	}
 	
 	/**
 	 * Get fckeditor specified by this->textEditor
 	 * 
 	 * @string $fieldname The field-name to user for the editor
 	 * @string $value The value to display in the editor.
 	 * @return void
 	 * @access public
 	 * @since 8/22/07
 	 */
 	function printFckEditor ($fieldname, $value) {
		$oFCKeditor = new FCKeditor($fieldname);
			
		$oFCKeditor->Config['EnterMode'] = "br";
		$oFCKeditor->Config['ShiftEnterMode'] = "p";
		
		$oFCKeditor->Config['ImageBrowser'] = "true";
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('media');
		$oFCKeditor->Config['ImageBrowserURL'] = str_replace('&amp;', '&', $harmoni->request->quickURL('media', 'filebrowser', array('node' => $this->getId())));
		$harmoni->request->endNamespace();
		$oFCKeditor->Config['ImageBrowserWindowWidth'] = "700";
		$oFCKeditor->Config['ImageBrowserWindowHeight'] = "600";
		
		$oFCKeditor->Config['LinkDlgHideTarget'] = "false";
		$oFCKeditor->Config['LinkDlgHideAdvanced'] = "false";
		
		$oFCKeditor->Config['ImageDlgHideLink'] = "false";
		$oFCKeditor->Config['ImageDlgHideAdvanced'] = "false";
		
		$oFCKeditor->Config['FlashDlgHideAdvanced'] = "false";
		
		
		$oFCKeditor->BasePath	= POLYPHONY_PATH."/javascript/fckeditor/" ;
		
		$oFCKeditor->Config['CustomConfigurationsPath'] = MYPATH.'/javascript/fck_custom_config.js';

		
 		$oFCKeditor->Value = $value;
	 	
		$oFCKeditor->Height		= '300' ;
//		$oFCKeditor->Width		= '400' ;
		$oFCKeditor->ToolbarSet		= 'ContentBlock' ;
		
		$oFCKeditor->Create() ;
		
		// Add an event check on back button to confirm that the user wants to
		// leave with their editor open.
		$string = _("You have edits open. Any changes will be lost.");
		print "
<script type='text/javascript'>
// <![CDATA[ 

		window.addUnloadConfirmationForElement(\"$fieldname\", \"$string\");
	
// ]]>
</script>
";
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
			$playerUrl = $this->getPublicFileUrl("player.swf");
			$url = $file->getUrl();
			$id = $this->getId();
			//the url that comes back from getUrlForFlash contains html entities
			//what we really want to handle the ampersands is url encoding, rather than
			//&amp; That way we get the urls in a way so that flash won't think that the 
			//variables in the url aren't for it, but in such a way that when the request
			//gets sent, the variables will get to the server.
			$flashUrl = urlencode(html_entity_decode($file->getUrlForFlash()));

			print "\n\t<script type='text/javascript' src='".$this->getPublicFileUrl('audio-player.js')."'></script>";

			if ($this->showDownloadLink()) {
				print "\n\t\t<div style='float: right; margin-top: 12px;'>";
				print "\n\t\t<img src='".MYPATH."/images/downarrow.gif' align='top' width='15' height='15' alt='"._('download')."'/>";
				print "\n\t\t\t<a style='text-decoration: none;' href='";
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
				print "\n\t\t<span style='font-size: 90%;'>(".$sizeString.")</span>";
				print "\n\t</div>";	
			}


			print "\n<div style='float: left;'>";			
                        print "\n\t<object width='290' height='24' id='audioplayer{$id}' data='{$playerUrl}' type='application/x-shockwave-flash'>";
                        print "\n\t<param value='{$playerUrl}' name='movie' />";
                        print "<param value='high' name='quality' /><param value='false' name='menu' /><param value='transparent' name='wmode' />\n";   
                        print "<param value='soundFile={$flashUrl}' name='FlashVars' />\n";
                        print "</object>\n";
			print "<br/>\n";
			
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
				if ($this->getFileId())
					$this->_mediaFile = MediaFile::withIdString($this->getFileId());				
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
 	
 	/**
 	 * Answer the file id or null.
 	 * 
 	 * @return mixed string or null
 	 * @access protected
 	 * @since 8/26/08
 	 */
 	protected function getFileId () {
 		$elements = $this->xpath->query('/AudioPlayerPlugin/File/Id');
 		if (!$elements->length)
 			return null;
 		
 		$id = $elements->item(0)->nodeValue;
 		if (strlen($id)) {
 			return str_replace('&amp;', '&', $id);
 		}
 		return null;
 	}
 	
 	/**
 	 * Answer the file id or null.
 	 * 
 	 * @param string $id
 	 * @return void
 	 * @access protected
 	 * @since 8/26/08
 	 */
 	protected function setFileId ($id) {
 		// Reencode ampersands for XML
 		$id = str_replace('&', '&amp;', $id);
 		
 		$fileElements = $this->xpath->query('/AudioPlayerPlugin/File');
 		if ($fileElements->length)
 			$fileElement = $fileElements->item(0);
 		else
 			$fileElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('File'));
 		
 		$idElements = $this->xpath->query('./Id', $fileElement);
 		if ($idElements->length) {
 			$idElement = $idElements->item(0);
 			$idElement->nodeValue = $id;
 		} else
 			$fileElement->appendChild($this->doc->createElement('Id', $id));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
 	}
 	
 	/**
 	 * Answer true if the download link should be shown.
 	 * 
 	 * @return boolean
 	 * @access protected
 	 * @since 8/26/08
 	 */
 	protected function showDownloadLink () {
 		return $this->_getBoolean('show_download_link', false);
 	}
 	
 	/**
 	 * Answer true if the download link should be shown.
 	 * 
 	 * @param boolean $bool
 	 * @return void
 	 * @access protected
 	 * @since 8/26/08
 	 */
 	protected function setShowDownloadLink ($bool) {
 		return $this->_setBoolean('show_download_link', $bool);
 	}
	
	/**
 	 * Answer a boolean option
 	 * 
 	 * @param string $name
 	 * @param optional $default
 	 * @return boolean
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _getBoolean ($name, $default = true) {
 		$elements = $this->xpath->query('/AudioPlayerPlugin/File');
 		if (!$elements->length)
 			return $default;
 		
 		$elem = $elements->item(0);
 		if (!$elem->hasAttribute($name))
 			return $default;
 			
 		if ($elem->getAttribute($name) == 'false')
 			return false;
 		
 		return true;
 	}
 	
 	/**
 	 * Set a boolean option
 	 * 
 	 * @param string $name
 	 * @param boolean $value
 	 * @return void
 	 * @access protected
 	 * @since 6/19/08
 	 */
 	protected function _setBoolean ($name, $value) {
 		$feedElements = $this->xpath->query('/AudioPlayerPlugin/File');
 		if ($feedElements->length)
 			$feedElement = $feedElements->item(0);
 		else
 			$feedElement = $this->doc->documentElement->appendChild(
 				$this->doc->createElement('File'));
 		
 		$feedElement->setAttribute($name, (($value)?'true':'false'));
 		
 		$this->setContent($this->doc->saveXMLWithWhitespace());
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
 		if (strlen(trim($this->getFileId()))) {
	 		$this->setFileId(MediaFile::getMappedIdString($idMap, $this->getFileId()));
 			unset($this->_mediaFile);
 		}
 		
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

