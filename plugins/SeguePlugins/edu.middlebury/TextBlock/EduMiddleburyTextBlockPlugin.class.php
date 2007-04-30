<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.5 2007/02/26 20:14:30 adamfranco Exp $
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
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.5 2007/02/26 20:14:30 adamfranco Exp $
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
 			$this->logEvent('Modify Content', 'TextBlock content updated');
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
 			
 			print "\n\t<textarea name='".$this->getFieldName('content')."' rows='20' cols='50'>".$this->getContent()."</textarea>";
 			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";
 			
 			// Image button
 			print "\n\t<input type='button' value='"._('Add Image')."' onclick=\"";
 			print "this.onUse = function (mediaFile) { ";
 			print 		"var newString = '\\n<img src=\'' + mediaFile.url.escapeHTML() + '\' title=\'' + mediaFile.asset.displayName.escapeHTML() + '\'/>' ; ";
 			print 		"edInsertContent(this.form.elements['".$this->getFieldName('content')."'], newString); ";
 			print "}; "; 
 			print "MediaLibrary.run('".$this->getId()."', this); ";
 			print "\"/>";
 			
 			// File button
 			print "\n\t<input type='button' value='"._('Add File')."' onclick=\"";
 			print "this.onUse = function (mediaFile) { ";
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
 			
 			print 		"var newString = '<div>' + downloadBar.innerHTML + '<div style=\'clear: both;\'></div></div>'; ";
 			print 		"edInsertContent(this.form.elements['".$this->getFieldName('content')."'], newString); ";
 			print "}; "; 
 			print "MediaLibrary.run('".$this->getId()."', this); ";
 			print "\"/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {
 			if ($this->shouldShowControls()) {
				print "\n<div onclick=".$this->url(array('edit' => 'true')).">";
 			}
	 		print "\n".$this->getContent();
	 		
	 		if ($this->shouldShowControls()) {
				print "\n</div>";
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a href=".$this->url(array('edit' => 'true')).">"._("click to edit")."</a>";
				print "\n</div>";
			}
				
 		}
 		
 		return ob_get_clean();
 	}
	
}

?>