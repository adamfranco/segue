<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: BlockSegue1To2Converter.abstract.php,v 1.2 2008/03/17 15:25:05 adamfranco Exp $
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
 * @version $Id: BlockSegue1To2Converter.abstract.php,v 1.2 2008/03/17 15:25:05 adamfranco Exp $
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
		$element->appendChild($this->getDescriptionElement($media));
		
		$element->appendChild($this->doc->createElement('roles'));
		
		$element->appendChild($this->getContentElement($media));
		
		$history = $this->getHistoryElement($media);
		if (!is_null($history))
			$element->appendChild($history);
		
		$comments = $element->appendChild($this->doc->createElement('comments'));
		
		$element->appendChild($media);
		
		// Comments
		$this->addComments($comments);
		
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
		$element->setAttribute('agent_id', $versionElement->getAttribute('agent_id'));
		
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
			$filename = $matches[1][$i];
			
			// Attach the media files and get the new id.
			$fileId = $this->attachFile($filename, $attachedMedia);
		
			// Re-Write the media url to use the new id.
			$html = str_replace($link, "[[fileurl:".$fileId."]]", $html);
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
			
			// GET URLs
			if ($matches[1][$i]) {
				preg_match_all($paramPattern, $matches[1][$i], $paramMatches);
				for ($j = 0; $j < count($paramMatches[0]); $j++) {
					if (!in_array($paramMatches[1][$j], $paramsToIgnore)
						&& !in_array($paramMatches[2][$j], $valuesToIgnore))
					{
						$params[$paramMatches[1][$j]] = $paramMatches[2][$j];
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
			
			$paramString = '';
			foreach ($params as $key => $val)
				$paramString .= '&amp;'.$key.'='.$val;
			
// 			print "<hr/>";
// 			printpre($link);
// 			printpre($paramString);

			// Re-Write the media url to use the new id.
			$html = str_replace($link, "[[localurl:".$paramString."]]", $html);
		}
		
// 		printpre(htmlentities($html));

		if (strpos($html, 'linkpath') !== false)
			throw new Exception("Did not fully convert links. Linkpath found in: \n ".htmlentities($html));
	
		return $html;
	}
	
	/**
	 * Given a file name, attach it to an attachedMedia node and return its id.
	 * 
	 * @param string $filename
	 * @param object DOMElement $destAttachedMedia
	 * @return string The file id
	 * @access protected
	 * @since 2/11/08
	 */
	protected function attachFile ($filename, DOMElement $destAttachedMedia) {
		if (!strlen(trim($filename)))
			return;
		
		// Currently attached locations here
		$currentlyAttachedLocations = $this->xpath->query('./mediaAsset[file/name = "'.$filename.'"]', $destAttachedMedia);
		if ($currentlyAttachedLocations->length)
			return $currentlyAttachedLocations->item(0)->getAttribute('id');
		
		// Currently attached locations elsewhere in the document.
		$currentlyAttachedLocations = $this->xpath->query('//mediaAsset[file/name = "'.$filename.'"]');
		if ($currentlyAttachedLocations->length)
			return $currentlyAttachedLocations->item(0)->getAttribute('id');
		
		$sourceFile = $this->sourceXPath->query('/site/media/media_file[filename = "'.$filename.'"]')->item(0);
		if (!$sourceFile)
			throw new MissingNodeException("Could not find entry for media file, '$filename'.");
		
		$element = $destAttachedMedia->appendChild($this->doc->createElement('mediaAsset'));
		$element->setAttribute('id', $this->createId());
		$element->appendChild($this->createCDATAElement('displayName', $filename));
		$element->appendChild($this->createCDATAElement('description', ''));
		
		$fileElement = $element->appendChild($this->doc->createElement('file'));
		$fileElement->setAttribute('id', $this->createId());
		$fileElement->appendChild($this->createCDATAElement('name', $filename));
		
		// move the media file and get its path
		$newPath = $this->director->copyFile($filename);
		$fileElement->appendChild($this->createCDATAElement('path', $newPath));
		
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
		
		return $fileElement->getAttribute('id');
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