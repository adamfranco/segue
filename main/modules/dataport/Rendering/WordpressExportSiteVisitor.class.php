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
		$this->doc->documentElement->setAttributeNS('http://www.w3.org/2000/xmlns/' ,'xmlns:wfw', 'http://wordpress.org/export/1.1/excerpt/');
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
			
			$parent = end($this->parentNavBlocks);
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_parent', $parent->getId()));
// 			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:menu_order', ));
			
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_type', $postType));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:post_password', ''));
			$element->appendChild($this->getElementNS("http://wordpress.org/export/1.1/", 'wp:is_sticky', '0'));

			
			// Content
			$pluginManager = Services::getService('PluginManager');
			$plugin = $pluginManager->getPlugin($siteComponent->getAsset());
			$shortContent = $plugin->executeAndGetMarkup(false, false);
			$longContent = $plugin->executeAndGetMarkup(false, true);
			$element->appendChild($this->getCDATAElementNS("http://purl.org/rss/1.0/modules/content/", 'content:encoded', $longContent));
			if ($shortContent != $longContent)
				$element->appendChild($this->getCDATAElementNS("http://purl.org/rss/1.0/modules/content/", 'excerpt:encoded', $longContent));
			
			// Files
			$this->recordAttachedMedia($siteComponent);
			
 			// Tags
// 			$this->recordTags($siteComponent);
			
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
			print $plugin->executeAndGetMarkup(false, true);
		
			// Comments
			$this->addComments($siteComponent, $this->currentPageElement);
		
			// Files
			$parentPage = end($this->parentNavBlocks);
			$this->recordAttachedMedia($siteComponent, $parentPage->getId());
	
			//tags
// 			$this->recordTags($siteComponent, $parentPage->getId());
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
		$content = $siteComponent->getOrganizer()->acceptVisitor($this);
		$element->appendChild($this->getCDATAElementNS("http://purl.org/rss/1.0/modules/content/", 'content:encoded', ob_get_clean()));
		
		// Nested Menus
		$nestedMenu = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenu)) 
			$nestedMenu->acceptVisitor($this);
		
		array_pop($this->parentNavBlocks);
		unset($this->currentPageElement);
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
			return $properties->getProperty('Id');
		else if ($properties = $agent->getPropertiesByType(new HarmoniType("Authentication", "edu.middlebury.harmoni", "Visitors")))
			return $properties->getProperty('identifier');
		else
			return $agent->getDisplayName();
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
	 * @return DOMElement
	 * @access protected
	 * @since 4/17/08
	 */
	protected function getTags (BlockSiteComponent $siteComponent) {
		$element = $this->doc->createElement('tags');
		if ($this->isAuthorizedToExport($siteComponent)) {
			$tags = array();
			$tagManager = Services::getService("Tagging");
			$item = HarmoniNodeTaggedItem::forId($siteComponent->getId(), 'segue');
			$tagInfoIterator = $tagManager->getTagInfoForItem($item);
			while($tagInfoIterator->hasNext())
				$element->appendChild($this->getTagApplication($tagInfoIterator->next()));
		}
		return $element;
	}

	/**
	 * Answer an element that represents a single tag application for a block.
	 * 
	 * @param object TagInfo $tagInfo
	 * @return DOMElement
	 * @access protected
	 * @since 1/17/08
	 */
	protected function getTagApplication (TagInfo $tagInfo) {
		$element = $this->doc->createElement('tag', $tagInfo->tag->getValue());
		
		$element->setAttribute('agent_id', $tagInfo->agentId->getIdString());
		$this->recordAgent($tagInfo->agentId);
		$element->setAttribute('create_date', $tagInfo->timestamp->asString());

		return $element;
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
		$file = $mediaAsset->getFiles()->next();
		
		$element = $this->channel->insertBefore($this->doc->createElement('item'), $this->endFiles);
		$element->appendChild($this->getElement('title', $mediaAsset->getDisplayName()));
		$element->appendChild($this->getElement('link', $file->getUrl()));
		$element->appendChild($this->getElement('guid', $file->getUrl()))->setAttribute('isPermaLink', 'false');
		$element->appendChild($this->getElement('description', $mediaAsset->getDescription()));
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
}
