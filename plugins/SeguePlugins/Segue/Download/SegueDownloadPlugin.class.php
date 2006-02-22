<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueDownloadPlugin.class.php,v 1.3 2006/02/22 20:29:56 adamfranco Exp $
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
 * @version $Id: SegueDownloadPlugin.class.php,v 1.3 2006/02/22 20:29:56 adamfranco Exp $
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

	// ===== whether or not to delete the stored file ===== //
 		$this->data['FILE'][0]['delete_file'][0] = 
 			(($this->getFieldValue('delete') == "on")?"true":"0");

	// ===== whether or not to change the stored file ===== //
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
 		// Assuming only one file download per plugin
 		// @todo support multiple file downloads from a single plugin
		if (!isset($this->data['FILE'])) {
			return "NO FILES FOR DOWNLOAD AVAILABLE";
 		} else {
 		$FILE =& $this->data['FILE'];
 		ob_start();
 		
// ===== What to print when editing ===== //
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();

// ===== Start Table for form ===== //
			print "\n\t<table border='0' cols='2'>";

// ===== Title Block Editing ===== //
 			print "<tr><td>"._("This Box Changes The Plugin Title:")." </td>";
 			print "\n\t<input type='text' name='".$this->getFieldName('title')."' value='".$this->getTitle()."' size='50'/></td></tr>";
 			print "\n\t<br/><br/>";

// ===== Data Records Editing ===== //
			// for changing what file is being served for download
			foreach ($FILE as $instance => $data) {
				print "\n<h4>"._("File")." ".$instance.":</h4>";
				print "\n\t\t<tr><td>"._("Change File For Download To:")
					."</td>";
				print "<td><input type='text' name='"
					.$this->getFieldName('FILE')."' value='' size='30'/></td></tr>";
				
				// for completely removing the file from the system
				print "<tr><td>"._("Check this box to delete this file from the plugin.")."</td>";
				print "\n\t<td><input type='checkbox'
					name='".$this->getFieldName('delete')."'/></td></tr><hr/>";
			}
			print "</table>";
// ===== End of Form Submit or Cancel ===== //
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {
// ===== What to print when viewing/downloading ===== //
			$printArray = array("FILE_NAME", "FILE_DATA", "FILE_SIZE");
			foreach ($FILE as $instance => $data) {
				$fileHTML = $this->printFileRecord($data, $printArray);				
				print $fileHTML."<hr/><br/>";
	 		}
	 		// can this agent edit the plugin?
	 		if ($this->shouldShowControls()) {
				print "\n<div style='text-align: right'>";
				print "\n\t<a href=".$this->url(array('edit' => 'true')).">"._("edit")."</a>";
				print "\n</div>";
			}
 		}
 		return ob_get_clean();
		}
 	}

}

?>