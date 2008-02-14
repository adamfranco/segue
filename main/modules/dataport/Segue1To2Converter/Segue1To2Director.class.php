<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Director.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */ 

require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(dirname(__FILE__)."/SiteNavBlockSegue1To2Converter.class.php");

/**
 * This class is the initial entry point and directs the conversion
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Director.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class Segue1To2Director {
		
	/**
	 * Constructor
	 *
	 * @param string $outputFilePath
	 * @param string $relativeOutputFilePath The output file path relative to
	 * 				encode into the xml output.
	 * @return void
	 * @access public
	 * @since 2/4/08
	 */
	public function __construct ($outputFilePath, $relativeOutputFilePath) {
		if (!is_dir($outputFilePath))
			throw new Exception("'$outputFilePath' does not exist for export.");
		if (!is_writable($outputFilePath))
			throw new Exception("'$outputFilePath' is not writable for export.");
		
		ArgumentValidator::validate($relativeOutputFilePath, NonzeroLengthStringValidatorRule::getRule());
		
		$this->outputFilePath = $outputFilePath;
		$this->relativeOutputFilePath = $relativeOutputFilePath;
		
	}
	
	/**
	 * Do the conversion for a source document
	 * 
	 * @param object DOMDocument $sourceDoc
	 * @param string $sourceFilePath
	 * @return DOMDocument The output document
	 * @access public
	 * @since 2/4/08
	 */
	public function convert (DOMDocument $sourceDoc, $sourceFilePath) {
		$this->sourceDoc = $sourceDoc;
		$this->sourceXPath = new DOMXPath($this->sourceDoc);
		
		if (!is_dir($sourceFilePath))
			throw new Exception("'$sourceFilePath' does not exist for conversion.");
		if (!is_readable($sourceFilePath))
			throw new Exception("'$sourceFilePath' is not readable for conversion.");
		
		$this->sourceFilePath = $sourceFilePath;
		
		// Set up the output document
		$this->doc = new Harmoni_DOMDocument;
		$this->doc->appendChild($this->doc->createElement('Segue2'));
		$this->doc->documentElement->setAttribute('export_date', DateAndTime::now()->asString());
		$this->doc->documentElement->setAttribute('segue_version', displayAction::getSegueVersion());
		$this->doc->documentElement->setAttribute('segue_export_version', '2.0');
		
		$this->agents = $this->doc->documentElement->appendChild($this->doc->createElement('agents'));
		$this->xpath = new DOMXPath($this->doc);
		$this->rootAdded = false;
		
// 		print "<pre>";
// 		foreach ($this->sourceXPath->query('/site/media/media_file/filename') as $filename)
// 			print $filename->nodeValue."\n";
// 		print "</pre>";
		
		
		$this->addSite();
		
		return $this->doc;
	}
	
	/**
	 * Add the top level site nodes.
	 * 
	 * @return void
	 * @access protected
	 * @since 2/4/08
	 */
	protected function addSite () {
		$source = $this->sourceXPath->query('/site', $this->sourceDoc->documentElement)->item(0);
		
		$converter = new SiteNavBlockSegue1To2Converter($source, $this->sourceXPath, $this->doc, $this->xpath, $this);
		
		$siteElement = $converter->convert();
		$this->doc->documentElement->insertBefore($siteElement, $this->agents);
		
	}
	
	/**
	 * Move a file from the source directory to the destination.
	 * 
	 * @param string $filename
	 * @return string The destination path relative to the output xml
	 * @access public
	 * @since 2/14/08
	 */
	public function copyFile ($filename) {
		$source = $this->sourceFilePath."/".$filename;
		$destination = $this->outputFilePath."/".$filename;
		if (!file_exists($source))
			throw new Segue1To2_MissingFileException ("Source media file, '$filename', listed in the XML export does not exist.");
		
// 		if (file_exists($destination))
// 			throw new Segue1To2_InTheWayFileException ("Source media file, '$filename', listed in the XML export already exists in the destination.");
		
			
		if (!copy($source, $destination))
			throw new Segue1To2_FileCopyException ("Could not copy media file from '$source' to '$destination'.");
		
		return $this->relativeOutputFilePath."/".$filename;
	}
	
}

/**
 * An exception for missing files
 * 
 * @since 2/14/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Director.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class Segue1To2_MissingFileException
	extends Exception
{	
}

/**
 * An exception for already existing files
 * 
 * @since 2/14/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Director.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class Segue1To2_InTheWayFileException
	extends Exception
{	
}

/**
 * An exception for missing files
 * 
 * @since 2/14/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Director.class.php,v 1.1 2008/02/14 20:25:43 adamfranco Exp $
 */
class Segue1To2_FileCopyException
	extends Exception
{	
}


?>