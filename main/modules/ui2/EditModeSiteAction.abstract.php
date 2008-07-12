<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteAction.abstract.php,v 1.11 2008/03/31 23:03:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This is an abstract class that makes it easy to add new editing actions
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteAction.abstract.php,v 1.11 2008/03/31 23:03:54 adamfranco Exp $
 */
abstract class EditModeSiteAction 
	extends MainWindowAction
{
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to modify this <em>Node</em>.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$director = $this->getSiteDirector();
		
		$this->processChanges($director);
		
		$this->writeDataAndReturn();
	}
	
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	abstract function processChanges ( SiteDirector $director );
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access public
	 * @since 4/14/06
	 */
	function getSiteDirector () {
		return SiteDispatcher::getSiteDirector();
	}
	
	/**
	 * Write to our data source and return to our previous action
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function writeDataAndReturn () {
		// 		printpre($this->document->toNormalizedString(true));
// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
// 		
// 		// Let's make sure the file exists and is writable first.
// 		if (is_writable($this->filename)) {
// 		
// 			// In our example we're opening $filename in append mode.
// 			// The file pointer is at the bottom of the file hence
// 			// that's where $somecontent will go when we fwrite() it.
// 			if (!$handle = fopen($this->filename, 'w')) {
// 				echo "Cannot open file (".$this->filename.")";
// 				exit;
// 			}
// 			
// 			// Write $somecontent to our opened file.
// 			if (fwrite($handle, $this->document->toNormalizedString()) === FALSE) {
// 				echo "Cannot write to file (".$this->filename.")";
// 				exit;
// 			}
// 			
// 			fclose($handle);
			
			$this->returnToCallerPage();
// 			
// 		} else {
// 			echo "The file ".$this->filename." is not writable.<hr/>";
// 			printpre($this->document->toNormalizedString(true));
// 		}

	}
	
	/**
	 * Return the browser to the page from whence they came
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function returnToCallerPage () {
		$harmoni = Harmoni::instance();
		if (!($returnAction = RequestContext::value('returnAction')))
			$returnAction = 'editview';
		
		if (isset($this->newIdToSendTo)) {
			$node = $this->newIdToSendTo;
		} else {
			$node = RequestContext::value('returnNode');
		}
		
		RequestContext::locationHeader($harmoni->request->quickURL(
			$harmoni->request->getRequestedModule(), $returnAction,
			array("node" => $node)));	
	}
}

?>