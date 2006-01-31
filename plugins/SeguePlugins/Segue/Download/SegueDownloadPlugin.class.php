<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueDownloadPlugin.class.php,v 1.1 2006/01/31 15:49:47 cws-midd Exp $
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
 * @version $Id: SegueDownloadPlugin.class.php,v 1.1 2006/01/31 15:49:47 cws-midd Exp $
 */
class SegueDownloadPlugin
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
 			$this->setTitle($this->cleanHTML($this->getFieldValue('title')));
			// updateDataArray used to handle all the data stored in the array
			$this->updateDataArray();
			// updateDataRecords actually updates the data records
			$this->updateDataRecords();
 		}
 	}
 	
 	/**
 	 * Update the data array from ($_REQUEST) data.
 	 *
 	 * This is just a helper function to keep update from becoming confusing
 	 *
 	 * @return void
 	 * @access public
 	 * @since 1/20/06
 	 */
 	function updateDataArray() {
 		$this->data['FILE'][0]['delete_file'][0] = 
 			(($this->getFieldValue('delete') == "on")?"true":"0");
 			
 		$this->data['FILE'][0]['new_file_path'][0] = 
 			$this->getFieldValue('FILE');
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
 		$FILE =& $this->data['FILE'][0];
 		ob_start();
 		
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();
 			print "This Box Changes The Plugin Title: ";
 			print "\n\t<input type='text' name='".$this->getFieldName('title')."' value='".$this->getTitle()."' size='50'/>";
 			
 			print "\n\t<br/>";

			print "Only Enter Text Into This Box If You Want A Different File For Download From This Plugin:";
			print "\n\t<input type='text' name='".$this->getFieldName('FILE')
				."' value='' size='50'/>";

			print "\n<input type='checkbox'
				name='".$this->getFieldName('delete')."'";
				print " checked='false'";
			print "/>Check this box to delete this file from the plugin.<br/>";
			
 			print "\n\t<br/>";
	
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {
	 		
			print "<img src='"
				.$this->getThumbnailURL($FILE['assoc_file_id'][0],
				$FILE['FILE_NAME'][0])."'/>";
			print "<br/>";
			
			print "\n<a href='"
				.$this->getFileURL($FILE['assoc_file_id'][0],
				$FILE['FILE_NAME'][0])."'";
			print " target='_blank'>";

			print "Download This File</a>\n";
	 		
	 		if ($this->canModify()) {
				print "\n<div style='text-align: right'>";
				print "\n\t<a href=".$this->url(array('edit' => 'true')).">edit</a>";
				print "\n</div>";
			}
 		}
 		
 		return ob_get_clean();
 	}
	
}

?>