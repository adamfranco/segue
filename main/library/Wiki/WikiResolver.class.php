<?php
/**
 * @since 11/30/07
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WikiResolver.class.php,v 1.5 2008/04/09 21:12:02 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/TitleSearcher.class.php");
require_once(dirname(__FILE__)."/TemplateResolver.class.php");

/**
 * The WikiResolver
 * 
 * @since 11/30/07
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WikiResolver.class.php,v 1.5 2008/04/09 21:12:02 adamfranco Exp $
 */
class WikiResolver {

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
			self::$instance = new WikiResolver;
		
		return self::$instance;
	}
	
	/**
	 * A site visitor that matches titles to node ids.
	 * @var object TitleSearcher $titleSearcher;  
	 * @access private
	 * @since 11/30/07
	 */
	private $titleSearcher;
	
	/**
	 * @var string $viewModule;  
	 * @access private
	 * @since 12/3/07
	 */
	private $viewModule = 'ui1';
	
	/**
	 * @var string $viewAction;  
	 * @access private
	 * @since 12/3/07
	 */
	private $viewAction = 'view';
	
	/**
	 * @var string $addModule;  
	 * @access private
	 * @since 12/3/07
	 */
	private $addModule = 'ui1';
	
	/**
	 * @var string $addAction;  
	 * @access private
	 * @since 12/3/07
	 */
	private $addAction = 'add_wiki_component';
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access private
	 * @since 11/30/07
	 */
	private function __construct () {
		$this->titleSearcher = new TitleSearcher;
		$this->templateResolver = new Segue_Wiki_TemplateResolver;
	}
	
	/**
	 * Set the module and action to use for viewing links.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 12/3/07
	 */
	public function setViewAction ($module, $action) {
		ArgumentValidator::validate($module, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($action, NonZeroLengthStringValidatorRule::getRule());
		
		$this->viewModule = $module;
		$this->viewAction = $action;
	}
	
	/**
	 * Set the module and action to use for adding component links.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 12/3/07
	 */
	public function setAddAction ($module, $action) {
		ArgumentValidator::validate($module, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($action, NonZeroLengthStringValidatorRule::getRule());
		
		$this->addModule = $module;
		$this->addAction = $action;
	}
	
	/**
	 * Parse the wiki-text and replace wiki markup with HTML markup.
	 * 
	 * @param string $text
	 * @param object SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 11/30/07
	 */
	public function parseText ($text, SiteComponent $siteComponent) {
		$text = $this->replaceInternalLinks($text, $siteComponent);
		$text = $this->replaceExternalLinks($text);		
		$text = $this->templateResolver->applyTemplates($text);
		return $text;
	}
	
	/**
	 * Answer a template
	 * 
	 * @param string $name
	 * @return object Segue_Wiki_Template
	 * @access public
	 * @since 7/14/08
	 */
	public function getTemplate ($name) {
		return $this->templateResolver->getTemplate($name);
	}
	
	/**
	 * Apply only two-way templates, those that can pull wiki markup out from
	 * HTML.
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function applyTwoWayTemplates ($text) {
		return $this->templateResolver->applyTemplates($text, true);
	}
	
	/**
	 * Convert HTML markup back into wiki-style template markup
	 * 
	 * @param string $text
	 * @return string
	 * @access public
	 * @since 7/14/08
	 */
	public function unapplyTemplates ($text) {
		return $this->templateResolver->unapplyTemplates($text);
	}
	
	
	/*********************************************************
	 * Internal methods
	 *********************************************************/
	
	/**
	 * Return a string in which all double-bracket wiki links are replaced with
	 * their HTML link equivalents.
	 * 
	 * @param string $text
	 * @param object SiteComponent $startingSiteComponent
	 * @return string
	 * @access private
	 * @since 11/30/07
	 */
	private function replaceInternalLinks ($text, SiteComponent $startingSiteComponent) {
		// loop through the text and look for wiki markup.
		preg_match_all('/(\[\[[^\]]+\]\])/', $text, $matches);
		
		// for each wiki link replace it with the HTML link text
		foreach ($matches[1] as $wikiLink) {
			$htmlLink = $this->makeHtmlLink($wikiLink, $startingSiteComponent);
			$text = str_replace($wikiLink, $htmlLink, $text);
		}
		
		return $text;
	}
	
	/**
	 * Return a string in which all single-bracket wiki-style links are replaced with
	 * their HTML link equivalents.
	 * 
	 * @param string $text
	 * @return string
	 * @access private
	 * @since 11/30/07
	 */
	private function replaceExternalLinks ($text) {
		// loop through the text and look for wiki external link markup.
		$regexp = "/
\[		# starting bracket

\s*		# optional whitespace

(
	[a-z]{2,7}	# Protocol i.e. http, ftp, rtsp, ...
	:\/\/		# separator
	[^\]\s]+	# the rest of the url
)

(?: [\s|]* ([^\]]+) )?	# optional display text

\s*		# optional whitespace

\]		# closing bracket
/xi";
		preg_match_all($regexp, $text, $matches);
// 		printpre($matches);
		
		// for each wiki link replace it with the HTML link text
		foreach ($matches[0] as $index => $wikiText) {
			ob_start();
			print "<a href='".$matches[1][$index]."'>";
			if (isset($matches[2][$index]) && $matches[2][$index])
				print $matches[2][$index];
			else
				print $matches[1][$index];
			print "</a>";
			
			$text = str_replace($wikiText, ob_get_clean(), $text);
		}
		
		return $text;
	}
		
	/**
	 * Add an inline field to show the wiki-style link for an item
	 * 
	 * @param string $title
	 * @return string The wiki markup
	 * @access public
	 * @since 11/30/07
	 */
	public function getMarkupExample ($title) {
		throw new UnimplementedException();
	}
	
	/**
	 * Convert a single wiki link [[SomeTitle]] to an html link.
	 *
	 * Forms:
	 *		[[Some Title]]
	 *		[[Some Title|alternate text to display]]
	 *		[[site:my_other_slot_name Some Title]]
	 *		[[site:my_other_slot_name Some Title|alternate text to display]]
	 *		[[node:12345 Some Title]]
	 *		[[node:12345|alternate text to display]]
	 *
	 * Local URL form:
	 *		[[localurl:module=modName&amp;action=actName&amp;param1=value1]]
	 * File URL form:
	 *		[[fileurl:repository_id=123&amp;asset_id=1234&amp;record_id=12345]]
	 * Unlike other forms, the local URL form and the file URL form do not write link tags. 
	 * They gets replaced with only the URL string itself.
	 * 
	 * @param string $wikiText
	 * @param object SiteComponent $startingSiteComponent
	 * @return string An HTML version of the link
	 * @access private
	 * @since 11/30/07
	 */
	private function makeHtmlLink ($wikiText, SiteComponent $startingSiteComponent) {
		$regexp = "/

^		# Anchor for the beginning of the line
\[\[	# The opening link tags

	\s*		# optional whitespace

	(?: site:([a-z0-9_\-]+) \s+ )?	# An optional designator for linking to another site
	
	([^\]#\|]+)	# The Title of the linked section, page, story
		
	(?: \s*\|\s* ([^\]]+) )?	# The optional link-text to display instead of the title

	\s*		# optional whitespace

\]\]	# The closing link tags
$		# Anchor for the end of the line

/xi";

		$siteOnlyRegexp = "/

^		# Anchor for the beginning of the line
\[\[	# The opening link tags

	\s*		# optional whitespace

	(?: site:([a-z0-9_\-]+) )?	# A designator for linking to another site
	
	(?: \s*\|\s* ([^\]]+) )?	# The optional link-text to display instead of the title

	\s*		# optional whitespace

\]\]	# The closing link tags
$		# Anchor for the end of the line

