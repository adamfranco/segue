<?php
/**
 * @since 2/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: convert.act.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(HARMONI."/oki2/SimpleTableRepository/SimpleTableRepositoryManager.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

require_once(dirname(__FILE__)."/Segue1To2Converter/Segue1To2Director.class.php");

/**
 * Convert a Segue1 site export to a Segue2 site export. This is pretty much just a test 
 * script for now.
 * 
 * @since 2/4/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: convert.act.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class convertAction
	extends MainWindowAction
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/4/08
	 */
	public function isAuthorizedToExecute () {
		return false; // Disabled as this is not yet complete.
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 2/4/08
	 */
	public function buildContent () {
		$sourceFilePath = dirname(__FILE__)."/test/ET/media";
		$sourceDocPath = dirname(__FILE__)."/test/ET/site.xml";
// 		$sourceFilePath = dirname(__FILE__)."/test/amcv0275a-s05/media";
// 		$sourceDocPath = dirname(__FILE__)."/test/amcv0275a-s05/site.xml";
		
		$destFilePath = dirname(__FILE__)."/test/Segue2TestMediaOutput";
		
		$sourceDoc = new Harmoni_DOMDocument;
		$sourceDoc->load($sourceDocPath);
		
		$converter = new Segue1To2Director($destFilePath, $destFilePath);
		$outputDoc = $converter->convert($sourceDoc, $sourceFilePath);
		
		$outputDoc2 = new Harmoni_DOMDocument;
		$outputDoc2->loadXML($outputDoc->saveXMLWithWhitespace());
		printpre(htmlentities($outputDoc2->saveXML()));
		try {
			$outputDoc2->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
	
}

?>