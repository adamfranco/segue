<?php
/**
 * @since 1/30/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * result printer
 * 
 * @since 1/30/09
 * @package segue.modules.participation
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class ParticipationResultPrinter
	extends EmbeddedArrayResultPrinter
{

	/**
	 * Constructor
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 1/30/09
	 */
	public function __construct (array $actions, $headRow, $numRows, $callback) {
		parent::EmbeddedArrayResultPrinter($actions, 1, $numRows, $callback);
		$this->headRow = $headRow;
	}
		
	/**
	 * Builds the header row of this result table.
	 *
	 * @return string
	 **/
	function createHeaderRow()
	{
		return $this->headRow;
	}
	
	/**
	 * Creates a table TD element with the passed content.
	 * @param string $content
	 * @param optional integer $colspan The number of columns for this element to span.
	 * @param optional string $align The text alignment.
	 *
	 * @return string
	 **/
	function createTDElement($content, $colspan = 0, $align='left')
	{
		return $content;
	}
	
	/**
	 * Returns a block of HTML markup.
	 * 
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return string
	 * @access public
	 * @date 8/5/04
	 */
	function getMarkup ($shouldPrintFunction = NULL) {
		$markup = parent::getMarkup($shouldPrintFunction);
		
		$markup = parent::getPageLinks($this->getStartingNumber(), $this->numItemsPrinted)
			.$markup;
			
		return $markup;
	}
	
	/**
	 * Return a string containing HTML links to other pages of the iterator.
	 * if all items fit on one page, an empty string will be returned.
	 * 
	 * @param integer $startingNumber The item number to start with.
	 * @param integer $numItems The total number of Items.
	 * @return string
	 * @access public
	 * @since 12/7/05
	 */
	function getPageLinks ($startingNumber, $numItems) {
		return "<td colspan='4'>".parent::getPageLinks($startingNumber, $numItems)."</td>";
	}
}

?>