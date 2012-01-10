<?php
/**
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomExportSiteVisitor.class.php,v 1.10 2008/04/18 20:39:15 achapin Exp $
 */ 

require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(HARMONI."/utilities/Harmoni_DOMDocument.class.php");
require_once(dirname(__FILE__).'/NumComponentsVisitor.class.php');
require_once(dirname(__FILE__).'/NumBlocksWithCommentsVisitor.class.php');

/**
 * This vistor will return an XML version of a site.
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomExportSiteVisitor.class.php,v 1.10 2008/04/18 20:39:15 achapin Exp $
 */
class WordpressExportSiteVisitor
	implements SiteVisitor
{
	
	/**
	 * @var DOMDocument $doc;  
	 * @access public
	 * @since 1/17/08
	 */
	public $doc;
	
	protected $outputBlocksAsPages = false;
	protected $blocksArePosts = false;
	protected $parentNavBlocks = array();
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/17/08
	 */
	public function __construct () {
		$this->doc = new Harmoni_DOMDocument('1.0', 'UTF-8');
		$this->doc->appendChild($this->doc->createElement('rss'));
		$this->doc->documentElement->setAttribute('version', "2.0");
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:excerpt', 'http://wordpress.org/export/1.1/excerpt/');
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:content', 'http://purl.org/rss/1.0/modules/content/');
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:wfw', 'http://wellformedweb.org/CommentAPI/');
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:wp', 'http://wordpress.org/export/1.1/');
		
		$this->channel = $this->doc->documentElement->appendChild($this->doc->createElement('channel'));
		
		// create some comment placeholders for organizing our elements
		$this->channel->appendChild($this->doc->createComment("Begin - Meta"));
		$this->endMeta = $this->channel->appendChild($this->doc->createComment("End - Meta"));
		
		$this->channel->appendChild($this->doc->createComment("Begin - Authors"));
		$this->endAuthors = $this->channel->appendChild($this->doc->createComment("End - Authors"));
		
		$this->channel->appendChild($this->doc->createComment("Begin - Categories"));
		$this->endCategories = $this->channel->appendChild($this->doc->createComment("End - Categories"));
		
		$this->channel->appendChild($this->doc->createComment("Begin - Tags"));
		$this->endTags = $this->channel->appendChild($this->doc->createComment("End - Tags"));
		
		$this->channel->appendChild($this->doc->createComment("Begin - Files"));
		$this->endFiles = $this->channel->appendChild($this->doc->createComment("End - Files"));
		
		$this->channel->insertBefore($this->getElement('generator', 'http://segue.middlebury.edu/'), $this->endMeta);
		
		$this->xpath = new DOMXPath($this->doc);
		$this->xpath->registerNamespace('wp', 'http://wordpress.org/export/1.1/');
	}
	
	/**
	 * Enable usage of a status indicator.
	 * 
	 * @param optional $message
	 * @return void
	 * @access public
	 * @since 3/24/08
	 */
	public function enableStatusOutput ($message = null) {
		if (is_null($message))
			$message = _("Exporting Site");
		$this->status = new StatusStars($message);
	}
	

/*********************************************************
 * Visitor Methods
 *********************************************************/

	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		// Skip plugins that just don't translate.
		$skippedTypes = array(
			new Type ('SeguePlugins', 'edu.middlebury', 'JoinSite'),
			new Type ('SeguePlugins', 'edu.middlebury', 'NextPrevious'),
			new Type ('SeguePlugins', 'edu.middlebury', 'Participation'),
			new Type ('SeguePlugins', 'edu.middlebury', 'RssFeed'),
			new Type ('SeguePlugins', 'edu.middlebury', 'Rsslinks'),
			new Type ('SeguePlugins', 'edu.middlebury', 'Tags'),
		);
		$assetType = $siteComponent->getAsset()->getAssetType();
		foreach ($skippedTypes as $skippedType) {
			if ($skippedType->isEqual($assetType))
				return;
		}
		
		// Rewrite any absolute file-URLs that didn't get localized properly
		$this->rewriteNonlocalFileUrls($siteComponent);
		
		// make each block its own item.
		if ($this->blocksArePosts || $this->outputBlocksAsPages) {
			if ($this->blocksArePosts)
				$postType = 'post';
			else
				$postType = 'page';
			
			$element = $this->channel->appendChild($this->doc->createElement('item'));
			$element->appendChild($this->getElement('title', $siteComponent->getDisplayName()));
			$element->appendChild($this->getElement('link', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())));
			$element->appendChild($this->getElement('guid', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())))->setAttribute('isPermaLink', 'false');
