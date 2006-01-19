<?php
/**
 * @since 1/18/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueAssignmentPlugin.class.php,v 1.1 2006/01/19 20:41:27 cws-midd Exp $
 */ 

/**
 * A Simple Plugin for making editable Assignments
 * 
 * @since 1/18/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueAssignmentPlugin.class.php,v 1.1 2006/01/19 20:41:27 cws-midd Exp $
 */
class SegueAssignmentPlugin
	extends AjaxPlugin
// 	extends Plugin
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
			$this->updateDataArray();
			$this->updateDataRecords();
 		}
 	}

	function updateDataArray () {
	// @todo update the assignment array

		// for each response array
		foreach ($this->data['SegueResponse'] as $i => $response) {

			// check all of the reading 
			foreach ($response['SegueResponseReading'] as $j => $read) {
				$this->data['SegueResponse'][$i]['SegueResponseReading'][$j] = 
					(($this->getFieldValue('reading-'.$i.'_'.$j) == 
					"on")?"true":"0");
			}

			// check all of the questions
			foreach ($response['SegueResponseAnswer'] as $j => $quest) {
				$this->data['SegueResponse'][$i]['SegueResponseAnswer'][$j] =
					$this->getFieldValue('quest-'.$i.'_'.$j);
			}
		}
	}
 	
 	/**
 	 * Write markup to be displayed in the site.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function getMarkup () {
	// @todo add student field to response array...
 		$data = $this->getDataRecords();
 		ob_start();

 		// if the user wants to change the plugin
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();
 			
			// print each assignment and editable responses
			foreach ($data['SegueAssignment'] as $i => $assignment) {
				print "\n<div><h2>Assignment ".$i."</h2>";
				
				// are there reading assignments if so put a header
				if (isset($assignment['SegueAssignmentReading'][0]))
					print "\n<h3>Reading:</h3>";

				// write each reading selection and a checkbox for status
				foreach ($assignment['SegueAssignmentReading'] as $j => $read) {
					print "\n<input type='checkbox'
						name='".$this->getFieldName('reading-'.$i.'_'.$j)."'";
					if ($data['SegueResponse'][$i]['SegueResponseReading'][$j] == "true")
						print " checked='true'";
					print "/>".$read."<br/>";
				}
				
				// are there questions if so put a header
				if (isset($assignment['SegueAssignmentQuestion'][0]))
					print "\n<h3>Questions:</h3>";

				// write each question with the editable textarea answer
				foreach ($assignment['SegueAssignmentQuestion'] as $j => $quest) {
					print "\n".$quest."<br/>";
					print "\n Answer: </br>";
					print "\n<textarea name='".$this->getFieldName('quest-'.$i.'_'.$j)
						."' rows='5' cols='50'";
					if (isset(
				$data['SegueResponse'][$i]['SegueResponseAnswer'][$j])) {
						print ">"
					.$data['SegueResponse'][$i]['SegueResponseAnswer'][$j]
					."</textarea><br/>";
					} else 
						print "/><br/>";
				}
				print "\n</div>";
			}
			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {

			// print each reading selection and it's current state for the user
			foreach ($data['SegueAssignment'] as $i => $assignment) {
				print "\n<div><h2>Assignment ".$i."</h2>";

				// are there reading assignments if so put a header
				if (isset($assignment['SegueAssignmentReading'][0]))
					print "\n<h3>Reading:</h3>";
					
				// write each reading assignment with its status
				foreach ($assignment['SegueAssignmentReading'] as $j => $read) {
					print "\n".$this->formStartTagWithAction();
					print "\n <input type='checkbox' ";
					if ($data['SegueResponse'][$i]['SegueResponseReading'][$j] == "true")
						print " checked='true'";
					print "disabled='true'/>";
					print "\n".$read;
					print "\n</form>";
				}
				
				// are there questions if so put a header
				if (isset($assignment['SegueAssignmentQuestion'][0]))
					print "\n<h3>Questions:</h3>";
					
				// write each question and the current answer
				foreach ($assignment['SegueAssignmentQuestion'] as $j => $quest) {
					print "\n".$quest."<br/>";
					if (isset($data['SegueResponse'][$i]['SegueResponseAnswer'][$j])) {
						print "\n Answer: </br>";
						print "\n".$data['SegueResponse'][$i]['SegueResponseAnswer'][$j]."<br/>";
					}
				}
				print "\n</div>";
			}
			
			// the edit link only if they can edit
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