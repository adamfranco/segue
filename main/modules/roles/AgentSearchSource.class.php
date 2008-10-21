<?php
/**
 * @since 11/27/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AgentSearchSource.class.php,v 1.7 2008/04/09 21:12:02 adamfranco Exp $
 */ 

/**
 * The AgentSearchSource provides support for searching for Agents and Groups
 * 
 * @since 11/27/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AgentSearchSource.class.php,v 1.7 2008/04/09 21:12:02 adamfranco Exp $
 */
class AgentSearchSource
	implements WSearchSource
{
		
	/**
	 * Answer a valid url that will return an XML document containing the search
	 * results.
	 * 
	 * @param string $placeholder A placeholder which can be replaced with the search
	 * 		term on the client-side.
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getXmlUrl ($placeholder) {
		$harmoni = Harmoni::instance();
		$url = $harmoni->request->quickURL('roles', 'search_agents', array('query' => $placeholder));
		return str_replace("&amp;", "&", $url);
	}
	
	/**
	 * Answer an array of search result objects after searching for the term passed
	 * 
	 * @param string $searchTerm
	 * @return array of WSearchResult objects
	 * @access public
	 * @since 11/27/07
	 */
	public function getResults ($searchTerm) {
		$results = array();
		$orderKeys = array();
		
		// Return an empty array if there is no search term.
		if (!strlen($searchTerm))
			return $results;
		
		$agentManager = Services::getService("Agent");
		$searchType = new HarmoniType("Agent & Group Search", "edu.middlebury.harmoni", "TokenSearch");
		$string = "*".$searchTerm."*";
		
		$agents = new MultiIteratorIterator;
		$agents->addIterator($agentManager->getAgentsBySearch($string, $searchType));
		$agents->addIterator($agentManager->getGroupsBySearch($string, $searchType));
		
		while ($agents->hasNext()) {
			$result = new AgentSearchResult($agents->next());	
			$results[] = $result;
			$orderKeys[] = strtolower($result->getName());
		}
		
		array_multisort($orderKeys, SORT_ASC, SORT_STRING, $results);
		
		return $results;
	}
	
	
	/**
	 * Answer the markup for a set of search results
	 * 
	 * @param string $fieldName
	 * @param array $results An array of WSearchResult objects
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getResultsMarkup ($fieldName, $results) {
		$harmoni = Harmoni::instance();
		
		if (count($results)) {
			print "\n\t<table id='".RequestContext::name($fieldName)."_output' class='search_results' cellspacing='0'>";
			
			$colorKey = 0;
			
			foreach ($results as $result) {
				print "\n\t\t<tr class='search_result_item '>";
				print "\n\t\t\t<td class='color".$colorKey."'>";
				print $result->getMarkup();
				print "\n\t\t\t</td>";
				print "\n\t\t\t<td class='action_button color".$colorKey."'>";
				$url =  $harmoni->request->quickURL('roles', 'modify', array(
					'node' => SiteDispatcher::getCurrentNodeId(),
					'agent' => $result->getIdString()
				));
				print "\n\t\t\t<button onclick='window.location = \"$url\".urlDecodeAmpersands(); return false;'>"._("Modify Roles &raquo;")."</button>";
				print "\n\t\t\t</td>";
				print "\n\t\t</tr>";
				
				$colorKey = intval(!$colorKey);
			}
			print "\n\t</table>";
		}
	}
}

/**
 * The AgentSearchResult gives information for Agents and Groups found via search
 * 
 * @since 11/27/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AgentSearchSource.class.php,v 1.7 2008/04/09 21:12:02 adamfranco Exp $
 */
class AgentSearchResult
	implements WSearchResult
{

	/**
	 * Constructor
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access public
	 * @since 11/27/07
	 */
	public function __construct (Agent $agent) {
		$this->id = $agent->getId();
		$this->name = $agent->getDisplayName();
		try {
			$propertiesIterator = $agent->getProperties();
			while ($propertiesIterator->hasNext()) {
				$properties = $propertiesIterator->next();
				try {
					if ($properties->getProperty('email')) {
						$this->email = $properties->getProperty('email');
						break;
					}
				} catch (Exception $e) {
				}
			}
		} catch (Exception $e) {
		}
		
	}

	/**
	 * Answer the string Id of result
	 * 
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getIdString () {
		return $this->id->getIdString();
	}
	
	/**
	 * Answer true if the result has been selected.
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/27/07
	 */
	public function isSelected () {
		return false;
	}
	
	/**
	 * Answer the name for the agent or an Id if no name exists
	 * 
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getName () {
		if ($this->name)
			return $this->name;
		else
			return $this->getIdString();
	}
	
	/**
	 * Answer some XHTML markup that can be used to display the result.
	 * 
	 * @return string
	 * @access public
	 * @since 11/27/07
	 */
	public function getMarkup () {
		ob_start();
		print "<a href='#' onclick=\"AgentInfoPanel.run('".addslashes($this->getIdString())."', '".addslashes($this->getName())."', this); return false;\">";
		print $this->getName();
		print "</a>";
		
		return ob_get_clean();
	}
	
}

?>