// 			$element->appendChild($this->getElement('description', $siteComponent->getDescription()));
			$element->appendChild($this->getElement('description', ''));
			$element->appendChild($this->getElement('pubDate', $siteComponent->getModificationDate()->format('r')));
			
			$agentUID = $this->recordAgent($siteComponent->getCreator());
			$element->appendChild($this->getElementNS("http://purl.org/dc/elements/1.1/", 'dc:creator', $agentUID));
			
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_id', $siteComponent->getId()));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date', $siteComponent->getModificationDate()->format('Y-m-d H:i:s')));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date_gmt', $siteComponent->getModificationDate()->asUTC()->format('Y-m-d H:i:s')));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_status', ($siteComponent->showComments()?'open':'closed')));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:ping_status', ($siteComponent->showComments()?'open':'closed')));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:status', 'publish'));
			
			if (!$this->blocksArePosts) {
				$parent = end($this->parentNavBlocks);
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $parent->getId()));
	// 			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', ));
			} else {
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', '0'));
			}
			
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', $postType));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));

			
			// Content
			$pluginManager = Services::getService('PluginManager');
			$plugin = $pluginManager->getPlugin($siteComponent->getAsset());
			$this->recordPluginExtras($siteComponent->getAsset());
			$shortContent = $plugin->executeAndGetMarkup(false, false);
			$longContent = $plugin->executeAndGetMarkup(false, true);
			$element->appendChild($this->getCDATAElementNS("http://purl.org/rss/1.0/modules/content/", 'content:encoded', $longContent));
