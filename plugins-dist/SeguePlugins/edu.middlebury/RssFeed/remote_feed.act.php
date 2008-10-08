<?php
/**
 * @since 6/19/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This Action will fetch and cache remote RSS feeds.
 * 
 * @since 6/19/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class remote_feed 
	implements SeguePluginsAction
{
		
	/**
	 * If this action should execute tied to a single plugin instance, return true.
	 * If true, a plugin instance will be passed to the plugin via its setPluginInstance()
	 * method.
	 *
	 * If this action is more general-purpose and not tied to a single plugin, return false.
	 * If false, no plugin instance will be availible to this action.
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/19/08
	 * @static
	 */
	public static function isPerInstance () {
		return false;
	}
	
	/**
	 * If this action is per-instance this method will be called to give the action
	 * its instance
	 * 
	 * @param object SeguePluginsAPI $pluginInstance
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function setPluginInstance (SeguePluginsAPI $pluginInstance) {
		throw new UnimplementedException();
	}
	
	/**
	 * This method will be called on the action to provide it with request params.
	 * Actions should not look at $_REQUEST, $_GET, or $_POST as alternate methods
	 * of passing or encoding parameters may be used.
	 * 
	 * @param array $requestParams
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function setRequestParams (array $requestParams) {
		$this->request = $requestParams;
		if (!preg_match(
				'/^(http|https):\/\/[a-zA-Z0-9_.-]+(:[0-9]+)?(\/[a-zA-Z0-9_.,?%+=\/-]*)/i', 
				$this->request['url']))
			throw new InvalidArgumentException("Not a valid feed URL: ".$this->request['url']);
	}
	
	/**
	 * Execute this action. The setX methods will be called before execute to
	 * initialize this action.
	 * 
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function execute () {
		try {
			$feedData = $this->getFeedXml($this->request['url']);
			
			// Output the feed data
			header('Content-Type: text/xml');
// 			header('Content-Length: '.strlen($feedData));
			print $feedData;
			exit;
		} catch (Exception $e) {
			header('Content-Type: text/xml');
			print "<error type=\"".get_class($e)."\" code=\"".$e->getCode()."\"><![CDATA[";
			print $e->getMessage();
			print "]]></error>";
			exit;
		}
	}
	
	/**
	 * Answer the feed XML string for a URL
	 * 
	 * @param string $url
	 * @return string
	 * @access protected
	 * @since 7/8/08
	 */
	protected function getFeedXml ($url) {
		if ($this->isCacheCurrent($url)) {
			return $this->getCachedXmlString($url);
		} else {
			$feedData = $this->loadFeedXml($url);
			$this->cacheXmlString($url, $feedData);
			return $feedData;
		}
	}
	
	/**
	 * Load feed data, convert and clean it, and return its string value.
	 * 
	 * @param string $url
	 * @return string RSS xml
	 * @access protected
	 * @since 7/8/08
	 */
	protected function loadFeedXml ($url) {
		$feedData = @file_get_contents($url);
		if (!strlen($feedData))
			throw new OperationFailedException("Could not access feed, '".$url."'.");
		
		$feed = new DOMDocument();
		
		// If the encoding is not UTF-8, convert the document
		if (preg_match('/^<\?xml .*encoding=[\'"]([a-zA-Z0-9-]+)[\'"].*\?>/m', $feedData, $matches)) {
			$encoding = $matches[1];
			if (strtoupper($encoding) != 'UTF8' && strtoupper($encoding) != 'UTF-8') {
				$feedData = mb_convert_encoding($feedData, 'UTF-8', strtoupper($encoding));
				$feedData = preg_replace('/^(<\?xml .*encoding=[\'"])([a-zA-Z0-9-]+)([\'"].*\?>)/m', '\1UTF-8\3', $feedData);
			}
		}
		
		// Convert any non-UTF-8 characters
		$string = String::withValue($feedData);
		$string->makeUtf8();
		$feedData = $string->asString();		
		if (!@$feed->loadXML($feedData))
			throw new OperationFailedException("Invalid feed data: \"".$feedData."\" for URL: ".$url);
		
		// Handle any format conversions
		$feed = $this->convertToRss($feed);
		
		// Validate Feed.
// 		$tmpFeed = $feed;
// 		$feed = new Harmoni_DOMDocument;
// 		$feed->loadXML($tmpFeed->saveXML());
// 		unset($tmpFeed);
// 		$feed->schemaValidateWithException(dirname(__FILE__).'/rss-2_0-lax.xsd');
		
		// Run through the titles, authors, and descriptions and clean out any unsafe HTML
		foreach ($feed->getElementsByTagName('title') as $element)
			$element->nodeValue = strip_tags(htmlspecialchars_decode($element->nodeValue));
		
		foreach ($feed->getElementsByTagName('author') as $element)
			$element->nodeValue = strip_tags(htmlspecialchars_decode($element->nodeValue));
		
		foreach ($feed->getElementsByTagName('comments') as $element)
			$element->nodeValue = htmlentities(strip_tags(html_entity_decode($element->nodeValue)));
		
		foreach ($feed->getElementsByTagName('link') as $element)
			$element->nodeValue = htmlentities(strip_tags(html_entity_decode($element->nodeValue)));
			
		foreach ($feed->getElementsByTagName('description') as $description) {				
			$html = HtmlString::fromString(htmlspecialchars_decode($description->nodeValue));
			$html->cleanXSS();
			$description->nodeValue = htmlspecialchars($html->asString());
		}
		
		// Move the feed into a dom document.
		$tmpFeed = $feed;
		$feed = new Harmoni_DOMDocument;
		$feed->loadXML($tmpFeed->saveXML());
		unset($tmpFeed);
		
		// Validate the feed again
// 		$feed->schemaValidateWithException(dirname(__FILE__).'/rss-2_0-lax.xsd');
		
		// Just ensure a few basic things:
		if (!$feed->documentElement->nodeName == 'rss')
			throw new DOMDocumentException("Feed root must be an rss element");
		// Check for channels
		foreach ($feed->documentElement->childNodes as $element) {
			if ($element->nodeType == 1 && $element->nodeName != 'channel')
				throw new DOMDocumentException("'".$node->nodeName."' is not expected, expecting 'channel'.");
		}
		// Check dates
		foreach ($feed->getElementsByTagName('pubdate') as $element) {
			if (!preg_match('/(((Mon)|(Tue)|(Wed)|(Thu)|(Fri)|(Sat)|(Sun)), *)?\d\d? +((Jan)|(Feb)|(Mar)|(Apr)|(May)|(Jun)|(Jul)|(Aug)|(Sep)|(Oct)|(Nov)|(Dec)) +\d\d(\d\d)? +\d\d:\d\d(:\d\d)? +(([+\-]?\d\d\d\d)|(UT)|(GMT)|(EST)|(EDT)|(CST)|(CDT)|(MST)|(MDT)|(PST)|(PDT)|\w)/', $element->nodeValue))
				throw new DOMDocumentException("'".$element->nodeValue."' is not a valid date.");
		}
		
		
		return $feed->saveXMLWithWhitespace();
	}
	
	/**
	 * Convert a feed's DOMDocument to one in the RSS 2.0 format. 
	 * Input may be RSS or Atom format.
	 * 
	 * @param object DOMDocument $feed
	 * @return object DOMDocument The RSS 2.0 version of the feed
	 * @access protected
	 * @since 7/8/08
	 */
	protected function convertToRSS (DOMDocument $feed) {
		switch ($feed->documentElement->nodeName) {
			// Convert Atom to RSS 2.0
			case 'feed':
				switch ($feed->documentElement->getAttribute('xmlns')) {
					// Convert Atom 0.3 to Atom 1.0
					case 'http://purl.org/atom/ns#':
						$sheet = new DOMDocument();
						$sheet->load(dirname(__FILE__).'/atom2atom.xsl');
						$processor = new XSLTProcessor();
						$processor->importStylesheet($sheet);
						$feed = $processor->transformToDoc($feed);
					
					// Convert Atom 1.0 to RSS2
					case 'http://www.w3.org/2005/Atom':
						$sheet = new DOMDocument();
						$sheet->load(dirname(__FILE__).'/atom2rss.xsl');
						$processor = new XSLTProcessor();
						$processor->registerPHPFunctions();
						$processor->importStylesheet($sheet);
						return $processor->transformToDoc($feed);
					default:
						throw new OperationFailedException("Unsupported feed format.");
				}
			case 'rss':
				// Convert RSS 0.9x to RSS 2.0 -- shouldn't need to do this as
				// they should be compatible
				return $feed;
			default:
				throw new OperationFailedException("Unsupported feed format.");
		}
	}
	
	/**
	 * Answer true if the cache of the feed is current.
	 * 
	 * @param string $url
	 * @return boolean
	 * @access protected
	 * @since 7/8/08
	 */
	protected function isCacheCurrent ($url) {
		$dbc = Services::getService("DatabaseManager");
		$query = new SelectQuery;
		$query->addTable('segue_plugins_rssfeed_cache');
		$query->addColumn('COUNT(*)', 'num');
		$query->addWhereEqual('url', $url);
		$query->addWhereRawGreaterThan('cache_time', 
			$dbc->toDBDate(DateAndTime::now()->minus(Duration::withSeconds(600)), IMPORTER_CONNECTION));
		
		try {
			$result = $dbc->query($query, IMPORTER_CONNECTION);
		} catch (NoSuchTableDatabaseException $e) {
			return false;
		}
		$num = intval($result->field('num'));
		$result->free();
		
		if ($num > 0)
			return true;
		else
			return false;
	}
	
	/**
	 * Save an XML string to the feed cache
	 * 
	 * @param string $url
	 * @param string $feedXml
	 * @return void
	 * @access protected
	 * @since 7/8/08
	 */
	protected function cacheXmlString ($url, $feedXml) {
		$dbc = Services::getService("DatabaseManager");
		
		$query = new DeleteQuery;
		$query->setTable('segue_plugins_rssfeed_cache');
		$query->addWhereEqual('url', $url);
		$query->addWhereRawLessThan('cache_time', 
			$dbc->toDBDate(DateAndTime::now()->minus(Duration::withSeconds(600)), IMPORTER_CONNECTION),
			_OR);
		
		try {
			$result = $dbc->query($query, IMPORTER_CONNECTION);
		} catch (NoSuchTableDatabaseException $e) {
			$this->createCacheTable();
		}
		
		$query = new InsertQuery;
		$query->setTable('segue_plugins_rssfeed_cache');
		$query->addValue('url', $url);
		$query->addValue('feed_data', $feedXml);
		
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Answer the cached version of the feed Xml
	 * 
	 * @param string $url
	 * @return string The feed XML
	 * @access protected
	 * @since 7/8/08
	 */
	protected function getCachedXmlString ($url) {
		$dbc = Services::getService("DatabaseManager");
		$query = new SelectQuery;
		$query->addTable('segue_plugins_rssfeed_cache');
		$query->addColumn('feed_data');
		$query->addWhereEqual('url', $url);
		
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		$data = $result->field('feed_data');
		$result->free();
		return $data;
	}
	
	/**
	 * Create the cache table.
	 * 
	 * @return void
	 * @access protected
	 * @since 7/8/08
	 */
	protected function createCacheTable () {
		$dbc = Services::getService("DatabaseManager");
		switch ($dbc->getDatabaseType(IMPORTER_CONNECTION)) {
			case MYSQL:
				$file = dirname(__FILE__).'/SQL/MySQL.sql';
				break;
			case POSTGRESQL:
			case ORACLE:
				$file = dirname(__FILE__).'/SQL/PostgreSQL.sql';
				break;
			default:
				throw new Exception("Database type is not supported.");
		}
		SQLUtils::runSQLfile($file, IMPORTER_CONNECTION);
	}
}

?>