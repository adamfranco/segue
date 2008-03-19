<?php
/**
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Converter.abstract.php,v 1.3 2008/03/19 18:19:31 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/TextBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/LinkBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/DownloadBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/HeadingBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/RssBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/ImageBlockSegue1To2Converter.class.php");
require_once(dirname(__FILE__)."/../Rendering/DomImportSiteVisitor.class.php");

/**
 * This is an abstract parent class that all converters will inherit.
 * 
 * @since 2/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Converter.abstract.php,v 1.3 2008/03/19 18:19:31 adamfranco Exp $
 */
abstract class Segue1To2Converter {

	/**
	 * A class variable to store a local Id counter.
	 * 
	 * @access private
	 * @since 2/12/08
	 */
	protected static $id = 0;
		
	/**
	 * Constructor takes the source element and the destination document and the destination xpath.
	 * 
	 * @param object DOMElement $sourceElement
	 * @param object DOMXPath $sourceXPath
	 * @param object DOMDocument $doc
	 * @param object DOMXPath $xpath
	 * @param object Segue1To2Director $director
	 * @return void
	 * @access public
	 * @since 2/12/08
	 */
	public function __construct (DOMElement $sourceElement, DOMXPath $sourceXPath, DOMDocument $doc, DOMXPath $xpath, Segue1To2Director $director) {
		$this->sourceElement = $sourceElement;
		$this->sourceXPath = $sourceXPath;
		$this->doc = $doc;
		$this->xpath = $xpath;
		$this->director = $director;
		
		$this->permissionResolvers = array();
		
		$this->permissionResolvers[] = new PermissionResolver('editor', array('view', 'add', 'edit', 'delete'));
		$this->permissionResolvers[] = new PermissionResolver('editor', array('view', 'add', 'edit'));
		$this->permissionResolvers[] = new PermissionResolver('editor', array('view', 'edit', 'delete'));
		$this->permissionResolvers[] = new PermissionResolver('editor', array('view', 'edit'));
		$this->permissionResolvers[] = new PermissionResolver('editor', array('add', 'edit'));
		
		
		$this->permissionResolvers[] = new PermissionResolver('author', array('view', 'add'));
		
		$this->permissionResolvers[] = new PermissionResolver('commenter', array('view', 'comment'));
		
		$this->permissionResolvers[] = new PermissionResolver('reader', array('view'));
		
		$this->permissionResolvers[] = new PermissionResolver('no_access', array());
	}
	
