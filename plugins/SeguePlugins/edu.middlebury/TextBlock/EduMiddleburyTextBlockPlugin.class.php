<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.9 2007/05/24 17:47:30 adamfranco Exp $
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
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.9 2007/05/24 17:47:30 adamfranco Exp $
 */
class EduMiddleburyTextBlockPlugin
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
 			$this->setContent($this->cleanHTML($this->getFieldValue('content')));
 			$this->setRawDescription($this->getFieldValue('abstractLength'));
 			$this->logEvent('Modify Content', 'TextBlock content updated');
 		}
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
 			
		print "\n\t<textarea name='".$this->getFieldName('content')."' rows='20' cols='50'>".$this->getContent()."</textarea>";
		
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
		print "\n\t<input name='".$this->getFieldName('abstractLength')."' type='text' value='".intval($this->getRawDescription())."' onchange='return false;'/>";
		
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
 			
 			$abstractLength = intval($this->getRawDescription());
 			if ($abstractLength) {
		 		print "\n".$this->trimHTML($this->getContent(), $abstractLength);
		 	} else {
		 		print "\n".$this->getContent();
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
 		
 		$content =& $wrapper->addComponent('content', WTextArea::withRowsAndColumns(20, 80));
 		$content->setValue($this->getContent());
 		
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
 	}
}

?>