<?php
/**
 * @since 1/18/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyAssignmentPlugin.class.php,v 1.4 2007/10/25 20:27:00 adamfranco Exp $
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
 * @version $Id: EduMiddleburyAssignmentPlugin.class.php,v 1.4 2007/10/25 20:27:00 adamfranco Exp $
 */
class EduMiddleburyAssignmentPlugin
	extends SegueAjaxPlugin
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
			$title = HtmlString::withValue($this->getFieldValue('title'));
 			$title->clean();
 			$this->setTitle($title->asString());

  			$this->updateDataArray();
  			$this->updateDataRecords();
 		}
 	}

	function updateDataArray () {
	// @todo update the assignment array

		// for each response array
		$response = $this->data['SegueResponse'][0];

			// check all of the reading 
			foreach ($response['SegueResponseReading'] as $j => $read) {
				$this->data['SegueResponse'][0]['SegueResponseReading'][$j] = 
					(($this->getFieldValue('reading-'.$j) == 
					"on")?"true":"0");
			}

			// check all of the questions
			foreach ($response['SegueResponseAnswer'] as $j => $quest) {
				$this->data['SegueResponse'][0]['SegueResponseAnswer'][$j] =
					$this->getFieldValue('quest-'.$j);
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
	// @todo add student field to response array for multiple responses
	// 
 		ob_start();

		//if the user wants to change the plugin
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();
 			
			// if there is an assignment print it
			if (isset($this->data['SegueAssignment'])) {
				$assignment = $this->data['SegueAssignment'][0];
			
	 			print "\n\t<input type='text' name='"
		 			.$this->getFieldName('title')."' value='".$this->getTitle()
 					."' size='50'/>";

				// are there reading assignments if so put a header
				if (isset($assignment['SegueAssignmentReading'][0]))
					print "\n<h3>"._("Reading:")."</h3>";

				// write each reading selection and a checkbox for status
				foreach ($assignment['SegueAssignmentReading'] as $j => $read) {
					print "\n<input type='checkbox'
						name='".$this->getFieldName('reading-'.$j)."'";
					if (isset($this->data['SegueResponse']) && ($this->data['SegueResponse'][0]['SegueResponseReading'][$j] == "true"))
						print " checked='true'";
					print "/>".$read."<br/>";
				}
				
				// are there questions if so put a header
				if (isset($assignment['SegueAssignmentQuestion'][0]))
					print "\n<h3>"._("Questions:")."</h3>";

				// write each question with the editable textarea answer
				foreach ($assignment['SegueAssignmentQuestion'] as $j => $quest) {
					print "\n".$quest."<br/>";
					print "\n "._("Answer:")." </br>";
					print "\n<textarea name='".$this->getFieldName('quest-'.$j)
						."' rows='5' cols='50'";
					if (isset(
				$this->data['SegueResponse'][0]['SegueResponseAnswer'][$j])) {
						print ">"
					.$this->data['SegueResponse'][0]['SegueResponseAnswer'][$j]
					."</textarea><br/>";
					} else 
						print '></textarea><br/>';
				}
			}
			// print the submit and cancel buttons
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {

			// if there is an assignment print it
			if (isset($this->data['SegueAssignment'][0])) {
				$assignment = $this->data['SegueAssignment'][0];

				// are there reading assignments if so put a header
				if (isset($assignment['SegueAssignmentReading'][0]))
					print "\n<h3>"._("Reading:")."</h3>";
					
				// write each reading assignment with its status
				foreach ($assignment['SegueAssignmentReading'] as $j => $read) {
					print "\n <input type='checkbox' ";
					if (isset($this->data['SegueResponse']) && ($this->data['SegueResponse'][0]['SegueResponseReading'][$j] == "true"))
						print " checked='true'";
					print "disabled='true'/>";
					print "\n".$read;
				}
				
				// are there questions if so put a header
				if (isset($assignment['SegueAssignmentQuestion'][0]))
					print "\n<h3>"._("Questions:")."</h3>";
					
				// write each question and the current answer
				foreach ($assignment['SegueAssignmentQuestion'] as $j => $quest) {
					print "\n".$quest."<br/>";
					if (isset($this->data['SegueResponse'][0]['SegueResponseAnswer'][$j])) {
						print "\n "._("Answer:")." </br>";
						print "\n".$this->data['SegueResponse'][0]['SegueResponseAnswer'][$j]."<br/>";
					}
				}
 			}
			
			// the edit link only if they can edit
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