	/**
	 * Add our Id to the output element
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 2/13/08
	 */
	protected function addId (DOMElement $element) {
		if ($this->sourceElement->hasAttribute('id') 
			&& strlen(trim($this->sourceElement->getAttribute('id')))) 
		{
			$element->setAttribute('id', 
				$this->getIdString($this->sourceElement->getAttribute('id')));
		} else {
			$element->setAttribute('id', $this->createId());
		}
	}
	
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
		return $idString;
	}
	
	/**
	 * Answer a new Id string unique within this export
	 * 
	 * @return string
	 * @access protected
	 * @since 2/6/08
	 */
	protected function createId () {
		self::$id++;
		return 'local_'.self::$id;
	}
	
	/**
	 * Answer a display name for the current element
	 * 
	 * @return string
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getDisplayName () {
		try {
			$name = $this->getStringValue($this->getSingleSourceElement('./title', $this->sourceElement));
			if (!strlen(trim($name)))
				$name = "Untitled";
		} catch (MissingNodeException $e) {
			$name = "Untitled";
		}
		
		return $name;
	}
	
	/**
	 * Answer an element for the displayname
	 * 
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getDisplayNameElement () {
		return $this->createCDATAElement('displayName', $this->getDisplayName());
	}
	
	/**
	 * Answer a content block for a given input element. Does the switching based on
	 * type.
	 * 
	 * @param object DOMElement $sourceElement
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function getContentBlock (DOMElement $sourceElement) {
		switch ($sourceElement->nodeName) {
			case 'story':
				$class = 'TextBlockSegue1To2Converter';
				break;
			case 'file':
				$class = 'DownloadBlockSegue1To2Converter';
				break;
			case 'link':
				$class = 'LinkBlockSegue1To2Converter';
				break;
			case 'image':
				$class = 'ImageBlockSegue1To2Converter';
				break;
			case 'link':
				$class = 'RssBlockSegue1To2Converter';
				break;
			default:
				throw new Exception('Unknown content type, "'.$sourceElement->nodeName.'".');
		}
		
		$converter = new $class ($sourceElement, $this->sourceXPath, $this->doc, $this->xpath, $this->director);
		
		return $converter->convert();
	}
	
	
/*********************************************************
 * History
 *********************************************************/
	/**
	 * Add created/modified agent and timestamps
	 * 
	 * @param DOMElement $sourceElement
	 * @param DOMElement $destElement
	 * @return void
	 * @access protected
	 * @since 2/5/08
	 */
	protected function addCreationInfo (DOMElement $destElement) {
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./history/creator', $this->sourceElement));
		} catch (MissingNodeException $e) {
			try {
				$value = $this->getStringValue($this->getSingleSourceElement('./creator', $this->sourceElement));
			} catch (MissingNodeException $e) {
				$value = null;
			}
		}
		if ($value)
			$destElement->setAttribute('create_agent', $this->addAgent($value));
		
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./history/created_time', $this->sourceElement));
		} catch (MissingNodeException $e) {
			try {
				$value = $this->getStringValue($this->getSingleSourceElement('./created_time', $this->sourceElement));
			} catch (MissingNodeException $e) {
				$value = null;
			}
		}
		if ($value)
			$destElement->setAttribute('create_date', $value);
		
		try {
			$value = $this->getStringValue($this->getSingleSourceElement('./history/last_edited_time', $this->sourceElement));
		} catch (MissingNodeException $e) {
			try {
				$value = $this->getStringValue($this->getSingleSourceElement('./last_edited_time', $this->sourceElement));
			} catch (MissingNodeException $e) {
				$value = null;
			}
		}
		if ($value)
			$destElement->setAttribute('modify_date', $value);
	}
	