// 			if ($shortContent != $longContent)
// 				$element->appendChild($this->getCDATAElementNS("http://wordpress.org/export/1.1/excerpt/", 'excerpt:encoded', $shortContent));
			
			// Files
			$this->recordAttachedMedia($siteComponent);
			
 			// Tags
			$this->recordTags($siteComponent, $element);
			
			// Categories
			if ($this->blocksArePosts) {
				$this->recordCategories($element, $this->parentNavBlocks);
			}
			
			// Comments
			$this->addComments($siteComponent, $element);
			
			
		}
		// Condense the blocks into a single page content.
		// Ignore top-level blocks
		else if (!empty($this->currentPageElement)) {
// 			var_dump($siteComponent->getDisplayType());
			switch ($siteComponent->getHeadingDisplayType()) {
				case 'Heading_1':
					$h = 'h1';
					break;
				case 'Heading_2':
					$h = 'h2';
					break;
				case 'Heading_3':
					$h = 'h3';
					break;
				default:
					$h = 'h4';
					break;
			}
			print "\n<".$h.">".$siteComponent->getDisplayName()."</".$h.">";
			
			$pluginManager = Services::getService('PluginManager');
			$plugin = $pluginManager->getPlugin($siteComponent->getAsset());
			$this->recordPluginExtras($siteComponent->getAsset());
			print $plugin->executeAndGetMarkup(false, true);
		
			// Comments
			$this->addComments($siteComponent, $this->currentPageElement);
		
			// Files
			$parentPage = end($this->parentNavBlocks);
			$this->recordAttachedMedia($siteComponent, $parentPage->getId());
	
			//tags
			$this->recordTags($siteComponent, $this->currentPageElement, $parentPage->getId());
		}
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		return $this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$element = $this->channel->appendChild($this->doc->createElement('item'));
		$element->appendChild($this->getElement('title', $siteComponent->getDisplayName()));
		$element->appendChild($this->getElement('link', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())));
		$element->appendChild($this->getElement('guid', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())))->setAttribute('isPermaLink', 'false');
		$element->appendChild($this->getElement('description', $siteComponent->getDescription()));
		$element->appendChild($this->getElement('pubDate', $siteComponent->getModificationDate()->format('r')));
		
		$agentUID = $this->recordAgent($siteComponent->getCreator());
		$element->appendChild($this->getElementNS("http://purl.org/dc/elements/1.1/", 'dc:creator', $agentUID));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_id', $siteComponent->getId()));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date', $siteComponent->getModificationDate()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date_gmt', $siteComponent->getModificationDate()->asUTC()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_status', ($siteComponent->showComments()?'open':'closed')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:ping_status', ($siteComponent->showComments()?'open':'closed')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:status', 'publish'));
		
		$parent = end($this->parentNavBlocks);
		if ($parent)
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $parent->getId()));
		else
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', '0'));
// 			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', ));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', 'page'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));

				
		$numBlockWithCommentsVisitor = new NumBlocksWithCommentsVisitor($siteComponent);
		$this->outputBlocksAsPages = ($numBlockWithCommentsVisitor->getNumberOfBlocksWithComments() > 1);
		
		array_push($this->parentNavBlocks, $siteComponent);
		$this->currentPageElement = $element;
		ob_start();
		$siteComponent->getOrganizer()->acceptVisitor($this);
		$pageContent = ob_get_clean();
		$element->appendChild($this->getCDATAElementNS("http://purl.org/rss/1.0/modules/content/", 'content:encoded', $pageContent));
		
		// Nested Menus
		$nestedMenu = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$nestedMenu->acceptVisitor($this);
		
		array_pop($this->parentNavBlocks);
		unset($this->currentPageElement);
		
		// Remove this page if it isn't needed.
		// It isn't needed if there are no sub-pages and it doesn't have any page content.
		$query = 'count(item[wp:post_parent = "'.$siteComponent->getId().'"])';
		$numSubpages = $this->xpath->evaluate($query, $this->channel);
		if (!$numSubpages && empty($pageContent)) {
			$element->parentNode->removeChild($element);
		}
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$this->channel->insertBefore($this->getElement('title', $siteComponent->getDisplayName()), $this->endMeta);
		$this->channel->insertBefore($this->getElement('link', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())), $this->endMeta);
		$this->channel->insertBefore($this->getElement('description', $siteComponent->getDescription()), $this->endMeta);
		$this->channel->insertBefore($this->getElement('pubDate', date('r')), $this->endMeta);
		$this->channel->insertBefore($this->getElement('language', 'en'), $this->endMeta);
		$this->channel->insertBefore($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:wxr_version', '1.1'), $this->endMeta);
		$this->channel->insertBefore($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:base_site_url', MYURL), $this->endMeta);
		$this->channel->insertBefore($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:base_blog_url', SiteDispatcher::getSitesUrlForSiteId($siteComponent->getId())), $this->endMeta);
		
		$siteComponent->getOrganizer()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		$output = array();
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				try {
					$output = array_merge($output, $child->acceptVisitor($this));
				} catch (PermissionDeniedException $e) {
				}
			}
		}
		return $output;
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		return $this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		// Set a flag that the blocks under this organizer should be posts
		// Determination is made based on the order of the content.
		$this->blocksArePosts = in_array($siteComponent->sortMethod(), array('create_date_asc', 'create_date_desc', 'mod_date_asc', 'mod_date_desc'));
		
		return $this->visitOrganizerChildren($siteComponent);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		if (!$this->isAuthorizedToExport($siteComponent))
			throw new PermissionDeniedException();
		
		return $this->visitOrganizerChildren($siteComponent);
	}
	
