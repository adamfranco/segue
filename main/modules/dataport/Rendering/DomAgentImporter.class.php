<?php
/**
 * @since 1/29/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomAgentImporter.class.php,v 1.4 2008/03/20 15:43:56 adamfranco Exp $
 */ 

require_once(HARMONI."oki2/shared/NonReferenceProperties.class.php");

/**
 * The Agent Importer matches an agent elements against agents found in the 
 * destination system and returns the id of the matching agent in the destination system.
 * 
 * @since 1/29/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DomAgentImporter.class.php,v 1.4 2008/03/20 15:43:56 adamfranco Exp $
 */
class DomAgentImporter {
		
	/**
	 * Constructor
	 * 
	 * @param object DOMDocument $doc
	 * @return void
	 * @access public
	 * @since 1/29/08
	 */
	public function __construct (DOMDocument $doc) {
		$this->doc = $doc;
		$this->xpath = new DOMXPath($this->doc);
	}
	
	/**
	 * Given an agent Id string from the source document, return the Id object
	 * of a matching agent in the destination System
	 * 
	 * @param string $idString
	 * @return object Id
	 * @access public
	 * @since 1/29/08
	 */
	public function getAgentId ($idString) {
		ArgumentValidator::validate($idString, NonZeroLengthStringValidatorRule::getRule());
		
		$idMgr = Services::getService('Id');
		
		$agentElement = $this->xpath->query('//agents/agent[@id = "'.$idString.'"]')->item(0);
		if (!$agentElement)
			throw new UnknownIdException("Could not find Agent with old id \"$idString\" in the document.");
			
		if (!$agentElement->hasAttribute('new_id'))
			$this->findAgent($agentElement);
		
		if (!$agentElement->hasAttribute('new_id')) {
			$this->createHistoricalAgent($agentElement);
			
//			throw new UnknownIdException("Could not match Agent with old id \"$idString\" and displayName \"".$this->getStringValue($this->getSingleElement('./displayName', $agentElement))."\" to an agent in the new system.");
		}
		
		if (!$agentElement->hasAttribute('new_id') || !strlen($agentElement->getAttribute('new_id')))
			throw new Exception ("No new_id available for agent with source id $idString. An agent should have been matched or an historical agent created.");
		
		return $idMgr->getId($agentElement->getAttribute('new_id'));
	}
	
	/**
	 * Match the XML representation of an agent to an agent in the system.
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/28/08
	 */
	protected function findAgent (DOMElement $element) {
		$idMgr = Services::getService('Id');
		$agentMgr = Services::getService('Agent');
		
		// Try getting the agent by Id
		try {
			$agent = $agentMgr->getAgent($idMgr->getId($element->getAttribute('id')));
			if ($this->agentMatches($agent, $element)) {
				$element->setAttribute('new_id', $agent->getId()->getIdString());
				return;
			}
		} catch (UnknownIdException $e) {
		}
		
		// Try searching on various properties and matching.
		$searchProperties = array (
// 			'identifier',
			'username',
			'email'
		);
		
		foreach ($searchProperties as $property) {
			try {
				$agents = $this->getAgentsMatchingProperty($property, $element);
				while ($agents->hasNext()) {
					$agent = $agents->next();
					if ($this->agentMatches($agent, $element)) {
						$element->setAttribute('new_id', $agent->getId()->getIdString());
						return;
					}
				}
			} catch (UnimplementException $e) {
			}
		}
	}
	
