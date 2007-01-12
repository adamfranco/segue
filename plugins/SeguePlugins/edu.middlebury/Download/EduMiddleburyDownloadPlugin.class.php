<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.1 2007/01/12 19:39:13 adamfranco Exp $
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
 * @version $Id: EduMiddleburyDownloadPlugin.class.php,v 1.1 2007/01/12 19:39:13 adamfranco Exp $
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
 		$FILE =& $this->data['FILE'];
 		ob_start();

// ===== Javascript for multifile upload ===== //

print<<<END
<script type='text/javascript'>
/* <![CDATA[ */
/** Credit:
 *   If you're nice, you'll leave this bit:
 *  
 *   Class by Stickman -- http://www.the-stickman.com
 *      with thanks to:
 *      [for Safari fixes]
 *         Luis Torrefranca -- http://www.law.pitt.edu
 *         and
 *         Shawn Parker & John Pennypacker -- http://www.fuzzycoconut.com
 *      [for duplicate name bug]
 *         'neal'
 */
function MultiSelector( list_target, max ){

	// Where to write the list
	this.list_target = list_target;
	// How many elements?
	this.count = 0;
	// How many elements?
	this.id = 0;
	// Is there a maximum?
	if( max ){
		this.max = max;
	} else {
		this.max = -1;
	};
	
	/**
	 * Add a new file input element
	 */
	this.addElement = function( element ){

		// Make sure it's a file input element
		if( element.tagName == 'INPUT' && element.type == 'file' ){

			// Element name -- what number am I?
			element.name = 'file_' + this.id++;

			// Add reference to this object
			element.multi_selector = this;

			// What to do when a file is selected
			element.onchange = function(){

				// New file input
				var new_element = document.createElement( 'input' );
				new_element.type = 'file';

				// Add new element
				this.parentNode.insertBefore( new_element, this );

				// Apply 'update' to element
				this.multi_selector.addElement( new_element );

				// Update list
				this.multi_selector.addListRow( this );

				// Hide this: we can't use display:none because Safari doesn't like it
				this.style.position = 'absolute';
				this.style.left = '-1000px';

			};
			// If we've reached maximum number, disable input element
			if( this.max != -1 && this.count >= this.max ){
				element.disabled = true;
			};

			// File element counter
			this.count++;
			// Most recent element
			this.current_element = element;
			
		} else {
			// This can only be applied to file input elements!
			alert( 'Error: not a file input element' );
		};

	};

	/**
	 * Add a new row to the list of files
	 */
	this.addListRow = function( element ){

		// Row div
		var new_row = document.createElement( 'div' );

		// Delete button
		var new_row_button = document.createElement( 'input' );
		new_row_button.type = 'button';
		new_row_button.value = 'Delete';

		// References
		new_row.element = element;

		// Delete function
		new_row_button.onclick= function(){

			// Remove element from form
			this.parentNode.element.parentNode.removeChild( this.parentNode.element );

			// Remove this row from the list
			this.parentNode.parentNode.removeChild( this.parentNode );

			// Decrement counter
			this.parentNode.element.multi_selector.count--;

			// Re-enable input element (if it's disabled)
			this.parentNode.element.multi_selector.current_element.disabled = false;

			// Appease Safari
			//    without it Safari wants to reload the browser window
			//    which nixes your already queued uploads
			return false;
		};

		// Set row value
		new_row.innerHTML = element.value;

		// Add button
		new_row.appendChild( new_row_button );

		// Add it to the list
		this.list_target.appendChild( new_row );
		
	};

};
/* ]]> */
</script>

END;
 		
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
				print "\n<h4>".$data['FILE_NAME'].":</h4>";
				
				// for completely removing the file from the system
				print "<tr><td>".
					_("Check this box to delete this file from the plugin.").
					"</td>";
				print "\n\t<td><input type='checkbox' name='".
					$this->getFieldName('delete-'.$instance).
					"'/></td></tr><hr/>";
			}
			print "\n<h4>"._("New File:")."</h4>";
			print "\n\t\t<tr><td>"._("Add File For Download:")
				."</td>";
			print "<td><input type='file' id='upload_input'".
				"size='30'/></td></tr>";
			print "</table>";
			print "<div id='files_list'></div>";
			print "<hr/>";
// ===== Javascript for multifile upload part 2 ===== //
print<<<END
<script type='text/javascript'>
/* <![CDATA[ */
	var multi_selector = new MultiSelector(
		document.getElementById('files_list'));
		
	multi_selector.addElement( document.getElementById( 'upload_input' ));

/* ]]> */
</script>

END;
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

?>