/*********************************************************
 * Roles
 *********************************************************/
	
	/**
	 * Add the roles from an element
	 * 
	 * @param DOMElement $destElement
	 * @return void
	 * @access protected
	 * @since 2/4/08
	 */
	protected function addRoles (DOMElement $destElement) {
		try {
			$rolesElement = $this->getSingleElement('./roles', $destElement);
		} catch (MissingNodeException $e) {
			$rolesElement = $destElement->appendChild($this->doc->createElement('roles'));
		}
		
		$permissions = $this->sourceXPath->query('./permissions/*', $this->sourceElement);
		$agents = array();
		foreach ($permissions as $perm) {
			$agentsHavingPerm = $this->sourceXPath->query('./agent', $perm);
			foreach ($agentsHavingPerm as $agent) {
				$agentId = trim($agent->nodeValue);
				if (!$agentId)
					throw new Exception("Unknown agent id '".$agentId."'.");
				switch ($perm->nodeName) {
					case 'view_permission':
						$agents[$agentId][] = 'view';
						break;
					case 'comment_permission':
						$agents[$agentId][] = 'comment';
						break;
					case 'add_permission':
						$agents[$agentId][] = 'add';
						break;
					case 'edit_permission':
						$agents[$agentId][] = 'edit';
						break;
					case 'delete_permission':
						$agents[$agentId][] = 'delete';
						break;
					default:
						throw new Exception("Unknown permission type '".$perm->nodeName."'.");
				}
			}
		}
		
		foreach ($agents as $agentId => $perms) {
			$this->addPermsForAgent($agentId, $perms, $destElement);
		}
	}
	
	/**
	 * Add a set of permissions as a role for an agent
	 * 
	 * @param string $agentId
	 * @param array $permissions
	 * @param object DOMElement $destElement
	 * @return void
	 * @access protected
	 * @since 2/5/08
	 */
	protected function addPermsForAgent ($agentId, array $perms, DOMElement $destElement) {
// 		try {
			$role = $this->getRoleFromPerms($perms);
			$this->addRoleForAgent($agentId, $role, $destElement);
// 		} catch (Exception $e) {}
	}
	
	/**
	 * For a given array of permissions, determine a matching role.
	 * 
	 * @param array $perms
	 * @return string The role Id
	 * @access protected
	 * @since 2/5/08
	 */
	protected function getRoleFromPerms (array $perms) {
		foreach ($this->permissionResolvers as $resolver) {
			if ($resolver->matches($perms))
				return $resolver->role;
		}
		
		throw new Exception("No role matches permissions, ".implode(", ", $perms).".");
	}
	
	/**
	 * Add a role for an agent
	 * 
	 * @param string $agentId
	 * @param string $role
	 * @param object DOMElement $destElement
	 * @return void
	 * @access protected
	 * @since 2/5/08
	 */
	protected function addRoleForAgent ($agentId, $role, DOMElement $destElement) {
		try {
			$rolesElement = $this->getSingleElement('./roles', $destElement);
		} catch (MissingNodeException $e) {
			$rolesElement = $destElement->appendChild($this->doc->createElement('roles'));
		}
		
		$entry = $rolesElement->appendChild($this->doc->createElement('entry'));
		$entry->setAttribute('agent_id', $this->addAgent($agentId));
		$entry->setAttribute('role', $role);
	}
	
	/**
	 * Add an agent element if needed.
	 * 
	 * @param string $agentId
	 * @return void
	 * @access protected
	 * @since 2/13/08
	 */
	protected function addAgent ($agentId) {
		switch ($agentId) {
			case 'everyone':
				$agentId = 'edu.middlebury.agents.everyone';
				break;
			case 'institute':
				$agentId = 'edu.middlebury.institute';
		}
		
		try {
			$agentElement = $this->getSingleElement('/Segue2/agents/agent[@id = "'.$agentId.'"]');
		} catch (MissingNodeException $e) {
			$agentsElement = $this->getSingleElement('/Segue2/agents');
			$agentElement = $agentsElement->appendChild($this->doc->createElement('agent'));
			$agentElement->setAttribute('id', $agentId);
			$agentElement->appendChild($this->createProperty('username', $agentId));
		}
		
		return $agentId;
	}
	