/xi";
	
		$nodeRegexp = "/

^		# Anchor for the beginning of the line
\[\[	# The opening link tags

	\s*		# optional whitespace

	(?: node:([a-z0-9_\-]+) )?	# A designator for linking to a particular node
	
	(?: \s*\|\s* ([^\]]+) )?	# The optional link-text to display instead of the title

	\s*		# optional whitespace

\]\]	# The closing link tags
$		# Anchor for the end of the line

/xi";
		
		$localUrlRegexp = "/

^		# Anchor for the beginning of the line
\[\[	# The opening link tags

	\s*		# optional whitespace

	(?: localurl:([^\]]+) )?	# A designator for linking to a local url

\]\]	# The closing link tags
$		# Anchor for the end of the line

/xi";

		$fileUrlRegexp = "/

^		# Anchor for the beginning of the line
\[\[	# The opening link tags

	\s*		# optional whitespace

	(?: fileurl:([^\]]+) )?	# A designator for linking to a local file

\]\]	# The closing link tags
$		# Anchor for the end of the line

/xi";
		
		// Check for a link only to a site [[site:my_other_site]]
		if (preg_match($siteOnlyRegexp, $wikiText, $matches)) {
			$slotName = $matches[1];
			
			if (isset($matches[2]) && $matches[2]) {
				$display = $matches[2];
				
				$slotMgr = SlotManager::instance();
				$slot = $slotMgr->getSlotByShortName($slotName);
				if (!$slot->siteExists()) {
					return $display." ?";
				}
			} else {
				$slotMgr = SlotManager::instance();
				$slot = $slotMgr->getSlotByShortName($slotName);
				if ($slot->siteExists()) {
					$director = $startingSiteComponent->getDirector();
					$site = $director->getSiteComponentById($slot->getSiteId()->getIdString());
					if (strlen($site->getDisplayName())) {
						$display = $site->getDisplayName();
					} else {
						$display = $slotName;
					}
				} else {
					return $slotName." ?";
				}
			}
			
			return $this->getSlotLink($slotName, $display);
		}
		
		// Check for a link to a node [[node:12345]]
		if (preg_match($nodeRegexp, $wikiText, $matches)) {
			$nodeIdString = $matches[1];
			
			try {
				if (isset($matches[2]) && $matches[2]) {
					$display = $matches[2];
					// Try getting the title to check if the node exists
					$title = $this->getNodeTitle($nodeIdString, $startingSiteComponent);
				} else {
					$display = $this->getNodeTitle($nodeIdString, $startingSiteComponent);
				}
			} catch (UnknownIdException $e) {
				if (isset($display))
					return $display." ?";
				else
					return $nodeIdString." ?";
			}
			
			return $this->getNodeLink($nodeIdString, $display);
		}
		
		// Check for a link to a local url:
		// [[localurl:module=modName&amp;action=actName&amp;param1=value1]]
		if (preg_match($localUrlRegexp, $wikiText, $matches)) {			
			preg_match_all('/(&(amp;)?)?([^&=]+)=([^&=]+)/', $matches[1], $paramMatches);
			$args = array();
			for ($i = 0; $i < count($paramMatches[1]); $i++) {
				$key = $paramMatches[3][$i];
				$value = $paramMatches[4][$i];
				
				if ($key == 'module')
					$module = $value;
				else if ($key == 'action')
					$action = $value;
				else
					$args[$key] = $value;
			}
			
			if (!isset($module))
				$module = 'ui1';
			if (!isset($action))
				$action = 'view';
			
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace(null);
			$newUrl = $harmoni->request->mkURLWithoutContext($module, $action, $args);
			$harmoni->request->endNamespace();
			
			return $newUrl->write();
		}
		
		// Check for a link to a file url:
		// [[fileurl:repository_id=123&amp;asset_id=1234&amp;record_id=12345]]
		if (preg_match($fileUrlRegexp, $wikiText, $matches)) {			
			preg_match_all('/(&(amp;)?)?([^&=]+)=([^&=]+)/', $matches[1], $paramMatches);
			$args = array();
			
			for ($i = 0; $i < count($paramMatches[1]); $i++) {
				$key = $paramMatches[3][$i];
				$value = $paramMatches[4][$i];
				
				switch ($key) {
					// Filtered Keys
					case 'module':
					case 'action':
						break;
					case 'repositoryId':
					case 'repository_id':
						$args['repository_id'] = $value;
						break;
					case 'assetId':
					case 'asset_id':
						$args['asset_id'] = $value;
						break;
					case 'recordId':
					case 'record_id':
						$args['record_id'] = $value;
						break;
					default:
						$args[$key] = $value;
				}
				
			}
			
			if (!isset($module))
				$module = 'repository';
			if (!isset($action))
				$action = 'viewfile';
			
			if (!isset($args['repository_id']))
				$args['repository_id'] = 'edu.middlebury.segue.sites_repository';
			
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace('polyphony-repository');
			$newUrl = $harmoni->request->mkURLWithoutContext($module, $action, $args);
			$harmoni->request->endNamespace();
			
			return $newUrl->write();
		}
		
		// Links of the form [[Assignments]]
		if (preg_match($regexp, $wikiText, $matches)) {
			
			if (isset($matches[1]) && $matches[1]) {
				$slotMgr = SlotManager::instance();
				$slot = $slotMgr->getSlotByShortName($matches[1]);
				if ($slot->siteExists()) {
					$director = $startingSiteComponent->getDirector();
					$startingSiteComponent = $director->getSiteComponentById($slot->getSiteId()->getIdString());
				} else {
					$title = $matches[2];
					if (isset($matches[3]) && $matches[3])
						$display = $matches[3];
					else
						$display = $title;
					
					return $display." ?";
				}
			}
			
			$title = $matches[2];
						
			if (isset($matches[3]) && $matches[3]) {
				$display = $matches[3];
			} else {
				$display = $title;
			}
			
			try {
				$nodeIdString = $this->titleSearcher->getNodeId($title, $startingSiteComponent);
				return $this->getNodeLink($nodeIdString, $display);
			} catch (UnknownTitleException $e) {
				return $this->getAddLink($title, $display, $startingSiteComponent);
			}
		}
		
		// If invalid, just return the wiki text.
		return $wikiText;
	}
	
	/**
	 * Answer a HTML link for a particular node Id.
	 * 
	 * @param string $nodeIdString
	 * @param string $display
	 * @return string
	 * @access private
	 * @since 12/3/07
	 */
	private function getNodeLink ($nodeIdString, $display) {
		ArgumentValidator::validate($nodeIdString, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($display, NonZeroLengthStringValidatorRule::getRule());
		
		$harmoni = Harmoni::instance();
		
		ob_start();
		print "<a href='";
		$harmoni->request->startNamespace(null);
		print SiteDispatcher::quickURL($this->viewModule, $this->viewAction, array('node' => $nodeIdString));
		$harmoni->request->endNamespace();
		print "'>";
		print $display;
		print "</a>";
		return ob_get_clean();
	}
	
	/**
	 * Answer a HTML link for a particular slot name.
	 * 
	 * @param string $nodeIdString
	 * @param string $display
	 * @return string
	 * @access private
	 * @since 12/3/07
	 */
	private function getSlotLink ($slotName, $display) {
		ArgumentValidator::validate($slotName, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($display, NonZeroLengthStringValidatorRule::getRule());
		
		$harmoni = Harmoni::instance();
		
		ob_start();
		print "<a href='";
		$harmoni->request->startNamespace(null);
		print SiteDispatcher::quickURL($this->viewModule, $this->viewAction, array('site' => $slotName, 'node' => null));
		$harmoni->request->endNamespace();
		print "'>";
		print $display;
		print "</a>";
		return ob_get_clean();
	}
	
	/**
	 * Answer an 'add new component' link
	 * 
	 * @param string $title
	 * @param string $display
	 * @param object SiteComponent $startingSiteComponent
	 * @return string
	 * @access public
	 * @since 12/3/07
	 */
	public function getAddLink ($title, $display, SiteComponent $startingComponent) {
		ArgumentValidator::validate($title, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($display, NonZeroLengthStringValidatorRule::getRule());
		
		$harmoni = Harmoni::instance();
		
		ob_start();
		print "<a href='";
		$harmoni->request->startNamespace(null);
		print $harmoni->request->quickURL($this->addModule, $this->addAction, array('title' => $title, 'refNode' => $startingComponent->getId()));
		$harmoni->request->endNamespace();
		print "'";
		print " title='"._('Add a new component.')."'";
		print ">";
		print $display;
		print " ?</a>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the title for a nodeId
	 * 
	 * @param string $nodeIdString
 	 * @param object SiteComponent $startingSiteComponent
	 * @return string
	 * @access public
	 * @since 12/3/07
	 */
	public function getNodeTitle ($nodeIdString, SiteComponent $startingSiteComponent) {
		$director = $startingSiteComponent->getDirector();
		$node = $director->getSiteComponentById($nodeIdString);
		if (strlen($node->getDisplayName()))
			return $node->getDisplayName();
		else
			return _("untitled");
	}
	
}

?>