/*********************************************************
 * Helper Methods
 *********************************************************/

	/**
	 * Record an agent so that their information can be passed on to new systems.
	 * 
	 * @param object Id $agentId
	 * @return string
	 * @access protected
	 * @since 1/18/08
	 */
	protected function recordAgent (Id $agentId) {
		$agentMgr = Services::getService('Agent');
		$agent = $agentMgr->getAgentOrGroup($agentId);
				
		$query = 'count(wp:author[wp:author_id = "'.$agentId->getIdString().'"])';
		if (!$this->xpath->evaluate($query, $this->channel)) {
			$element = $this->channel->insertBefore($this->doc->createElementNS('http://wordpress.org/export/1.1/', 'wp:author'), $this->endAuthors);
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_id', $agentId->getIdString()));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_login', $this->getAgentUID($agent)));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_email', $this->getAgentEmail($agent)));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_display_name', $agent->getDisplayName()));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_first_name', $this->getAgentFirstName($agent)));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:author_last_name', $this->getAgentLastName($agent)));
		}
		
		return $this->getAgentUID($agent);
	}
	
	/**
	 * Answer a UID for an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 */
	protected function getAgentUID (Agent $agent) {
		if ($properties = $agent->getPropertiesByType(new HarmoniType("GroupProperties", "edu.middlebury", "CAS Properties")))
			return strtolower($properties->getProperty('Id'));
		else if ($properties = $agent->getPropertiesByType(new HarmoniType("Authentication", "edu.middlebury.harmoni", "Visitors")))
			return strtolower($properties->getProperty('identifier'));
		else
			return strtolower($agent->getDisplayName());
	}
	
	/**
	 * Answer a email for an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 */
	protected function getAgentEmail (Agent $agent) {
		if ($properties = $agent->getPropertiesByType(new HarmoniType("GroupProperties", "edu.middlebury", "CAS Properties")))
			return $properties->getProperty('EMail');
		else if ($properties = $agent->getPropertiesByType(new HarmoniType("Authentication", "edu.middlebury.harmoni", "Visitors")))
			return $properties->getProperty('email');
		else
			return null;
	}
	
	/**
	 * Answer a first name for an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 */
	protected function getAgentFirstName (Agent $agent) {
		if ($properties = $agent->getPropertiesByType(new HarmoniType("GroupProperties", "edu.middlebury", "CAS Properties")))
			return $properties->getProperty('FirstName');
		else
			return null;
	}
	
	/**
	 * Answer a last name for an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 */
	protected function getAgentLastName (Agent $agent) {
		if ($properties = $agent->getPropertiesByType(new HarmoniType("GroupProperties", "edu.middlebury", "CAS Properties")))
			return $properties->getProperty('LastName');
		else
			return null;
	}
	
	/**
	 * Answer an element with a text section
	 * 
	 * @param string $elementName
	 * @param string $data
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getElement ($elementName, $data) {
		$string = String::withValue($data);
		$string->makeUtf8();
		$element = $this->doc->createElement($elementName, $string->asString());
// 		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * Answer an element with a text section
	 * 
	 * @param string $elementName
	 * @param string $data
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getElementNS ($namespaceURI, $elementName, $data) {
		$string = String::withValue($data);
		$string->makeUtf8();
		$element = $this->doc->createElementNS($namespaceURI, $elementName, $string->asString());
// 		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * Answer an element with a single CDATA section
	 * 
	 * @param string $elementName
	 * @param string $data
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getCDATAElement ($elementName, $data) {
		$element = $this->doc->createElement($elementName);
		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * Answer an element with a single CDATA section
	 * 
	 * @param string $elementName
	 * @param string $data
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getCDATAElementNS ($namespaceURI, $elementName, $data) {
		$element = $this->doc->createElementNS($namespaceURI, $elementName);
		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * Add the child cells of an organizer
	 * 
	 * @param OrganizerSiteComponent $siteComponent
	 * @param DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/17/08
	 */
	protected function visitOrganizerChildren (OrganizerSiteComponent $siteComponent) {
		$output = array();
		for ($i = 0; $i < $siteComponent->getTotalNumberOfCells(); $i++) {
			$child = $siteComponent->getSubComponentForCell($i);
			if ($child) {
				try {
					$output[] = $child->acceptVisitor($this);
				} catch (PermissionDeniedException $e) {
				}
			}
		}
		return $output;
	}
	
	/**
	 * add an elements that represents the comments attached to a block.
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @access protected
	 */
	protected function addComments (BlockSiteComponent $siteComponent, DOMElement $item) {
		if ($this->isAuthorizedToExport($siteComponent)) {
			$commentMgr = CommentManager::instance();
			$idMgr = Services::getService("Id");
			$comments = $commentMgr->getAllComments($idMgr->getId($siteComponent->getId()));
			while($comments->hasNext())
				$item->appendChild($this->getComment($comments->next()));
		}
	}
	
	/**
	 * Answer an element that represents a comment.
	 * 
	 * @param object CommentNode $comment
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getComment (CommentNode $comment) {
		$element = $this->doc->createElementNS("http://wordpress.org/export/1.1/", 'wp:comment');
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_id', $comment->getId()));
		
		$author = $comment->getAuthor();
		$authorUID = $this->recordAgent($author->getId());
		if ($properties = $author->getPropertiesByType(new HarmoniType("GroupProperties", "edu.middlebury", "CAS Properties")))
			$email = $properties->getProperty('EMail');
		else if ($properties = $author->getPropertiesByType(new HarmoniType("Authentication", "edu.middlebury.harmoni", "Visitors")))
			$email = $properties->getProperty('email');
		else
			$email = '';
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_author', $author->getDisplayName()));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_author_email', $email));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_user_id', $author->getId()));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_date', $comment->getCreationDate()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_date_gmt', $comment->getCreationDate()->asUTC()->format('Y-m-d H:i:s')));

		
		$content = "<h3>".$comment->getSubject()."</h3>";
		$pluginManager = Services::getService('PluginManager');
		$plugin = $pluginManager->getPlugin($comment->getAsset());
		$this->recordPluginExtras($comment->getAsset());
		$content .= $plugin->executeAndGetMarkup(false, true);
		$element->appendChild($this->getCDATAElementNS("http://wordpress.org/export/1.1/", 'wp:comment_content', $content));
		
		$parent = $comment->getParentComment();
		if ($parent) 
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_parent', $parent->getId()));
		else
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_parent', '0'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_approved', '1'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_type', ''));
		
		return $element;
	}

	/**
	 * Answer an element that represents the tags attached to a block.
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @param DOMElement $element
	 * @param optional string $pageId
	 * @access protected
	 */
	protected function recordTags (BlockSiteComponent $siteComponent, DOMElement $element, $pageId = null) {
		if (is_null($pageId))
			$pageId = $siteComponent->getId();
		
		$tagManager = Services::getService("Tagging");
		$item = HarmoniNodeTaggedItem::forId($siteComponent->getId(), 'segue');
		$tagInfoIterator = $tagManager->getTagInfoForItem($item);
		while($tagInfoIterator->hasNext()) {
			$tagInfo = $tagInfoIterator->next();
			
			// Record the tag in the channel.
			$this->recordTag($tagInfo);
			
			// Just add the tag once to our element
			$query = 'count(category[@domain = "post_tag" and @nicename = "'.$tagInfo->tag->getValue().'"])';
			if (!$this->xpath->evaluate($query, $element)) {
				$tagElement = $element->appendChild($this->getCDATAElement('category', $tagInfo->tag->getValue()));
				$tagElement->setAttribute('domain', 'post_tag');
				$tagElement->setAttribute('nicename', $tagInfo->tag->getValue());
			}
		}
	}

	/**
	 * Answer an element that represents a single tag application for a block.
	 * 
	 * @param object TagInfo $tagInfo
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function recordTag (TagInfo $tagInfo) {
		// Generate a synthetic id for the tag.
		static $tagId = 100000000000;
		$query = 'count(wp:tag[wp:tag_slug = "'.$tagInfo->tag->getValue().'"])';
		if (!$this->xpath->evaluate($query, $this->channel)) {
			$element = $this->channel->insertBefore($this->doc->createElementNS('http://wordpress.org/export/1.1/', 'wp:tag'), $this->endTags);
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:term_id', $tagId));
			$tagId++;
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:tag_slug', $tagInfo->tag->getValue()));
			$element->appendChild($this->getCDATAElementNS('http://wordpress.org/export/1.1/', 'wp:tag_name', $tagInfo->tag->getValue()));
		}
	}
	
	/**
	 * Answer an element that represents the tags attached to a block.
	 * 
	 * @param DOMElement $element
	 * @param array $parentNavBlocks
	 * @access protected
	 */
	protected function recordCategories (DOMElement $element, array $parentNavBlocks) {
		$parentId = 0;
		foreach ($parentNavBlocks as $navBlock) {			
			// Record the tag in the channel.
			$this->recordCategory($navBlock, $parentId);
			
			$slug = $this->getUniqueSlug($navBlock);
			
			// Just add the tag once to our element
			$query = 'count(category[@domain = "category" and @nicename = "'.$slug.'"])';
			if (!$this->xpath->evaluate($query, $element)) {
				$tagElement = $element->appendChild($this->getCDATAElement('category', $navBlock->getDisplayName()));
				$tagElement->setAttribute('domain', 'category');
				$tagElement->setAttribute('nicename', $slug);
			}
			
			$parentId = $navBlock->getId();
		}
	}

	/**
	 * Answer an element that represents a single tag application for a block.
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @access protected
	 * @since 1/17/08
	 */
	protected function recordCategory (NavBlockSiteComponent $siteComponent, $parentId = '') {
		
		$query = 'count(wp:category[wp:term_id = "'.$siteComponent->getId().'"])';
		if (!$this->xpath->evaluate($query, $this->channel)) {
			$element = $this->channel->insertBefore($this->doc->createElementNS('http://wordpress.org/export/1.1/', 'wp:category'), $this->endCategories);
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:term_id', $siteComponent->getId()));			
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:category_nicename', $this->getUniqueSlug($siteComponent)));
			$element->appendChild($this->getCDATAElementNS('http://wordpress.org/export/1.1/', 'wp:cat_name', $siteComponent->getDisplayName()));
			$element->appendChild($this->getElementNS('http://wordpress.org/export/1.1/', 'wp:category_parent', $parentId));
		}
	}
	
	/**
	 * Answer a unique slug for the site component
	 * 
	 * @param BlockSiteComponent $siteComponent
	 * @return string
	 */
	public function getUniqueSlug (BlockSiteComponent $siteComponent) {
		// append the id for uniqueness.
		$slug = strtolower($siteComponent->getDisplayName().' '.$siteComponent->getId());
		$slug = preg_replace('/[^a-z0-9]/', '-', $slug);
		$slug = preg_replace('/-+/', '-', $slug);
		return $slug;
	}
	
	/**
	 * Record any extra files that are used by plugins
	 * 
	 * @param Asset $asset
	 * @return void
	 */
	public function recordPluginExtras (Asset $asset) {
		$audioType = new Type ('SeguePlugins', 'edu.middlebury', 'AudioPlayer');
		$downloadType = $audioType = new Type ('SeguePlugins', 'edu.middlebury', 'Download');
		if ($asset->getAssetType()->isEqual($audioType) || $asset->getAssetType()->isEqual($audioType)) {
			
			$query = 'count(item[wp:post_type = "attachment" and title = "downarrow.gif"])';
			if (!$this->xpath->evaluate($query, $this->channel)) {
				$url = MYPATH.'/images/downarrow.gif';
				$element = $this->channel->insertBefore($this->doc->createElement('item'), $this->endFiles);
				$element->appendChild($this->getElement('title', "downarrow.gif"));
				$element->appendChild($this->getElement('link', $url));
				$element->appendChild($this->getElement('guid', $url))->setAttribute('isPermaLink', 'false');
				$element->appendChild($this->getElement('description', 'Download icon.'));
				$element->appendChild($this->getElement('pubDate', date('r')));
				
				$agentUID = $this->recordAgent($asset->getCreator());
				$element->appendChild($this->getElementNS("http://purl.org/dc/elements/1.1/", 'dc:creator', '0'));
				
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_id', '0'));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date', date('Y-m-d H:i:s')));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date_gmt', date('Y-m-d H:i:s')));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_status', 'closed'));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:ping_status', 'closed'));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:status', 'publish'));
				
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $asset->getId()->getIdString()));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', '0'));
				
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', 'attachment'));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));
				
				$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:attachment_url', $url));
			}
		}
	}
	
	/**
	 * Answer an XML representation of the files attached to a site component.
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @access protected
	 */
	protected function recordAttachedMedia (BlockSiteComponent $siteComponent, $pageId = null) {
		if (is_null($pageId))
			$pageId = $siteComponent->getId();
		
		$mediaAssetType = new Type ('segue', 'edu.middlebury', 'media_file',
			'A file that is uploaded to Segue.');
		$children = $siteComponent->getAsset()->getAssets();
		while ($children->hasNext()) {
			$child = $children->next();
			if ($mediaAssetType->isEqual($child->getAssetType())) {
				try {
					$this->recordMediaAsset($child, $pageId);
				} catch (PermissionDeniedException $e) {
				} catch (OperationFailedException $e) {
				}
			}
		}		
	}
	
	/**
	 * Answer an XML representation of a media file.
	 * 
	 * @param object Asset $asset
	 * @param string $parentId
	 * @access protected
	 */
	protected function recordMediaAsset (Asset $asset, $parentId) {
		$mediaAsset = MediaAsset::withAsset($asset);
		if (!$mediaAsset->getFiles()->hasNext())
			return;
		
		$file = $mediaAsset->getFiles()->next();
		
		$element = $this->channel->insertBefore($this->doc->createElement('item'), $this->endFiles);
		$element->appendChild($this->getElement('title', $mediaAsset->getDisplayName()));
		$element->appendChild($this->getElement('link', $file->getUrl()));
		$element->appendChild($this->getElement('guid', $file->getUrl()))->setAttribute('isPermaLink', 'false');
		$element->appendChild($this->getElement('description', $mediaAsset->getDescription()));
		$element->appendChild($this->getCDATAElementNS("http://wordpress.org/export/1.1/excerpt/", 'excerpt:encoded', $mediaAsset->getDescription()));
		$element->appendChild($this->getElement('pubDate', $mediaAsset->getModificationDate()->format('r')));
		
		$agentUID = $this->recordAgent($asset->getCreator());
		$element->appendChild($this->getElementNS("http://purl.org/dc/elements/1.1/", 'dc:creator', $agentUID));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_id', $mediaAsset->getId()));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date', $mediaAsset->getModificationDate()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date_gmt', $mediaAsset->getModificationDate()->asUTC()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_status', 'closed'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:ping_status', 'closed'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:status', 'publish'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $parentId));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', '0'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', 'attachment'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:attachment_url', $file->getUrl()));
		
		// Thumbnail
		$element = $this->channel->insertBefore($this->doc->createElement('item'), $this->endFiles);
		if ($file->getThumbnailMimeType()) {
			$mime = Services::getService("MIME");
			$thumbExtension = $mime->getExtensionForMIMEType($file->getThumbnailMimeType());
		} else {
			$thumbExtension = 'png';
		}
		$element->appendChild($this->getElement('title', $mediaAsset->getDisplayName().' - thumbnail'));
		$element->appendChild($this->getElement('link', $file->getThumbnailUrl()));
		$element->appendChild($this->getElement('guid', $file->getThumbnailUrl()))->setAttribute('isPermaLink', 'false');
		$element->appendChild($this->getElement('description', $mediaAsset->getDescription()));
		$element->appendChild($this->getElement('pubDate', $mediaAsset->getModificationDate()->format('r')));
		
		$agentUID = $this->recordAgent($asset->getCreator());
		$element->appendChild($this->getElementNS("http://purl.org/dc/elements/1.1/", 'dc:creator', $agentUID));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_id', intval($mediaAsset->getId()->getIdString()) + 200000000000));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date', $mediaAsset->getModificationDate()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_date_gmt', $mediaAsset->getModificationDate()->asUTC()->format('Y-m-d H:i:s')));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:comment_status', 'closed'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:ping_status', 'closed'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:status', 'publish'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $parentId));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', '0'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', 'attachment'));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));
		
		$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:attachment_url', $file->getThumbnailUrl()));
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 * @since 1/18/08
	 */
	protected function isAuthorizedToExport (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		// Since view AZs cascade up, just check at the node.
		return $authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $siteComponent->getQualifierId());
	}
	
	/**
	 * Answer true if the current user is authorized to export this node.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access protected
	 * @since 1/18/08
	 */
	protected function isAuthorizedToExportComments (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		return $authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view_comments'), $siteComponent->getQualifierId());
	}
	
	/**
	 * Rewrite file urls that weren't properly localized.
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return void
	 */
	protected function rewriteNonlocalFileUrls (BlockSiteComponent $siteComponent) {
		static $baseUrls;
		if (empty($baseUrls)) {
			$baseUrls = array(
				'http://segue.middlebury.edu',
				'https://segue.middlebury.edu',
				'http://seguecommunity.middlebury.edu',
				'https://seguecommunity.middlebury.edu',
			);
			foreach (SlotAbstract::getLocationCategories() as $locationCategory) {
				$baseUrls[] = rtrim(SiteDispatcher::getBaseUrlForLocationCategory($locationCategory), '/');
			}
			$baseUrls = array_unique($baseUrls);
		}
		
		$content = $siteComponent->getAsset()->getContent();
		foreach ($baseUrls as $baseUrl) {
			if (preg_match_all('#[\'"]'.$baseUrl.'/repository/(viewfile|viewfile_flash|viewthumbnail|viewthumbnail_flash)/polyphony-repository___repository_id/edu.middlebury.segue.sites_repository/polyphony-repository___asset_id/([0-9]+)/polyphony-repository___record_id/([0-9]+)(?:polyphony-repository___file_name/(.+))?[\'"]#', $content->asString(), $matches, PREG_SET_ORDER)) {
				foreach ($matches as $m) {
					$urlParts = array(
						'module' => 'repository',
						'action' => $m[1],
						'polyphony-repository___repository_id' => 'edu.middlebury.segue.sites_repository',
						'polyphony-repository___asset_id' => $m[2],
						'polyphony-repository___record_id' => $m[3],
					);
					if (!empty($m[4])) {
						$urlParts['polyphony-repository___file_name'] = $m[4];
					}
					// File URLs should have the filename appended.
					else if ($m[1] == 'viewfile') {
						try {
							$file = MediaFile::withIdStrings('edu.middlebury.segue.sites_repository', $m[2], $m[3]);
							$urlParts['polyphony-repository___file_name'] = $file->getFilename();
						} catch (UnknownIdException $e) {
						}
					}
					$url = http_build_query($urlParts, '', '&amp;');
					$content->_setValue(str_replace($m[0], '"'.'[[localurl:'.$url.']]'.'"', $content->value()));
				}
			}
		}
	}
}
