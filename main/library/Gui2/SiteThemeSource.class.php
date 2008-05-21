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
require_once(HARMONI.'/Gui2/ThemeAdmin.interface.php');
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
	implements Harmoni_Gui2_ThemeSourceInterface, Harmoni_Gui2_ThemeAdminInterface
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
	 * Answer the current site id for this source.
	 * 
	 * @return string $id
	 * @access private
	 * @since 5/15/08
	 */
	private function getSiteId () {
		if (isset($this->siteId))
			return $this->siteId;
		else
			return SiteDispatcher::getCurrentRootNode()->getId();
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
		
		$query = new SelectQuery;
		$query->addTable('segue_site_theme');
		$query->addColumn('id');
		$query->addWhereEqual('fk_site', $this->getSiteId());
		$dbMgr = Services::getService("DatabaseManager");
		$result = $dbMgr->query($query, $this->databaseIndex);
		while ($result->hasNext()) {
			$row = $result->next();
			$themes[] = new Segue_Gui2_SiteTheme($this->databaseIndex, $row['id']);
		}
		$result->free();
		
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
		if (!preg_match('/^site_theme-([0-9]+)$/i', $idString, $matches))
			throw new UnknownIdException("No theme exists with id, '$idString'.");
		
		return new Segue_Gui2_SiteTheme($this->databaseIndex, $matches[1]);
	}
	
	/*********************************************************
	 * Theme Administration
	 *********************************************************/
	
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
		return true;
	}
	
	/**
	 * Answer an object that implements the Harmoni_Gui2_ThemeAdminInterface
	 * for this theme source. This could be the same or a different object.
	 * 
	 * @return object Harmoni_Gui2_ThemeAdminInterface
	 * @access public
	 * @since 5/6/08
	 */
	public function getThemeAdminSession () {
		return $this;
	}
	
	/*********************************************************
	 * Theme Administration
	 *********************************************************/
	/**
	 * Create a new empty theme.
	 * 
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/16/08
	 */
	public function createTheme () {
		$query = new InsertQuery;
		$query->setTable('segue_site_theme');
		$query->addValue('fk_site', $this->getSiteId());
		$query->addValue('display_name', _("Untitled"));
		$dbc = Services::getService('DatabaseManager');
		$result = $dbc->query($query, $this->databaseIndex);
		return new Segue_Gui2_SiteTheme($this->databaseIndex, strval($result->getLastAutoIncrementValue()));
	}
	/**
	 * Create a copy of a theme and return the new copy.
	 * 
	 * @param object Harmoni_Gui2_ThemeInterface $theme
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access public
	 * @since 5/16/08
	 */
	public function createCopy (Harmoni_Gui2_ThemeInterface $theme) {
		$newTheme = $this->createTheme();
		$newTheme->updateDisplayName($theme->getDisplayName()." "._("copy"));
		$newTheme->updateDescription($theme->getDescription());
		$newTheme->updateThumbnail($theme->getThumbnail());
		if ($theme->supportsOptions()) {
			$optionsSession = $theme->getOptionsSession();
			$newOptionsSession = $newTheme->getOptionsSession();
			$newOptionsSession->updateOptionsDocument($optionsSession->getOptionsDocument());
		}
		$modSess = $newTheme->getModificationSession();
		$modSess->updateGlobalCss($theme->getGlobalCss());
		foreach ($theme->getComponentTypes() as $type) {
			$modSess->updateCssForType($type, $theme->getCssForType($type));
			$modSess->updateTemplateForType($type, $theme->getTemplateForType($type));
		}
		foreach ($theme->getImages() as $image) {
			$path = $image->getPath();
			$dir = dirname($path);
			$prefixPath = '';
			if (preg_match('/^(?:(?:.*\/)?images\/)?(.+)$/', $dir, $matches)) {
				$prefixPath = $matches[1];
			}
			$modSess->addImage($image, $image->getBasename(), $prefixPath);
		}
		
		return $newTheme;
	}
}

?>