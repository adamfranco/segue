<?php
/**
 * @since 3/20/08
 * @package segue.segue1map
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1UrlResolver.class.php,v 1.2 2008/03/26 14:39:07 adamfranco Exp $
 */ 

/**
 * This class will resolve Segue1 urls and forward the user to an imported Segue2
 * version of the site, back to another Segue1 instance, or to an error page.
 * 
 * @since 3/20/08
 * @package segue.segue1map
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1UrlResolver.class.php,v 1.2 2008/03/26 14:39:07 adamfranco Exp $
 */
class Segue1UrlResolver {
		
	/**
 	 * @var object  $instance;  
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;

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
		if (!isset(self::$instance))
			self::$instance = new Segue1UrlResolver;
		
		return self::$instance;
	}
	
	/**
	 * Check the current request state to see if a segue1-style url is requested.
	 *
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	public function isCurrentSegue1 () {
		$harmoni = Harmoni::instance();
		
		if (isset($_SERVER['PATH_INFO']) 
			&& preg_match('/^\/sites\/(\w+)\/?/', $_SERVER['PATH_INFO'], $matches)) 
		{
			return true;
		}
		
		// Segue 1 doesn't use modules
		if (strlen($harmoni->request->getRequestedModule()))
			return false;
		
		return $this->getMatchesSegue1($_GET);
	}
	
	/**
	 * Answer true if the url string passed is a segue1 url
	 * 
	 * @param string $url
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	public function isUrlSegue1 ($url) {
		preg_match_all('/[?&]([^=]+)=([^&=])+/', $url, $matches);
		$get = array();
		foreach ($matches[1] as $i => $key)
			$get[$key] = $matches[2][$i];
		
		return $this->getMatchesSegue1($get);
	}
	
	/**
	 * Answer true if the array of GET parameters matches a Segue 1 url
	 * 
	 * @param array $get
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	public function getMatchesSegue1 (array $get) {
		// Valid Segue 1 actions to link to
		$segue1Actions = array('site', 'viewsite', 'rss');
		if (!isset($get['action']) || !in_array($get['action'], $segue1Actions))
			return false;
		
		// Valid Segue 1 identifiers
		$segue1Identifiers = array('site', 'section', 'page', 'story');
		foreach ($segue1Identifiers as $identifier)
			if (isset($get[$identifier]) && $get[$identifier])
				return true;
		
		return false;
	}
	
	/**
	 * check the current url and forward if needed.
	 * 
	 * @return void
	 * @access public
	 * @since 3/20/08
	 * @static
	 */
	public static function forwardCurrentIfNeeded () {
		$instance = self::instance();
		if ($instance->isCurrentSegue1())
			$instance->resolveCurrent();
	}
	
	/**
	 * Resolve the current state to a new url
	 * 
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function resolveCurrent () {
		$get = $_GET;
		if (isset($_SERVER['PATH_INFO']) 
			&& preg_match('/^\/sites\/([\w_-]+)\/?/', $_SERVER['PATH_INFO'], $matches)) 
		{
			$get['action'] = 'site';
			$get['site'] = $matches[1];
		}
		if (isset($get['site']) && !isset($get['action']))
			$get['action'] = 'site';
		
		$this->resolveGetArray($get);
	}
	
	/**
	 * Resolve an array of GET parameters
	 * 
	 * @param array $get
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function resolveGetArray (array $get) {
		$harmoni = Harmoni::instance();
		if (!count($get))
			throw new Exception("Could not resolve Segue 1 site, no parameters specified.");
		
		// Send to an imported version if it exists
		$segue1Identifiers = array('story', 'page', 'section', 'site');
		foreach ($segue1Identifiers as $identifier) {
			if (isset($get[$identifier]) && $get[$identifier]) {
				try {
					$newId = $this->getSegue2IdForOld($identifier, $get[$identifier]);
					RequestContext::sendTo(
						$harmoni->request->quickURL('view', 'html', array('node' => $newId)));
				} catch (UnknownIdException $e) {
				}
			}
		}
		
		// Send to a Segue 2 site with the same site-name if that exists.
		if (isset($get['site']) && $get['site']) {
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotByShortname($get['site']);
			if ($slot->siteExists())
				RequestContext::sendTo(
						$harmoni->request->quickURL('view', 'html', array('site' => $get['site'])));
		}
		
		// Send to the old Segue 1 instance if it is configured.
		if (defined('DATAPORT_SEGUE1_URL')) {
			// If segue 1 doesn't know about the site, just return and show segue2-specific
			// errors
			if (!$this->isGetValidInSegue1($get))
				return false;
			
			// If segue 1 knows about 
			$segue1Url = DATAPORT_SEGUE1_URL.'/index.php?';
			foreach ($get as $key => $value)
				$segue1Url .= '&'.$key.'='.rawurlencode($value);
			
			RequestContext::sendTo($segue1Url);
		}
	}
	
	/**
	 * Answer true if the Segue1 instance finds a valid record for the get parameters passed
	 * 
	 * @param array $get
	 * @return boolean
	 * @access private
	 * @since 3/20/08
	 */
	private function isGetValidInSegue1 (array $get) {
		if (!defined('DATAPORT_SEGUE1_URL'))
			throw new ConfigurationErrorException('DATAPORT_SEGUE1_URL is not defined.');
		
		if (!isset($get['site']))
			throw new Exception("Could not validate Segue 1 site, no site specified.");
		
		$url = DATAPORT_SEGUE1_URL.'/export/siteExists.php?';
		foreach ($get as $key => $value)
				$url .= '&'.$key.'='.rawurlencode($value);
				
		$result = @file_get_contents($url);
		
		if (!strlen($result))
			throw new Exception("Could not validate Segue 1 site, invalid result returned.");
		
		if ($result == 'true')
			return true;
	}
	
	/**
	 * Answer a new Segue 2 id for a segue1 id 
	 * 
	 * @param string $idType 'site', 'section', 'page', 'story', or 'comment'
	 * @param string $id
	 * @return string or throw an UnknownIdException
	 * @access private
	 * @since 3/20/08
	 */
	private function getSegue2IdForOld ($idType, $id) {
		$segue1Identifiers = array('story', 'page', 'section', 'site');
		if (!in_array($idType, $segue1Identifiers))
			throw new InvalidArgumentException("$idType is not one of (".implode(', ', $segue1Identifiers).").");
		
		$query = new SelectQuery;
		$query->addTable('segue1_id_map');
		$query->addColumn('segue2_slot_name', 'slotName');
		$query->addColumn('segue2_id', 'id');
		$query->addWhereEqual('segue1_id', $idType."_".$id);
		
		$dbc = Services::getService('DatabaseManager');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		if (!$result->getNumberOfRows())
			throw new UnknownIdException("No map matches for Segue 1 $idType $id.");
		
		$slotName = $result->field('slotName');
		$newId = $result->field('id');
		
		// check to see if the new Id is valid.
		try {
			$repositoryMgr = Services::getService('Repository');
			$idMgr = Services::getService('Id');
			$repository = $repositoryMgr->getRepository(
				$idMgr->getId('edu.middlebury.segue.sites_repository'));
			$asset = $repository->getAsset($idMgr->getId($newId));
			return $newId;
		} 
		// If the Id is invalid, try to get a site for the slot name
		catch (UnknownIdException $e) {
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotByShortname($slotName);
			if ($slot->siteExists())
				return $slot->getSiteId()->asString();
		}
		
		// If we still couldn't resolve throw an exception.
		throw new UnknownIdException("A match was found for Segue 1 $idType $id, but it was not valid.");
	}
}

?>