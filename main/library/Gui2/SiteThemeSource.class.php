<?php
/**
 * @since 5/6/08
 * @package harmoni.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(HARMONI.'/Gui2/ThemeSource.interface.php');
require_once(dirname(__FILE__).'/SiteTheme.class.php');

/**
 * This class provides access to themes that exist in a database.
 * 
 * @since 5/6/08
 * @package harmoni.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Gui2_SiteThemeSource
	implements Harmoni_Gui2_ThemeSourceInterface
{
	/**
	 * Constructor
	 * 
	 * @param array $configuration
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function __construct (array $configuration) {
		if (!isset($configuration['database_index']))
			throw new ConfigurationErrorException("No 'database_index' specified.'");
		if (is_numeric($configuration['database_index']))
			$this->databaseIndex = intval($configuration['database_index']);
		else
			throw new ConfigurationErrorException("'database_index' must be an integer, '".$configruation['database_index']."' given.");
		
		// If no site Id is specified, we will fetch it from the request context 
		// when we need it.
		if (isset($configuration['site_id'])) {
			ArgumentValidator::validate($configuration['site_id'], NonzeroLengthStringValidatorRule::getRule());
		
			$this->siteId = $configuration['site_id'];
		}
	}
	
	/**
	 * Answer an array of all of the themes known to this source
	 * 
	 * @return array of Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getThemes () {
		$themes = array();
		$subDirs = scandir($this->path);
		if (!$subDirs)
			throw new OperationFailedException("Could not get themes.");
		foreach ($subDirs as $name) {
			$fullPath = $this->path."/".$name;
			if ($name != '.' && $name != '..' && is_dir($fullPath))
				$themes[] = new Harmoni_Gui2_DirectoryTheme($fullPath);
		}
		
		return $themes;
	}
	
	/**
	 * Answer a theme by Id
	 * 
	 * @param string $idString
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getTheme ($idString) {
		// check for any except the allow charachers.
		if (preg_match('/[^a-z0-9_\.-]/i', $idString))
			throw new UnknownIdException("No theme exists with id, '$idString'.");
		
		return new Harmoni_Gui2_DirectoryTheme($this->path.'/'.$idString);
	}
	
	/**
	 * Answer true if this source supports theme administration.
	 * If this method returns true, getThemeAdminSession must
	 * not throw an UnimplementedException
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/6/08
	 */
	public function supportsThemeAdmin () {
		return false;
	}
	
	/**
	 * Answer an object that implements the ThemeAdminSessionInterface
	 * for this theme source. This could be the same or a different object.
	 * 
	 * @return object Harmoni_Gui2_ThemeAdminSessionInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getThemeAdminSession () {
		throw new UnimplementedException();
	}
	
}

?>