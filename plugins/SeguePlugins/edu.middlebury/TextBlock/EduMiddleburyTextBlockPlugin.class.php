<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.14 2007/08/23 20:10:09 achapin Exp $
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
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.14 2007/08/23 20:10:09 achapin Exp $
 */
class EduMiddleburyTextBlockPlugin
// 	extends SeguePluginsAjaxPlugin
	extends SeguePluginsPlugin
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
 	function getPluginDescription () {
 		return _("The Text Block is a unit of HTML-formatted text that may contain inline images, links, and formatting.");
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
 			
 			if ($this->getFieldValue('editor')) {
 				$this->textEditor = $this->getFieldValue('editor');
 				$_SESSION[$this->getId()."_textEditor"] = $this->textEditor;
 			}
 			
 			$this->setContent($this->cleanHTML($this->getFieldValue('content')));
 			$this->setRawDescription($this->getFieldValue('abstractLength'));
 			$this->logEvent('Modify Content', 'TextBlock content updated');
 		}
 	}
 	
 	/**
 	 * Get the editor specified by this->textEditor
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 8/22/07
 	 */
 	function getEditor () {
		if ($this->textEditor == "none") {
			print "\n\t<textarea name='".$this->getFieldName('content')."' rows='20' cols='50'>".$this->getContent()."</textarea>";
		} else if ($this->textEditor == "fck") {
			$this->getFckEditor();
		} else {
			throw new Exception("Supplied editor, '".$this->textEditor."', is not valid.");
		}
 	}

 	/**
 	 * Get fckeditor specified by this->textEditor
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 8/22/07
 	 */
 	function getFckEditor () {
 		print "load fckeditor...";
 		
 	//	require_once(POLYPHONY_DIR."/javascript/fckeditor_create.php");
 		$harmoni = Harmoni::instance();

// 		$config = <<< END
// FCKConfig.ToolbarSets["simple"] = [
// 	['Source','-','Cut','Copy','Paste','PasteText','PasteWord','RemoveFormat','-'],
// 	['Bold','Italic','Underline','StrikeThrough','-','Subscript','Superscript'],
// 	'/',
// 	['JustifyLeft','JustifyCenter','JustifyRight','JustifyFull'],
// 	['Link','Unlink','Anchor'],
// 	['Image','Flash','Table','Rule','SpecialChar'],
// 	'/',
// 	['TextColor','BGColor'],
// 	['OrderedList','UnorderedList','-','Outdent','Indent'],
// 	['FontFormat','FontName','FontSize','FitWindow','About']
// ] ;
// FCKConfig.EnterMode = 'br' ;			// p | div | br
// FCKConfig.ShiftEnterMode = 'p' ;	// p | div | br
// 
// FCKConfig.LinkDlgHideTarget		= false ;
// FCKConfig.LinkDlgHideAdvanced	= false ;
// 
// FCKConfig.ImageDlgHideLink		= false ;
// FCKConfig.ImageDlgHideAdvanced	= false ;
// 
// FCKConfig.FlashDlgHideAdvanced	= false ;
// 
// var _FileBrowserLanguage	= 'php' ;	// asp | aspx | cfm | lasso | perl | php | py
// var _QuickUploadLanguage	= 'php' ;	// asp | aspx | cfm | lasso | php
// 
// FCKConfig.ImageBrowser = true ;
// FCKConfig.ImageBrowserURL = '$filebrowserUrl?Type=Image&Connector=connectors/' + _FileBrowserLanguage + '/connector.' + _FileBrowserExtension ;
// FCKConfig.ImageBrowserWindowWidth  = 700 ;
// FCKConfig.ImageBrowserWindowHeight = 600 ;
// 		
// 		
// END;
		

		
		$oFCKeditor = new FCKeditor($this->getFieldName('content'));
		
		$oFCKeditor->Config['EnterMode'] = "br";
		$oFCKeditor->Config['ShiftEnterMode'] = "p";
		
		$oFCKeditor->Config['ImageBrowser'] = "true";
		$oFCKeditor->Config['ImageBrowserURL'] = str_replace('&amp;', '&', $harmoni->request->quickURL('fckeditor', 'filebrowser', array('node' => $this->getId())));
		$oFCKeditor->Config['ImageBrowserWindowWidth'] = "700";
		$oFCKeditor->Config['ImageBrowserWindowHeight'] = "600";
		
		$oFCKeditor->Config['LinkDlgHideTarget'] = "false";
		$oFCKeditor->Config['LinkDlgHideAdvanced'] = "false";
		
		$oFCKeditor->Config['ImageDlgHideLink'] = "false";
		$oFCKeditor->Config['ImageDlgHideAdvanced'] = "false";
		
		$oFCKeditor->Config['FlashDlgHideAdvanced'] = "false";
		
		
		$oFCKeditor->BasePath	= POLYPHONY_PATH."/javascript/fckeditor/" ;
		$oFCKeditor->Value		= $this->getContent();
		$oFCKeditor->Height		= '400' ;
// 		$oFCKeditor->ToolbarSet		= 'simple' ;
		
		$oFCKeditor->Create() ;
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
		print "\n\t<div align='right'>Current Editor: <select name='".$this->getFieldName('editor')."' onchange='this.form.submit()'>";
		print "\n\t<option value='fck'".(($this->textEditor=='fck')?" selected='selected'":"").">FCKeditor</option>";
		print "\n\t<option value='none'".(($this->textEditor=='none')?" selected='selected'":"").">None</option>";
		print "\n\t</select></div>";

 		// replace with editor code
 		$this->getEditor();
	//	print "\n\t<textarea name='".$this->getFieldName('content')."' rows='20' cols='50'>".$this->getContent()."</textarea>";
		
		print "\n\t<br/>";
		print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
		
		print "\n\t<input type='button' value='"._('Cancel')."' onclick='".$this->locationSend()."'/>";
		
		// Image button
		print "\n\t<input type='button' value='"._('Add Image')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print 		"var newString = '\\n<img src=\'' + mediaFile.getUrl().escapeHTML() + '\' title=\'' + mediaFile.getTitles()[0].escapeHTML() + '\'/>' ; ";
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
		print		"link.title = mediaFile.getTitles()[0].escapeHTML(); ";
		
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
		print "\"/>";
		
		print "\n\t<br/>";
		print str_replace('%1',
			"<input name='".$this->getFieldName('abstractLength')."' type='text' value='".intval($this->getRawDescription())."' onchange='return false;' size='3'/>",
			_("Abstract to %1 words. (Enter '0' for no abstract)"));
		
		print "\n</form>";
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
			$this->printEditForm();
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div onclick='if (event.shiftKey) { ".$this->locationSend(array('edit' => 'true'))."}'>";
 			}
 			
 			if ($this->hasContent()) {
				$abstractLength = intval($this->getRawDescription());
				if ($abstractLength) {
					print "\n".$this->trimHTML($this->getContent(), $abstractLength);
				} else {
					print "\n".$this->getContent();
				}
			} else {
				print "\n<div class='plugin_empty'>";
				print _("No text has been added yet. ");
				if ($this->shouldShowControls()) {
					print "<br/>"._("Click the 'edit' link to choose a file. ");
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
 		
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			$this->printEditForm();
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div onclick='if (event.shiftKey) { ".$this->locationSend(array('edit' => 'true'))."}'>";
 			}
	 		print "\n".$this->getContent();
	 		
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
 	function &getWizardComponent () {
 		$wrapper =& new WComponentCollection;
 		ob_start();
 		
 		$property =& $wrapper->addComponent('content', WTextArea::withRowsAndColumns(20, 80));
 		$property->setValue($this->getContent());
 		
 		$property =& $wrapper->addComponent('abstractLength', new WTextField);
 		$property->setSize(3);
 		$property->setValue($this->getRawDescription());
 		
 		print "[[content]]";
 		
 		// Image button
 		print "<br/>";
		print "\n\t<input type='button' value='"._('Add Image')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print 		"var newString = '\\n<img src=\'' + mediaFile.getUrl().escapeHTML() + '\' title=\'' + mediaFile.getTitles()[0].escapeHTML() + '\'/>' ; ";
		print 		"edInsertContent(this.form.elements['[[fieldname:content]]'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/>";
		
		// File button
		print "\n\t<input type='button' value='"._('Add File')."' onclick=\"";
		print "this.onUse = function (mediaFile) { ";
		print		"var downloadBar = document.createElement('div'); ";
		print 		"var link = downloadBar.appendChild(document.createElement('a')); ";
		print 		"link.href = mediaFile.getUrl().escapeHTML(); ";
		print		"link.title = mediaFile.getTitles()[0].escapeHTML(); ";
		
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
		print 		"edInsertContent(this.form.elements['[[fieldname:content]]'], newString); ";
		print "}; "; 
		print "MediaLibrary.run('".$this->getId()."', this); ";
		print "\"/>";
		
		print "\n\t<br/>";
		print _("Abstract to [[abstractLength]] words. (Enter '0' for no abstract)");
 		
 		$wrapper->setContent(ob_get_clean());
 		return $wrapper;
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
 		$this->setContent($values['content']);
 		$this->setRawDescription(intval($values['abstractLength']));
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
}

?>