/*********************************************************
 * 	Utility functions
 *********************************************************/
 	
 	/**
 	 * Answer a property element for the key and value passed.
 	 * 
 	 * @param string $key
 	 * @param string $value
 	 * @return DOMElement
 	 * @access protected
 	 * @since 2/13/08
 	 */
 	protected function createProperty ($key, $value) {
 		$element = $this->doc->createElement('property');
 		$element->appendChild($this->doc->createElement('key', $key));
 		$element->appendChild($this->createCDATAElement('string', $value));
 		
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
	protected function createCDATAElement ($elementName, $data) {
		$element = $this->doc->createElement($elementName);
		$element->appendChild($this->doc->createCDATASection($data));
		return $element;
	}
	
	/**
	 * Answer a single element with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param optional DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getSingleElement ($xpath, DOMElement $element = null) {
		if (is_null($element))
			$nodes = $this->xpath->query($xpath);
		else
			$nodes = $this->xpath->query($xpath, $element);
		for ($i = 0; $i < $nodes->length; $i++) {
			$node = $nodes->item($i);
			if ($node->nodeType == XML_ELEMENT_NODE) {
				if (isset($resElement))
					throw new Exception("2 elements (".get_class($resElement)." '".$resElement->nodeName."', ".get_class($node)." '".$node->nodeName."') found for xpath '$xpath'. Expecting one and only one.");
				$resElement = $node;
			}
		}
		
		if (!isset($resElement))
			throw new MissingNodeException("0 elements found for xpath '$xpath'. Expecting one and only one.");
		
		return $resElement;
	}
	
	/**
	 * Answer a single node of any type with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/30/08
	 */
	protected function getSingleNode ($xpath, DOMElement $element) {
		$nodes = $this->xpath->evaluate($xpath, $element);
		if ($nodes->length != 1)
			throw new Exception("".$nodes->length." nodes found for XPATH '$xpath'. Expecting one and only one.");
		
		return $nodes->item(0);
	}
	
	/**
	 * Answer a single element with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getSingleSourceElement ($xpath, DOMElement $element = null) {
		if (is_null($element))
			$nodes = $this->sourceXPath->evaluate($xpath);
		else
			$nodes = $this->sourceXPath->evaluate($xpath, $element);
		for ($i = 0; $i < $nodes->length; $i++) {
			$node = $nodes->item($i);
			if ($node->nodeType == XML_ELEMENT_NODE) {
				if (isset($resElement))
					throw new Exception("2 elements (".get_class($resElement)." '".$resElement->nodeName."', ".get_class($node)." '".$node->nodeName."') found for xpath '$xpath'. Expecting one and only one.");
				$resElement = $node;
			}
		}
		
		if (!isset($resElement))
			throw new MissingNodeException("0 elements found for xpath '$xpath'. Expecting one and only one.");
		
		return $resElement;
	}
	
	/**
	 * Answer a single node of any type with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/30/08
	 */
	protected function getSingleSourceNode ($xpath, DOMElement $element) {
		$nodes = $this->sourceXPath->evaluate($xpath, $element);
		if ($nodes->length != 1)
			throw new Exception("".$nodes->length." nodes found for XPATH '$xpath'. Expecting one and only one.");
		
		return $nodes->item(0);
	}
	
	/**
	 * Answer the string value of an element in any text or CDATA nodes.
	 * 
	 * @param DOMElement $element
	 * @return string
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getStringValue (DOMElement $element) {
		$value = '';
		foreach ($element->childNodes as $child) {
			switch ($child->nodeType) {
				case XML_TEXT_NODE:
				case XML_CDATA_SECTION_NODE:
					$value .= $child->nodeValue;
				case XML_COMMENT_NODE:
					break;
				default:
					throw new Exception("Found ".get_class($child).", expecting a text node or CDATA Section.");
			}
		}
		
		return $value;
	}
	
	/**
	 * Clean an HTML String
	 * 
	 * @param string $html
	 * @return string The cleaned HTML
	 * @access protected
	 * @since 2/14/08
	 */
	protected function cleanHtml ($html) {
		return $this->trimHtml($html);
	}
	
	/**
	 * Clean and trim an HTML String.
	 * 
	 * @param string $html
	 * @param optional int $numWords The maximum number of words.
	 * @return string
	 * @access protected
	 * @since 2/14/08
	 */
	protected function trimHtml ($html, $numWords = null) {
		$string = HtmlString::withValue($html);
		// SafeHTML looks for the first colon to determine if something is a
		// a protocal.
		$string->addSafeProtocal('[[fileurl');
		$string->addSafeProtocal('[[localurl');
		$string->cleanXSS();
		if (!is_null($numWords))
			$string->trim($numWords);
		return $string->asString();
	}
}

/**
 * A Class to resolve matching permissions sets into roles
 * 
 * @since 2/5/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1To2Converter.abstract.php,v 1.3 2008/03/19 18:19:31 adamfranco Exp $
 */
class PermissionResolver {
		
	/**
	 * Constructor
	 * 
	 * @param string $role
	 * @param array $perms
	 * @return void
	 * @access public
	 * @since 2/5/08
	 */
	public function __construct ($role, array $perms) {
		$this->role = $role;
		$this->perms = $perms;
	}
	
	/**
	 * Answer true if our list of permissions are all found in the set passed.
	 * 
	 * @param array $perms
	 * @return boolean
	 * @access public
	 * @since 2/5/08
	 */
	public function matches (array $perms) {
		// return false if a permission in our list is missing.
		foreach ($this->perms as $perm) {
			if (!in_array($perm, $perms))
				return false;
		}
		
		return true;
	}
	
}

?>