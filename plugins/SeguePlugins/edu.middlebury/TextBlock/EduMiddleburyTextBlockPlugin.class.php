<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.48 2008/03/25 19:41:45 adamfranco Exp $
 */
 
require_once(POLYPHONY_DIR."/javascript/fckeditor/fckeditor.php");

/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.48 2008/03/25 19:41:45 adamfranco Exp $
 */
class EduMiddleburyTextBlockPlugin
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
 		return _("The Text Block is a unit of HTML-formatted text that may contain inline images, links, and formatting.");
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
 		return _("Text Block");
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
		if (isset($_SESSION[$this->getId()."_textEditor"]))
			$this->textEditor = $_SESSION[$this->getId()."_textEditor"];
		else
	 		$this->textEditor = 'fck';
	 	
		$this->editing = false;
		$this->workingContent = null;
		$this->workingAbstractLength = null;
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
 		if ($this->getFieldValue('edit'))
	 		$this->editing = true;
	 	
 		if ($this->getFieldValue('submit_pressed')) {	
 			$this->setContent($this->tokenizeLocalUrls($this->cleanHTML($this->getFieldValue('content'))));
 			$this->setRawDescription(intval($this->getFieldValue('abstractLength')));
 			$this->logEvent('Modify Content', 'TextBlock content updated');
 			
 			if ($this->getFieldValue('comment') && $this->getFieldValue('comment') != $this->getCommentText())
 				$this->markVersion($this->getFieldValue('comment'));
 			else
	 			$this->markVersion();
	 		
 		} else if ($this->getFieldValue('editor')) {
			$this->textEditor = $this->getFieldValue('editor');
			$_SESSION[$this->getId()."_textEditor"] = $this->textEditor;
			
			$this->editing = true;
			$this->workingContent = $this->cleanHTML($this->getFieldValue('content'));
			$this->workingAbstractLength = intval($this->getFieldValue('abstractLength'));
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
 		 		
 		if ($this->editing && $this->canModify()) {
			$this->printEditForm();
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div onclick='if (event.shiftKey) { ".$this->locationSend(array('edit' => 'true'))."}'>";
 			}
 			
 			if ($this->hasContent()) {
				$abstractLength = intval($this->getRawDescription());
				if ($abstractLength) {
					print "\n".$this->trimHTML($this->parseWikiText($this->getContent()), $abstractLength);
				} else {
					print "\n".$this->cleanHTML($this->parseWikiText($this->getContent()));
				}
			} else {
				print "\n<div class='plugin_empty'>";
				print _("No text has been added yet. ");
				if ($this->shouldShowControls()) {
					print "<br/>"._("Click the 'edit' link to add content. ");
				}
				print "</div>";
			}
		 	
	 		
	 		if ($this->shouldShowControls()) {
				print "\n</div>";
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
				
 		}
 		
 		return ob_get_clean();
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	function getExtendedMarkup () {
 		ob_start();
 		
 		if ($this->editing && $this->canModify()) {
			$this->printEditForm();
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div onclick='if (event.shiftKey) { ".$this->locationSend(array('edit' => 'true'))."}'>";
 			}
 			if ($this->hasContent()) {
		 		print "\n".$this->cleanHTML($this->parseWikiText($this->getContent()));
	 		} else {
				print "\n<div class='plugin_empty'>";
				print _("No text has been added yet. ");
				if ($this->shouldShowControls()) {
					print "<br/>"._("Click the 'edit' link to add content. ");
				}
				print "</div>";
			}
	 		
	 		if ($this->shouldShowControls()) {
				print "\n</div>";
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
				
 		}
 		
 		return ob_get_clean();
 	}
 	
 	/**
 	 * Generate a plain-text or HTML description string for the plugin instance.
 	 * This may simply be a stored 'raw description' string, it could be generated
 	 * from other content in the plugin instance, or some combination there-of.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/22/07
 	 */
 	function generateDescription () {
 		return $this->trimHTML($this->getMarkup(), 50);
 	}
 	
 	 	/**
 	 * Print out the editing form
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 5/23/07
 	 */
 	function printEditForm () {
 		print "\n".$this->formStartTagWithAction();
 		
 		//add editor select
		print "\n\t<div align='right'>Current Editor: <select name='".$this->getFieldName('editor')."' onchange='";
		// FCK editor is getting in the way of form submission and not triggering an onsubmit event when the form
		// is submitted via this.form.submit().
		print ' if (this.form.onsubmit) {this.form.onsubmit(); this.form.submit();} else {this.form.submit();} ';
		print "'>";
		print "\n\t<option value='fck'".(($this->textEditor=='fck')?" selected='selected'":"").">Rich-Text Editor</option>";
		print "\n\t<option value='none'".(($this->textEditor=='none')?" selected='selected'":"").">None</option>";
		print "\n\t</select></div>";

 		// replace with editor code
 		$this->printEditor();
	
		print $this->getWikiHelp();

		print "\n\t<br/>";
		if (is_null($this->workingAbstractLength))
			$length = intval($this->getRawDescription());
		else
			$length = intval($this->workingAbstractLength);
		print str_replace('%1',
			"<input name='".$this->getFieldName('abstractLength')."' type='text' value='".$length."' onchange='return false;' size='3'/>",
			_("Abstract to %1 words. (Enter '0' for no abstract)"));
		
		print "\n\t<br/>";
		print "\n\t<br/>";
		print "\n\t<input type='text' name='".$this->getFieldName('comment')."' size='80' value='".$this->getCommentText()."' ";
		print "style='color: #999;' ";
		print "onfocus=\"if (this.value == this.nextSibling.innerHTML) { this.value = ''; this.style.color = '#000'; }\" ";
		print "onblur=\"if (this.value == '') { this.value = this.nextSibling.innerHTML; this.style.color = '#999'; }\" ";
		print "/>";
		print "<div style='display: none;'>".$this->getCommentText()."</div>";
		
		print "\n\t<br/>";
		
		print "\n\t<input type='hidden' value='' name='".$this->getFieldName('submit_pressed')."'/>";
		print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."' onclick='this.form.elements[\"".$this->getFieldName('submit_pressed')."\"].value = \"true\"; '/>";
		
		print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";
				
		print "\n</form>";
 	}
 	
 	/**
 	 * Answer the Comment help text
 	 * 
 	 * @return string
 	 * @access private
 	 * @since 1/9/08
 	 */
 	private function getCommentText () {
 		return _("Add a comment about your changes here.");
 	}
 	
 	/**
 	 * Get the editor specified by this->textEditor
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 8/22/07
 	 */
 	function printEditor () {
		if ($this->textEditor == "none") {
			$this->printTextField();
		} else if ($this->textEditor == "fck") {
			$this->printFckEditor();
		} else {
			throw new Exception("Supplied editor, '".$this->textEditor."', is not valid.");
		}
 	}
 	
 	 	/**
 	 * Print out a text field
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 8/27/07
 	 */
 	function printTextField () {
 		print "\n\t<textarea name='".$this->getFieldName('content')."' rows='20' style='width: 100%;'>";
 		if (is_null($this->workingContent))
	 		print $this->cleanHTML($this->untokenizeLocalUrls($this->getContent()));
	 	else
	 		print $this->workingContent;
 		print "</textarea>";
 		
		// Image button
		print "\n\t<br/><input type='button' value='"._('Add Image')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print 		"var title = mediaFile.getTitles()[0]; ";
		print		"if (title) { title = title.escapeHTML(); }; ";
		print 		"var newString = '\\n<img src=\'' + mediaFile.getUrl().escapeHTML() + '\' title=\'' + title + '\'/>' ; ";
		print 		"edInsertContent(this.form.elements['".$this->getFieldName('content')."'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/>";
		
		// File button
		print "\n\t<input type='button' value='"._('Add File')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print		"var downloadBar = document.createElement('div'); ";
		print 		"var link = downloadBar.appendChild(document.createElement('a')); ";
		print 		"link.href = mediaFile.getUrl().escapeHTML(); ";
		
		print 		"link.title = mediaFile.getTitles()[0]; ";
		print		"if (link.title) { link.title = link.title.escapeHTML(); }; ";
		
		print		"var img = link.appendChild(document.createElement('img')); ";
		print		"img.src = mediaFile.getThumbnailUrl(); ";
		print		"img.align = 'left'; ";
		print		"img.border = '0'; ";
		
		print		"var title = downloadBar.appendChild(document.createElement('div')); ";
		print 		"title.innerHTML = mediaFile.getTitles()[0]; ";
		print		"title.fontWeight = 'bold'; ";
		
		print		"var citation = downloadBar.appendChild(document.createElement('div')); ";
		print 		"mediaFile.writeCitation(citation); ";
		
		print 		"var newString = '<div>' + downloadBar.innerHTML + '<div style=\'clear: both;\'></div></div>'; ";
		print 		"edInsertContent(this.form.elements['".$this->getFieldName('content')."'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/><br/>";
		
 	}

 	/**
 	 * Get fckeditor specified by this->textEditor
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 8/22/07
 	 */
 	function printFckEditor () {
		$oFCKeditor = new FCKeditor($this->getFieldName('content'));
			
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

		
		if (is_null($this->workingContent))
	 		$oFCKeditor->Value = $this->cleanHTML($this->untokenizeLocalUrls($this->getContent()));
	 	else
	 		$oFCKeditor->Value = $this->workingContent;
	 	
		$oFCKeditor->Height		= '400' ;
//		$oFCKeditor->Width		= '400' ;
		$oFCKeditor->ToolbarSet		= 'ContentBlock' ;
		
		$oFCKeditor->Create() ;
 	}
 	
 	/*********************************************************
 	 * The following three methods allow plugins to work within
 	 * the "Segue Classic" user interface.
 	 *
 	 * If plugins do not support the wizard directly, then their
 	 * markup with 'show controls' enabled will be put directly 
 	 * in the wizard.
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin natively supports editing via wizard components.
 	 * Override to return true if you implement the getWizardComponent(), 
 	 * and updateFromWizard() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 5/9/07
 	 */
 	function supportsWizard () {
 		return true;
 	}
 	/**
 	 * Return the a {@link WizardComponent} to allow editing of your
 	 * plugin in the Wizard.
 	 * 
 	 * @return object WizardComponent
 	 * @access public
 	 * @since 5/8/07
 	 */
 	function getWizardComponent () {
 		$wrapper = new WComponentCollection;
 		$harmoni = Harmoni::instance();
 		ob_start();
 		 
 		$property = $wrapper->addComponent('comment', new WTextField);
 		$property->setSize(80);
 		$property->setStartingDisplayText(_("Add a comment about your changes here."));
 		
 		$property = $wrapper->addComponent('content', HtmlTextArea::withRowsAndColumns(20, 80));
 		$property->setValue($this->cleanHTML($this->untokenizeLocalUrls($this->getContent())));
 		$property->chooseEditor('fck');
 		
 		$fckArea = $property->getEditor('fck');
 		$fckArea->setOption('ToolbarSet', 'ContentBlock');
 		$fckArea->setConfigOption('CustomConfigurationsPath', MYPATH.'/javascript/fck_custom_config.js');
 		$fckArea->setConfigOption('ImageBrowserWindowWidth', '700');
 		$fckArea->setConfigOption('ImageBrowserWindowHeight', '600');
 		
 		$fckTextArea = $property->getEditor('fck');
 		$harmoni->request->startNamespace('media');
 		$fckTextArea->enableFileBrowsingAtUrl(
 			$harmoni->request->quickURL('media', 'filebrowser', array('node' => $this->getId())));
 		$harmoni->request->endNamespace();
 		
 		$property->addPostHtml('none', $this->getImageAndFileButtons());
 		
 		print "[[content]]";
 		
 		print $this->getWikiHelp();
		
		$property = $wrapper->addComponent('abstractLength', new WTextField);
 		$property->setSize(3);
 		$property->setValue($this->getRawDescription());
		
		print "\n\t<br/>";
		print _("Abstract to [[abstractLength]] words. (Enter '0' for no abstract)");
		
		print "\n\t<br/>";
		print "\n\t<br/>";
		print "[[comment]]";
 		
 		$wrapper->setContent(ob_get_clean());
 		return $wrapper;
 	}
 	
 	/**
 	 * Answer the image and file buttons used by the plain-text editor.
 	 * 
 	 * @return string
 	 * @access private
 	 * @since 1/14/08
 	 */
 	private function getImageAndFileButtons () {
 		ob_start();
 		// Image button
 		print "<br/>";
		print "\n\t<input type='button' value='"._('Add Image')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print 		"var title = mediaFile.getTitles()[0]; ";
		print		"if (title) { title = title.escapeHTML(); }; ";
		print 		"var newString = '\\n<img src=\'' + mediaFile.getUrl().escapeHTML() + '\' title=\'' + title + '\'/>' ; ";
		print 		"edInsertContent(this.form.elements['[[fieldname:]]'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/>";
		
		// File button
		print "\n\t<input type='button' value='"._('Add File')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print		"var downloadBar = document.createElement('div'); ";
		print 		"var link = downloadBar.appendChild(document.createElement('a')); ";
		print 		"link.href = mediaFile.getUrl().escapeHTML(); ";
		print 		"link.title = mediaFile.getTitles()[0]; ";
		print		"if (link.title) { link.title = link.title.escapeHTML(); }; ";
		
		print		"var img = link.appendChild(document.createElement('img')); ";
		print		"img.src = mediaFile.getThumbnailUrl(); ";
		print		"img.align = 'left'; ";
		print		"img.border = '0'; ";
		
		print		"var title = downloadBar.appendChild(document.createElement('div')); ";
		print 		"title.innerHTML = mediaFile.getTitles()[0]; ";
		print		"title.fontWeight = 'bold'; ";
		
		print		"var citation = downloadBar.appendChild(document.createElement('div')); ";
		print 		"mediaFile.writeCitation(citation); ";
		
		print 		"var newString = '<div>' + downloadBar.innerHTML + '<div style=\'clear: both;\'></div></div>'; ";
		print 		"edInsertContent(this.form.elements['[[fieldname:]]'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/>";
		return ob_get_clean();
 	}
 	/**
 	 * Update the component from an array of values
 	 * 
 	 * @param mixed string or array $values
 	 * @return void
 	 * @access public
 	 * @since 5/8/07
 	 */
 	function updateFromWizard ( $values ) {
 		$this->setContent($this->tokenizeLocalUrls($values['content']));
 		$this->setRawDescription(intval($values['abstractLength']));
 		$this->logEvent('Modify Content', 'TextBlock content updated');
 		$this->markVersion($values['comment']);
 	}
 	
 	/**
 	 * Answer true if this instance of a plugin 'has content'. This method is called
 	 * to determine if the plugin instance is ready to be 'published' or is a newly-created
 	 * placeholder awaiting content addition. If the plugin has no appreciable 
 	 * difference between have content or not, this method should return true. For
 	 * example: an interactive calendar plugin should probably be 'published' 
 	 * whether or not events have been added to it.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 7/13/07
 	 */
 	function hasContent () {
 		if (strlen($this->getContent()) > 0)
	 		return true;
	 	else
	 		return false;
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
 	 * The following methods are used to support versioning of
 	 * the plugin instance
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin supports versioning. 
 	 * Override to return true if you implement the exportVersion(), 
 	 * and applyVersion() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function supportsVersioning () {
 		return true;
 	}
 	
 	/**
 	 * Answer a DOMDocument representation of the current plugin state.
 	 *
 	 * @return DOMDocument
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function exportVersion () {
 		$doc = new DOMDocument;
 		$version = $doc->appendChild($doc->createElement('version'));
 		
 		$content = $version->appendChild($doc->createElement('content'));
 		$content->appendChild($doc->createCDATASection($this->tokenizeLocalUrls($this->getContent())));
 		
 		$version->appendChild($doc->createElement('abstractLength', $this->getRawDescription()));
 		
 		return $doc;
 	}
 	
 	/**
 	 * Update the plugin state to match the representation passed in the DOMDocument.
 	 * The DOM Element passed will have been exported using the exportVersion() method.
 	 *
 	 * Do not mark a new version in the implementation of this method. If necessary this
 	 * will be done by the driver.
 	 * 
 	 * @param object DOMDocument $version
 	 * @return void
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function applyVersion (DOMDocument $version) {
 		$this->setContent($this->tokenizeLocalUrls($this->getContentFromVersion($version)));
 		$this->setRawDescription($this->getAbstractLengthFromVersion($version));
 	}
 	
 	/**
 	 * Answer a string of XHTML markup that displays the plugin state representation
 	 * in the DOMDocument passed. This markup will be used in displaying a version history.
 	 * The DOM Element passed will have been exported using the exportVersion() method.
 	 * 
 	 * @param object DOMDocument $version
 	 * @return string
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function getVersionMarkup (DOMDocument $version) {
 		ob_start();
 		$content = $this->getContentFromVersion($version);
 		$abstractLength = $this->getAbstractLengthFromVersion($version);
 		if ($abstractLength) {
 			print "\n<div>";
 			print $this->trimHTML($this->parseWikiText($content), $abstractLength);
 			print "\n</div>";
 			print "\n<hr/>";
 		}
 		
 		print "\n<div>";
		print $this->cleanHTML($this->parseWikiText($content));
		print "\n</div>";
		
		return ob_get_clean();
 	}
 	
 	/**
 	 * Answer a difference between two versions. Should return an XHTML-formatted
 	 * list or table of differences.
 	 * 
 	 * @param object DOMDocument $oldVersion
 	 * @param object DOMDocument $newVersion
 	 * @return string
 	 * @access public
 	 * @since 1/7/08
 	 */
 	public function getVersionDiff (DOMDocument $oldVersion, DOMDocument $newVersion) {
 		$oldContent = $this->getContentFromVersion($oldVersion);
//  		$abstractLength = $this->getAbstractLengthFromVersion($oldVersion);
//  		if ($abstractLength) {
//  			$oldAbstract = $this->trimHTML($this->parseWikiText($content), $abstractLength);
//  		} else {
//  			$oldAbstract = '';
//  		}
 		
 		$newContent = $this->getContentFromVersion($newVersion);
//  		$abstractLength = $this->getAbstractLengthFromVersion($newVersion);
//  		if ($abstractLength) {
//  			$newAbstract = $this->trimHTML($this->parseWikiText($content), $abstractLength);
//  		} else {
//  			$newAbstract = '';
//  		}
 		
 		return $this->getDiff(explode("\n", $oldContent), explode("\n", $newContent));
 	}
 	
 	/**
 	 * Answer the content string
 	 * 
 	 * @param object DOMDocument $version
 	 * @return string
 	 * @access private
 	 * @since 1/4/08
 	 */
 	private function getContentFromVersion (DOMDocument $version) {
 		return $this->getContentElementFromVersion($version)->nodeValue;
  	}
  	
  	/**
  	 * Answer the content element of a version
  	 * 
  	 * @param object DOMDocument $version
  	 * @return DOMElement
  	 * @access private
  	 * @since 1/25/08
  	 */
  	private function getContentElementFromVersion (DOMDocument $version) {
  		// Content
 		$contentElements = $version->getElementsByTagName('content');
 		if (!$contentElements->length)
 			throw new InvalidVersionException("Missing 'content' element.");
 		if ($contentElements->length > 1)
 			throw new InvalidVersionException("Too many 'content' elements, should be 1.");
 		
 		$contentElement = $contentElements->item(0);
 		$content = $contentElement->firstChild;
 		if ($content) {
 			if ($content->nodeType == XML_CDATA_SECTION_NODE) {
				return $content;
			} else {
				throw new InvalidVersionException("The 'content' element should contain one CDATA section.");
			}
		}
		// if empty, append an empty CDATA Section
		else {
			return $contentElement->appendChild($version->createCDATASection(''));
		}

  	}
 	
 	/**
 	 * Answer the abstract length from the DOMDocument
 	 * 
 	 * @param object DOMDocument $version
 	 * @return int
 	 * @access private
 	 * @since 1/4/08
 	 */
 	private function getAbstractLengthFromVersion (DOMDocument $version) {
 		// Abstract Length
 		$abstractElements = $version->getElementsByTagName('abstractLength');
 		if (!$abstractElements->length)
 			throw new InvalidVersionException("Missing 'abstractLength' element.");
 		if ($abstractElements->length > 1)
 			throw new InvalidVersionException("Too many 'abstractLength' elements, should be 1.");
 		$abstractElement = $abstractElements->item(0);
 		$abstract = $abstractElement->firstChild;
 		if (!$abstract)
 			return 0;
 		if ($abstract->nodeType == XML_TEXT_NODE) {
 			return intval($abstract->nodeValue);
		} else {
			throw new InvalidVersionException("The 'abstractLength' element should contain a string.");
		}
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
 		$this->setContent($this->replaceIdsInHtml($idMap, $this->getContent()));
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
 		$contentElement = $this->getContentElementFromVersion($version);
 		$contentElement->nodeValue = $this->replaceIdsInHtml($idMap, $contentElement->nodeValue);
 	}
}

?>
