<?php
/**
 * @since 6/10/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This class is provides access template metadata.
 * 
 * @since 6/10/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Templates_Template {
	
	/**
	 * @var string $_path;  
	 * @access private
	 * @since 6/10/08
	 */
	private $_path;
	
	/**
	 * Constructor
	 * 
	 * @param string $path
	 * @return null
	 * @access public
	 * @since 6/10/08
	 */
	public function __construct ($path) {
		if (!is_dir($path))
			throw new ConfigurationErrorException("Template dir '$path' is not a directory.");
		if (!is_readable($path)) 
			throw new ConfigurationErrorException("Template dir '$path' is not readable.");
		
		$this->_path = $path;
	}
	
	/**
	 * Answer a string Id for this template
	 * 
	 * @return string
	 * @access public
	 * @since 6/10/08
	 */
	public function getIdString () {
		return basename($this->_path);
	}
	
	/**
	 * Answer the display name of this template
	 * 
	 * @return string
	 * @access public
	 * @since6/10/08
	 */
	public function getDisplayName () {
		if (!isset($this->info))
			$this->loadInfo();
		if (is_null($this->info))
			return _("Untitled");
		
		$xpath = new DOMXPath($this->info);
		return trim($xpath->query('/TemplateInfo/DisplayName')->item(0)->nodeValue);
	}
	
	/**
	 * Answer a description of this template
	 * 
	 * @return string
	 * @access public
	 * @since6/10/08
	 */
	public function getDescription () {
		if (!isset($this->info))
			$this->loadInfo();
		if (is_null($this->info))
			return '';
		
		$xpath = new DOMXPath($this->info);
		return trim($xpath->query('/TemplateInfo/Description')->item(0)->nodeValue);
	}
	
	/**
	 * Answer a thumbnail file.
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access public
	 * @since6/10/08
	 */
	public function getThumbnail () {
		$file = $this->_path.'/thumbnail.png';
		if (!file_exists($file))
			throw new OperationFailedException("Required thumbnail file, 'thumbnail.png' is missing from template '".$this->getIdString()."'.");
		if (!is_readable($file))
			throw new OperationFailedException("Required thumbnail file, 'thumbnail.png' is not readable in template '".$this->getIdString()."'.");
		
		return new Harmoni_Filing_FileSystemFile($file);
	}
	
	/**
	 * Load the information XML file
	 * 
	 * @return null
	 * @access protected
	 * @since 5/7/08
	 */
	protected function loadInfo () {
		$path = $this->_path.'/info.xml';
		if (!file_exists($path))
			throw new OperationFailedException("Template '".$this->getIdString()."' is missing its info.xml file.");
		
		$this->info = new Harmoni_DOMDocument;
		$this->info->load($path);
		$this->info->schemaValidateWithException(dirname(__FILE__).'/template_info.xsd');
	}
}

?>