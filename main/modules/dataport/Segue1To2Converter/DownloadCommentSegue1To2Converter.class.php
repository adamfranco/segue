<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DownloadCommentSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/CommentSegue1To2Converter.abstract.php");

/**
 * A TextComment importer.
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DownloadCommentSegue1To2Converter.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class DownloadCommentSegue1To2Converter
	extends CommentSegue1To2Converter
{
		
	/**
	 * Constructor takes the source element and the destination document and the destination xpath.
	 * 
	 * @param object DOMElement $sourceElement
	 * @param object DOMXPath $sourceXPath
	 * @param object DOMDocument $doc
	 * @param object DOMXPath $xpath
	 * @param object Segue1To2Director $director
	 * @return void
	 * @access public
	 * @since 2/12/08
	 */
	public function __construct (DOMElement $sourceElement, DOMXPath $sourceXPath, DOMDocument $doc, DOMXPath $xpath, Segue1To2Director $director) {
		parent::__construct($sourceElement, $sourceXPath, $doc, $xpath, $director);
		
		$this->surrogate = new DownloadBlocktSegue1To2Converter($sourceElement, $sourceXPath, $doc, $xpath, $director);
	}
	
}

?>