	/**
	 * Compare an Agent to an Agent XML description and determine if the two match.
	 *
	 * The current implementation of this method is kind of goofy. It should be
	 * re-worked to either use a more reliable comparison method or be made configurable
	 * for different comparrison methods.
	 * 
	 * @param object Agent $agent
	 * @param object DOMElement $element
	 * @return boolean
	 * @access protected
	 * @since 1/28/08
	 */
	protected function agentMatches (Agent $agent, DOMElement $element) {
		// check some system-ids first as these will not have meaningful properties
		// to compare and their IDs are hard coded into the system.
		$sourceId = $element->getAttribute('id');
		$systemIds = array(
				"edu.middlebury.agents.everyone",
				"edu.middlebury.institute",
				"1",
				"edu.middlebury.agents.anonymous"
			);
		if (in_array($sourceId, $systemIds)) {
			if ($sourceId == $agent->getId()->getIdString())
				return true;
			else
				return false;
		}
		
		// Compare agent properties to make sure that the agent and the DOMElement
		// are representing the same user.
		$xmlPropertiesToCheck = array(
			'email',
			'username'
		);
		
		$agentPropertiesToCheck = array(
			'email',
			'username'
		);
		// How man properties must match
		$threshold = 1;
		$matches = 0;
		
		
		$xmlValues = array();
		foreach ($xmlPropertiesToCheck as $property) {
// 			printpre(htmlentities($this->doc->saveXMLWithWhitespace($element)));
			$valueElements = $this->xpath->evaluate('./property[key = \''.$property.'\']/string', $element);
			if ($valueElements->length) {
				$xmlValues[] = $this->getStringValue($valueElements->item(0));
			}
// 			throw new Exception('test error');
		}
		
		$agentPropertiesIterator = $agent->getProperties();
		while ($agentPropertiesIterator->hasNext()) {
			$properties = $agentPropertiesIterator->next();
// 			printpre($properties->getKeys());
			foreach ($agentPropertiesToCheck as $key) {
				try {
					$value = $properties->getProperty($key);
// 					printpre($value);
					if (!is_null($value) && in_array($value, $xmlValues))
						$matches++;
				} catch (UnknownKeyException $e) {
				}
			}
		}
		
		if ($matches >= $threshold)
			return true;
		else
			return false; 
	}
	
	
	/**
	 * Answer an iterator of agents that match the value of the property specified
	 * with data from the agent element.
	 * 
	 * @param string $propertyKey
	 * @param object DOMElement $element
	 * @return object Iterator
	 * @access protected
	 * @since 1/29/08
	 */
	protected function getAgentsMatchingProperty ($propertyKey, DOMElement $element) {
		ArgumentValidator::validate($propertyKey, NonZeroLengthStringValidatorRule::getRule());
		
		$valueElements = $this->xpath->evaluate('./property[key = \''.$propertyKey.'\']/string', $element);
		if ($valueElements->length) {
			$value = $this->getStringValue($valueElements->item(0));
			if (strlen($value)) {
				$agentManager = Services::getService("Agent");
				$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
				$string = "*".$value."*";
				
				$agents = new MultiIteratorIterator;
				$agents->addIterator($agentManager->getAgentsBySearch($string, $searchType));
				$agents->addIterator($agentManager->getGroupsBySearch($string, $searchType));
				
				$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "AgentPropertiesSearch");
				$string = "*".$value."*";
				$agents->addIterator($agentManager->getAgentsBySearch($string, $searchType));
				$agents->addIterator($agentManager->getGroupsBySearch($string, $searchType));
				
				return $agents;
			}
		}
		
		return new HarmoniIterator(array());
	}
	
	/**
	 * Create a historical agent with properties set from the agent element for referencing 
	 * in asset histories.
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/29/08
	 */
	protected function createHistoricalAgent (DOMElement $element) {
		$agentManager = Services::getService("Agent");
		$agentType = new Type ("History", "edu.middlebury.harmoni", "Historical Agent", "Historical Agents are created to serve as a record of an agent that was once in the system. These agents do not have authentication credentials and cannot act in the system.");
				
		$agentProperties = new NonReferenceProperties($agentType);
		$properties = $element->getElementsByTagName('property');
		foreach ($properties as $property) {
			$key = $this->getStringValue($this->getSingleElement('./key', $property));
			$value = $this->getStringValue($this->getSingleElement('./string', $property));
			$agentProperties->addProperty($key, $value);
		}
		
		$agentProperties->addProperty("status", "Expired");
		try {
			$displayName = $this->getStringValue($this->getSingleElement('./displayName', $element));
		} catch (Exception $e) {
			$displayName = $element->getAttribute('id');
		}
		$agent = $agentManager->createAgent(
			$displayName." - Historical", 
			$agentType, $agentProperties);
		
		$element->setAttribute('new_id', $agent->getId()->getIdString());
	}
	
	/*********************************************************
	 * Utility methods
	 *********************************************************/
	
	/**
	 * Answer a single element with the xpath specified.
	 * 
	 * @param string $xpath
	 * @param DOMElement $element
	 * @return DOMElement
	 * @access protected
	 * @since 1/22/08
	 */
	protected function getSingleElement ($xpath, DOMElement $element) {
		$nodes = $this->xpath->evaluate($xpath, $element);
		for ($i = 0; $i < $nodes->length; $i++) {
			$node = $nodes->item($i);
			if ($node->nodeType == XML_ELEMENT_NODE) {
				if (isset($resElement))
					throw new Exception("2 elements (".get_class($resElement)." '".$resElement->nodeName."', ".get_class($node)." '".$node->nodeName."') found for xpath '$xpath'. Expecting one and only one.");
				$resElement = $node;
			}
		}
		
		if (!isset($resElement))
			throw new Exception("0 elements found for xpath '$xpath'. Expecting one and only one.");
		
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
	
}

?>