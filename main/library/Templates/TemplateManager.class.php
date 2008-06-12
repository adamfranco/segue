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

require_once(dirname(__FILE__)."/Template.class.php");

/**
 * Template Manager provides access to the list of installed Templates.
 * 
 * @since 6/10/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Templates_TemplateManager {
		
	/**
 	 * @var object  $instance;  
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;
 	
 	/**
 	 * @var array $order;  
 	 * @access private
 	 * @since 6/12/08
 	 */
 	private $order = array();

	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance)) {
			$class = __CLASS__;
			self::$instance = new $class;
		}
		
		return self::$instance;
	}
	
	/**
	 * Set the order of templates
	 * 
	 * @param array $orderedIdStrings
	 * @return void
	 * @access public
	 * @since 6/12/08
	 */
	public function setOrder (array $orderedIdStrings) {
		ArgumentValidator::validate($orderedIdStrings, ArrayValidatorRuleWithRule::getRule(
			NonzeroLengthStringValidatorRule::getRule()));
		$this->order = $orderedIdStrings;
	}
	
	/**
	 * Answer a template by id string
	 * 
	 * @param string $id
	 * @return object Segue_Templates_Template
	 * @access public
	 * @since 6/11/08
	 */
	public function getTemplate ($id) {
		if (preg_match('/(\.\.|\/)/', $id))
			throw new InvalidArgumentException("Invalid Template Id, '$id'.");
		
		if (file_exists(MYDIR.'/templates-local/'.$id))
			return new Segue_Templates_Template(MYDIR.'/templates-local/'.$id);
		
		return new Segue_Templates_Template(MYDIR.'/templates-dist/'.$id);
	}
	
	/**
	 * Answer an array of template objects
	 * 
	 * @return array
	 * @access public
	 * @since 6/10/08
	 */
	public function getTemplates () {
		$localTemplates = $this->_getTemplates(MYDIR.'/templates-local');
		$localIds = array_map(array($this, '_getTemplateId'), $localTemplates);
		$distTemplates = $this->_getTemplates(MYDIR.'/templates-dist');
		
		// Add dist templates that aren't duplicates.
		$templates = $localTemplates;
		foreach ($distTemplates as $template) {
			if (!in_array($template->getIdString(), $localIds))
				$templates[] = $template;
		}
		
		// Order the templates Alphabetically
		$templateIds = array_map(array($this, '_getTemplateId'), $templates);
		array_multisort($templateIds, $templates);
		
		// Remove disabled templates
		// @todo
		
		// Apply configured order.
		if (count($this->order)) {
			$orderedTemplates = array();
			// Add the ordered templates to the new array
			foreach ($this->order as $id) {
				$key = array_search($id, $templateIds);
				if ($key !== false) {
					$orderedTemplates[] = $templates[$key];
					unset($templates[$key], $templateIds[$key]);
				}
			}
			// Add remaining templates to the new array.
			foreach ($templates as $template) {
				$orderedTemplates[] = $template;
			}
			// Make our array the new ordered one.
			$templates = $orderedTemplates;
		}
		
		return $templates;
	}
	
	/**
	 * Answer the Id of a template
	 * 
	 * @param object Segue_Templates_Template $template
	 * @return string
	 * @access private
	 * @since 6/12/08
	 */
	private function _getTemplateId (Segue_Templates_Template $template) {
		return $template->getIdString();
	}
	
	/**
	 * Answer an array of template objects from those in a directory
	 * 
	 * @param string $path
	 * @return array
	 * @access protected
	 * @since 6/10/08
	 */
	protected function _getTemplates ($path) {
		$templates = array();
		$subDirs = scandir($path);
		if (!$subDirs)
			throw new OperationFailedException("Could not read templates in ".basename($path).".");
		foreach ($subDirs as $name) {
			$fullPath = $path."/".$name;
			if ($name != '.' && $name != '..' && is_dir($fullPath)) {
				try {
					$templates[] = new Segue_Templates_Template($fullPath);
				} catch (PermissionDeniedException $e) {
				}
			}
		}
		
		return $templates;
	}
	
}

?>