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
			$feedData = @file_get_contents($this->request['url']);
			if (!strlen($feedData))
				throw new OperationFailedException("Could not access feed, '".$this->request['url']."'.");
			
			$feed = new DOMDocument();
			
			// Convert any non-UTF-8 characters
			$string = String::withValue($feedData);
			$string->makeUtf8();
			$feed->loadXML($string->asString());
			
			// Handle any format conversions
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
							$feed = $processor->transformToDoc($feed);
							break;
						default:
							throw new OperationFailedException("Unsupported feed format.");
					}
					break;
				case 'rss':
					// Convert RSS 0.9x to RSS 2.0 -- shouldn't need to do this as
					// they should be compatible
					break;
				default:
					throw new OperationFailedException("Unsupported feed format.");
			}
			
			
			// Validate Feed.
			$tmpFeed = $feed;
			$feed = new Harmoni_DOMDocument;
			$feed->loadXML($tmpFeed->saveXML());
			unset($tmpFeed);
			$feed->schemaValidateWithException(dirname(__FILE__).'/rss-2_0-lax.xsd');
			
			// Run through the titles, authors, and descriptions and clean out any unsafe HTML
			foreach ($feed->getElementsByTagName('title') as $title)
				$title->nodeValue = strip_tags(htmlspecialchars_decode($title->nodeValue));
			
			foreach ($feed->getElementsByTagName('author') as $author)
				$author->nodeValue = strip_tags(htmlspecialchars_decode($author->nodeValue));
				
			foreach ($feed->getElementsByTagName('description') as $description) {				
				$html = HtmlString::fromString(htmlspecialchars_decode($description->nodeValue));
				$html->cleanXSS();
				$description->nodeValue = htmlspecialchars($html->asString());
			}
			
			// Validate the feed again
			$tmpFeed = $feed;
			$feed = new Harmoni_DOMDocument;
			$feed->loadXML($tmpFeed->saveXML());
			unset($tmpFeed);
			$feed->schemaValidateWithException(dirname(__FILE__).'/rss-2_0-lax.xsd');
			
			// Cache the feed data
			// @todo
			
			
			// Output the feed data
			$feedData = $feed->saveXMLWithWhitespace();
			header('Content-Type: text/xml');
			header('Content-Length: '.strlen($feedData));
			print $feedData;
			exit;
		} catch (Exception $e) {
			header('Content-Type: text/xml');
			print "<error><![CDATA[";
			print $e->getMessage();
			print "]]></error>";
			exit;
		}
	}
}

?>