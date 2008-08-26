<?php
/**
 * @since 6/18/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/ConditionalGetAction.abstract.php');

/**
 * This action provides access to files that ship within plugins. These files
 * are likely not in a web-accessible directory, so this action provides acces to them.
 * 
 * @since 6/18/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class public_fileAction
	extends ConditionalGetAction
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/18/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 6/18/08
	 */
	public function getModifiedDateAndTime () {
		return $this->getFile()->getModificationDate();
	}
	
	/**
	 * Answer the delay (in seconds) that the modification time should be cached without
	 * checking the source again. 
	 * 
	 * @return object Duration
	 * @access public
	 * @since 6/18/08
	 */
	public function getCacheDuration () {
		// A default of 1 minute is used. Override this method to add longer
		// or shorter times.
		return Duration::withHours(2);
	}
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 6/18/08
	 */
	public function outputContent () {
		$file = $this->getFile();
		
		header("Content-Type: ".$file->getMimeType());
		header("Content-Length: ".$file->getSize());
		print $file->getContents();
		exit;
	}
	
	/**
	 * Answer a filing object for the file requested.
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access protected
	 * @since 6/18/08
	 */
	protected function getFile () {
		if (!isset($this->_file)) {
			$filename = RequestContext::value('file');
			if (!$filename)
				throw new InvalidArgumentException("No file specified.");
			
			// Ensure that no directory traversal requested
			$relPathParts = explode('/', $filename);
			foreach ($relPathParts as $part) {
				if ($part == '..')
					throw new InvalidArgumentException("Directory traversal is not allowed.");
			}
			
			$path = $this->getPluginDir().'/public/'.$filename;
			if (!file_exists($path))
				throw new UnknownIdException("No file found with name '$filename' for plugin '".RequestContext::value('plugin')."'.");
			
			// Hide the file-system path by throwing a custom exception.
			try {
				return new Harmoni_Filing_FileSystemFile($path);
			} catch (Exception $e) {
				throw new UnknownIdException("No file found with name '$filename' for plugin '".RequestContext::value('plugin')."'.");
			
			}
		}
		return $this->_file;
	}
	
	/**
	 * Answer the directory for the plugin
	 * 
	 * @return string
	 * @access protected
	 * @since 6/18/08
	 */
	protected function getPluginDir () {
		$pluginMgr = Services::getService("PluginManager");
		$dir = rtrim($pluginMgr->getPluginDir(
			HarmoniType::fromString(RequestContext::value('plugin'))), '/');
		
		if (!file_exists($dir))
			throw new Exception('Unknown Plugin "'.RequestContext::value('plugin').'".');
		
		return $dir;
	}
}

?>