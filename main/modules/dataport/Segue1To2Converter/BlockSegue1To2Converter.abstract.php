<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSegue1To2Converter.abstract.php,v 1.13 2008/04/21 19:53:34 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/Segue1To2Converter.abstract.php");
require_once(dirname(__FILE__)."/TextCommentSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/DownloadCommentSegue1To2Converter.class.php");

/**
 * An abstract converter for content blocks.
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSegue1To2Converter.abstract.php,v 1.13 2008/04/21 19:53:34 achapin Exp $
 */
abstract class BlockSegue1To2Converter
	extends Segue1To2Converter
{
	/**
	 * If necessary, re-write an Id to put it into a particular namespace, i.e. section_.
	 *
	 * Override this method in child classes as necessary.
	 * 
	 * @param string $idString
	 * @return string
	 * @access protected
	 * @since 2/13/08
	 */
	protected function getIdString ($idString) {
		return 'story_'.$idString;
	}
	
	/**
	 * Convert the source element and return our resulting element
	 * 
	 * @return DOMElement
	 * @access public
	 * @since 2/12/08
	 */
	public function convert () {
		$element = $this->doc->createElement('Block');
		// Temporarily append to the document element to enable searching
		$this->doc->documentElement->appendChild($element);
		
		$media = $this->doc->createElement('attachedMedia');
		
		$this->addId($element);
		
		$element->appendChild($this->createMyPluginType());
		$element->appendChild($this->getDisplayNameElement());
		
		// Hide the title for this block if there is no title specified.
		try {
			$name = $this->getStringValue($this->getSingleSourceElement('./title', $this->sourceElement));
			if (!strlen(trim($name)))
				$element->setAttribute('showDisplayNames', 'false');
		} catch (MissingNodeException $e) {
			$element->setAttribute('showDisplayNames', 'false');
		}
		
		$element->appendChild($this->getDescriptionElement($media));
		
		$this->addRoles($element);
		
		$this->addCreationInfo($element);
		
		$element->appendChild($this->getContentElement($media));
		
		$history = $this->getHistoryElement($media);
		if (!is_null($history))
			$element->appendChild($history);
		
		$comments = $element->appendChild($this->doc->createElement('comments'));
		
		$element->appendChild($media);
				
		// Comments
		$this->setCommentsEnabled($element);
		$this->addComments($comments);
		
		//tags
		$this->addTags($element);
		
		return $element;
	}
	
	/**
	 * Answer a new Type DOMElement for this plugin
	 * 
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function createMyPluginType ();
	
	/**
	 * Answer a description element for this Block
	 * 
	 * @param object DOMElement $mediaElement
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function getDescriptionElement (DOMElement $mediaElement);
	
	/**
	 * Answer a element that represents the content for this Block
	 * 
	 * @return object DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	abstract protected function getContentElement (DOMElement $mediaElement);
	
	/**
	 * Answer a element that represents the history for this Block, null if not
	 * supported. Override this to return null if history isn't supported.
	 * 
	 * @return object DOMElement $mediaElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getHistoryElement (DOMElement $mediaElement) {
		$element = $this->doc->appendChild($this->doc->createElement('history'));
		$versions = $this->sourceXPath->query('./history/versions/version', $this->sourceElement);
		foreach ($versions as $version) {
			$element->appendChild($this->getVersion($version, $mediaElement));
		}
		
		return $element;
	}
	
	/**
	 * Answer a version from the history
	 * 
	 * @param object DOMElement $versionElement
	 * @param object DOMElement $mediaElement
	 * @return DOMElement
	 * @access protected
	 * @since 2/13/08
	 */
	protected function getVersion ( DOMElement $versionElement, DOMElement $mediaElement ) {
		$element = $this->doc->appendChild($this->doc->createElement('entry'));
		$element->setAttribute('number', $versionElement->getAttribute('number'));
// 		$element->setAttribute('id', $versionElement->getAttribute('id'));
		if ($versionElement->getAttribute('agent_id'))
			$element->setAttribute('agent_id', $this->addAgent($versionElement->getAttribute('agent_id')));
		else
			$element->setAttribute('agent_id', $this->addAgent('unknown'));
		
		$timeStamp = DateAndTime::fromString($versionElement->getAttribute('time_stamp'));
		$element->setAttribute('time_stamp', $timeStamp->asString());
		
		$element->appendChild($this->doc->importNode(
			$this->getSingleSourceElement('./comment', $versionElement), true));
		
		// Use a temporary Converter to extract the version 
		$class = get_class($this);
		$tempBlock = new $class($versionElement, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
		$element->appendChild($tempBlock->getContentElement($mediaElement));
		
		return $element;
	}

	/**
	 * Add Comments to a comments element
	 * 
	 * @param object DOMElement $commentsElement
	 * @return void
	 * @access protected
	 * @since 2/11/08
	 */
	protected function addComments (DOMElement $commentsElement) {
		$comments = $this->sourceXPath->query('./discussion/discussion_node', $this->sourceElement);
		foreach ($comments as $comment) {
			$this->addComment($comment, $commentsElement);
		}
	}

	/**
	 * Add tags to this block
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 2/11/08
	 */
	protected function addTags (DOMElement $element) {
		$sourceTagElements = $this->sourceXPath->query('./tags/tag', $this->sourceElement);		
		$tagsElement = $element->appendChild($this->doc->createElement('tags'));

		foreach ($sourceTagElements as $sourceTagElement) {
			if ($sourceTagElement->nodeValue) {
				$sourceTag = preg_replace("/[^a-z0-9_]/i", "_", urldecode($sourceTagElement->nodeValue));				
				$tagElement = $tagsElement->appendChild($this->doc->createElement('tag', $sourceTag));
				if ($sourceTagElement->getAttribute('agent_id'))
					$tagElement->setAttribute('agent_id', $this->addAgent($sourceTagElement->getAttribute('agent_id')));
				else
					$tagElement->setAttribute('agent_id', $this->addAgent('unknown'));
				$tagElement->setAttribute('create_date', $sourceTagElement->getAttribute('time_stamp'));
			}
		}
	}

	
	/**
	 * Add the comments enabled attribute if needed
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 3/19/08
	 */
	protected function setCommentsEnabled (DOMElement $element) {
		if (!$this->pageCommentsEnabled()) {
			$discussionNodes = $this->sourceXPath->query('./discussion', $this->sourceElement);
			if ($discussionNodes->length)
				$element->setAttribute('commentsEnabled', 'true');
		}
	}
	
	/**
	 * Answer true if all blocks in the page have comments enabled and that
	 * the commentsEnabled setting should be made on the page level or higher.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 3/19/08
	 */
	protected function pageCommentsEnabled () {
		$storyNodes = $this->sourceXPath->query('../story | ../file | ../link | ../rss | ../image', $this->sourceElement);
		$discussionNodes = $this->sourceXPath->query('../*/discussion', $this->sourceElement);
		if ($storyNodes->length == $discussionNodes->length)
			return true;
		else
			return false;
	}
	
	/**
	 * Add a comment node
	 * 
	 * @param object DOMElement $sourceElement
	 * @param object DOMElement $parentElement
	 * @return void
	 * @access protected
	 * @since 2/11/08
	 */
	protected function addComment (DOMElement $sourceElement, DOMElement $parentElement) {
		try {
			$file = trim($this->getStringValue($this->getSingleSourceElement('./file', $sourceElement)));
		} catch (MissingNodeException $e) {
			$file = '';
		}
		
		if ($file)
			$converter = new DownloadCommentSegue1To2Converter($sourceElement, $this->sourceXPath,
				$this->doc, $this->xpath, $this->director);
		else
			$converter = new TextCommentSegue1To2Converter($sourceElement, $this->sourceXPath,
				$this->doc, $this->xpath, $this->director);
		
		$parentElement->appendChild($converter->convert());
	}
	
	/**
	 * Search an HTML string for a media reference and attach 
	 * 
	 * @param string $html
	 * @param object DOMElement $attachedMedia
	 * @return string The HTML with any re-writing done
	 * @access protected
	 * @since 2/6/08
	 */
	protected function attachMediaFromHtml ($html, DOMElement $attachedMedia) {
		// Search through an HTML string and find any cases where there are links
		// to media files.
		preg_match_all('/\\\?\[\\\?\[mediapath\\\?\]\\\?\]\/([^\'"]+)/', $html, $matches);
		
		for ($i = 0; $i < count($matches[1]); $i++) {
			$link = $matches[0][$i];
			$filename = urldecode($matches[1][$i]);
			
			// Attach the media files and get the new id.
			try {
				$fileUrlVal = $this->attachFile($filename, $attachedMedia);
				
				// Re-Write the media url to use the new id.
				$html = $this->str_replace_once($link, "[[fileurl:".$fileUrlVal."]]", $html);
			}
			// If the HTML references a file that doesn't exist, just put a link
			// to a missing file action.
			catch (MissingNodeException $e) {
				$html = $this->str_replace_once($link, 
					"[[localurl:&amp;module=media&amp;action=missing&amp;filename="
						.rawurlencode($filename)."]]",
					$html, 1);
			}
			// If the HTML references a file that doesn't exist, just put a link
			// to a missing file action.
			catch (Segue1To2_MissingFileException $e) {
				$html = $this->str_replace_once($link, 
					"[[localurl:&amp;module=media&amp;action=missing&amp;filename="
						.rawurlencode($filename)."]]",
					$html, 1);
			}
		}
	
		return $html;
	}
	
	/**
	 * Re-Write local links in the html.
	 * 
	 * @param string $html
	 * @return string The converted html
	 * @access protected
	 * @since 2/14/08
	 */
	protected function rewriteLocalLinks ($html) {
		// remove any extra back-slashes.
		$html = str_replace('\\[\\[linkpath\\]\\]', '[[linkpath]]', $html);
		
		// Search through an HTML string and find any cases where there are links
		// that use the [[linkpath]] syntax
		$pattern = '/
		
		\[\[linkpath\]\] 					# linkpath tag
		
		(?:
			\/								# slash
			
			(?:
				(?: index\.php\?([^\'"><]+))	# GET url
				|								# or
				(?: sites\/([^\'"\<\>\/]+))		# sites\/slotname url
			)
		)?
		
		/xi';
// 		if (!preg_match_all($pattern, $html, $matches))
// 			return $html;
		preg_match_all($pattern, $html, $matches);
		
		$paramPattern = '/

		(?: &amp;|& )?						# leading ampersand
		([^=&]+)							# key
		=
		([^&]+)								# value
		
		/xi';
		$paramsToIgnore = array(
			'action'
		);
		$valuesToIgnore = array(
			'\\[\\[site\\]\\]',
			'[[site]]',
		);
		
		for ($i = 0; $i < count($matches[0]); $i++) {
			$link = $matches[0][$i];
			$params = array();
			
			$module = 'view';
			$action = 'html';
			
			// GET URLs
			if ($matches[1][$i]) {
				preg_match_all($paramPattern, $matches[1][$i], $paramMatches);
				for ($j = 0; $j < count($paramMatches[0]); $j++) {
					$params[$paramMatches[1][$j]] = $paramMatches[2][$j];
				}
				
				// Check for RSS-urls
				if (isset($params['action']) && $params['action'] == 'rss') {
					$module = 'rss';
					if (isset($params['scope']) && $params['scope'] == 'alldiscuss') {
						$action = 'comments';
					} else {
						$action = 'content';
					}
				}
				
				// Filter out unneeded params
				foreach ($params as $key => $val) {
					if (in_array($key, $paramsToIgnore) ||in_array($val, $valuesToIgnore)) {
						unset($params[$key]);
					}
				}
			}
			// sites/slotname url
			else if ($matches[2][$i]) {
				$params['site'] = $matches[2][$i];
			}
			// other matching url
			else {
				// do nothing, allow an empty local url.
			}
			
			$newLink = $this->convertParamsToNewLink($params, $module, $action);
			
			
// 			print "<hr/>";
// 			printpre($link);
// 			printpre($newLink);

			// Re-Write the media url to use the new id.
			$html = $this->str_replace_once($link, $newLink, $html);
		}
		
// 		printpre(htmlentities($html));

		if (strpos($html, 'linkpath') !== false)
			throw new Exception("Did not fully convert links. Linkpath found in: \n ".htmlentities($html));		
			
		return $html;
	}
	
	/**
	 * Convert an array of parameters to a new link string.
	 * 
	 * @param array $params
	 * @return string
	 * @access protected
	 * @since 4/3/08
	 */
	protected function convertParamsToNewLink (array $params, $module = 'view', $action = 'html') {
		// If there is no site specified, then the site is the current one.
		// In that case, we can do a [[node:smallest_node_id]] style url.
		if (!isset($params['site'])) {
			$segue1Identifiers = array('story', 'page', 'section');
			foreach ($segue1Identifiers as $key) {
				if (isset($params[$key]))
					return "[[localurl:module=".$module."&amp;action=".$action."&amp;node=".$key."_".$params[$key]."]]";
			}
			return "[[localurl:module=".$module."&amp;action=".$action."&amp;site=".$this->getSlotName()."]]";
		} 
		// If there is a site specified, then it may or may not have been imported
		// yet.
		else {
			// First try resolving it. If it has been imported, then use the 
			// new id.
			$resolver = Segue1UrlResolver::instance();
			$segue1Identifiers = array('story', 'page', 'section', 'site');
			foreach ($segue1Identifiers as $identifier) {
				if (isset($get[$identifier]) && $get[$identifier]) {
					try {
						return "[[localurl:module=".$module."&amp;action=".$action."&amp;node=".$resolver->getSegue2IdForOld($identifier, $get[$identifier])."]]";
					} catch (UnknownIdException $e) {
					}
				}
			}
			
			// If it hasn't been imported yet, build a url that will point to the old
			// version and be pickup up later by the resolver.
			$paramString = 'module=resolver&amp;action=segue1';
			foreach ($params as $key => $val)
				$paramString .= '&amp;'.$key.'='.$val;
			
			return "[[localurl:".$paramString."]]";
		}
	}
	
	/**
	 * Given a file name, attach it to an attachedMedia node and return its id.
	 * 
	 * @param string $filename
	 * @param object DOMElement $destAttachedMedia
	 * @return string The fileurl value for the file
	 * @access protected
	 * @since 2/11/08
	 */
	protected function attachFile ($filename, DOMElement $destAttachedMedia) {
		if (!strlen(trim($filename)))
			return;
		
		// Currently attached locations here
		$currentlyAttachedLocations = $this->xpath->query('./mediaAsset/file[name = "'.$filename.'"]', $destAttachedMedia);
		if ($currentlyAttachedLocations->length)
			return 'asset_id='.$currentlyAttachedLocations->item(0)->parentNode->getAttribute('id')
			.'&amp;record_id='.$currentlyAttachedLocations->item(0)->getAttribute('id');
		
		// Currently attached locations elsewhere in the document.
		$currentlyAttachedLocations = $this->xpath->query('//mediaAsset/file[name = "'.$filename.'"]');
		if ($currentlyAttachedLocations->length) {
			return 'asset_id='.$currentlyAttachedLocations->item(0)->parentNode->getAttribute('id')
			.'&amp;record_id='.$currentlyAttachedLocations->item(0)->getAttribute('id');
		}
		
		$sourceFile = $this->sourceXPath->query('/site/media/media_file[filename = "'.$filename.'"]')->item(0);
		if (!$sourceFile)
			throw new MissingNodeException("Could not find entry for media file, '$filename'.");
		
		$element = $this->doc->createElement('mediaAsset');
		$element->setAttribute('id', $this->createId());
		
		
		// get title whole = Journal or book title
		try {
			$titleWholeElements = $this->sourceXPath->query('./title_whole', $sourceFile);

			if ($titleWholeElements->length) {
				$titleWhole = $this->getStringValue($titleWholeElements->item(0));

			} else {
				$titleWhole = '';
			}
		} catch (Exception $e) {
			$titleWhole = '';
		}
		
		// get title part = article title
		try {
			$titlePartElements = $this->sourceXPath->query('./title_part', $sourceFile);

			if ($titlePartElements->length) {
				$titlePart = $this->getStringValue($titlePartElements->item(0));

			} else {
				$titlePart = $filename;
			}
		} catch (Exception $e) {
			$titlePart = '';
		}
		
		// set file displayName = dc title otherwise set it to filename
		if (strlen($titlePart) && strlen($titleWhole)) {
			$title = $titlePart.". ".$titleWhole;
		} else if (!strlen($titleWhole)) {
			$title = $titlePart;
		} else if (!strlen($titlePart)) {
			$title = $titleWhole;
		}
		
		if (!strlen($title))
			$title = $filename;
		
		$element->appendChild($this->createCDATAElement('displayName', $title));

		
		// Make file description be dc description otherwise set it to null
		if (isset($description)) {
			$element->appendChild($this->createCDATAElement('description', $description));
		} else {
			$element->appendChild($this->createCDATAElement('description', ''));
		}
				
		// get file name and path
		$fileElement = $element->appendChild($this->doc->createElement('file'));
		$fileElement->setAttribute('id', $this->createId());
		$fileElement->appendChild($this->createCDATAElement('name', $filename));
		
		// move the media file and get its path
		$newPath = $this->director->copyFile($filename);
		$fileElement->appendChild($this->createCDATAElement('path', $newPath));
		
		// create dublin core element
		$dublinCoreElement = $element->appendChild($this->doc->createElement('dublinCore'));
		$dublinCoreElement->setAttribute('id', $this->createId());
		
		// set title
		$dublinCoreElement->appendChild($this->createCDATAElement('title', $title));
		
		// get author
		try {
			$authorElements = $this->sourceXPath->query('./author', $sourceFile);
			if ($authorElements->length)
				$author = $this->getStringValue($authorElements->item(0));
			else
				$author = '';
		} catch (Exception $e) {
			$author = '';
		}
		
		$dublinCoreElement->appendChild($this->createCDATAElement('creator', $author));

		// get page range and put into source field					
		try {
			$pagerangeElements = $this->sourceXPath->query('./pagerange', $sourceFile);
			if ($pagerangeElements->length)
				$pagerange = $this->getStringValue($pagerangeElements->item(0));
			else
				$pagerange = '';
		} catch (Exception $e) {
			$pagerange = '';
		}

		if (strlen($pagerange)) {
			$source = "(".$pagerange.")";		
			$dublinCoreElement->appendChild($this->createCDATAElement('source', $source));
		}

		// get publisher
		try {
			$publisherElements = $this->sourceXPath->query('./publisher', $sourceFile);
			if ($publisherElements->length)
				$publisher = $this->getStringValue($publisherElements->item(0));
			else
				$publisher = '';
		} catch (Exception $e) {
			$publisher = '';
		}
		
		$dublinCoreElement->appendChild($this->createCDATAElement('publisher', $publisher));

		// get publication date
		try {
			$pubyearElements = $this->sourceXPath->query('./pubyear', $sourceFile);
			if ($pubyearElements->length)
				$pubyear = $this->getStringValue($pubyearElements->item(0));
			else
				$pubyear = '';
		} catch (Exception $e) {
			$pubyear = '';
		}

		// need to validate date
		if ($pubyear != "") {
			$dateAndTime = DateAndTime::fromString($pubyear);
			if ($dateAndTime) {
				$dublinCoreElement->appendChild($this->createCDATAElement('date', $dateAndTime->asString()));
			}
		}
		
		// Create-info
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./creator', $sourceFile));
			if ($value)
				$element->setAttribute('create_agent', $value);
		} catch (MissingNodeException $e) {}
		
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./created_time', $sourceFile));
			if ($value)
				$element->setAttribute('create_date', $value);
		} catch (MissingNodeException $e) {}
		
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./last_edited_time', $sourceFile));
			if ($value)
				$element->setAttribute('modify_date', $value);
		} catch (MissingNodeException $e) {}
		
		$destAttachedMedia->appendChild($element);
		
		return 'asset_id='.$element->getAttribute('id')
			.'&amp;record_id='.$fileElement->getAttribute('id');
	}
		
	/**
	 * Answer a plugin Type element
	 * 
	 * @param string $keyword
	 * @return DOMElement
	 * @access protected
	 * @since 2/5/08
	 */
	protected function createPluginType ($keyword) {
		$element = $this->doc->createElement('type');
		
		$element->appendChild($this->doc->createElement('domain', 'SeguePlugins'));
		$element->appendChild($this->doc->createElement('authority', 'edu.middlebury'));
		$element->appendChild($this->doc->createElement('keyword', $keyword));
		
		return $element;
	}
}


?>