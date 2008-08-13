<?php
/**
 * @since 8/13/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This search source searches for agents and adds them to the add-site wizard's 
 * site-wide roles property.
 * 
 * @since 8/13/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AddSiteAgentSearchSource
	extends AgentSearchSource
{
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 8/13/08
	 */
	public function __construct (WSearchField $parent) {
		$this->buttons = array();
		$this->parent = $parent;
	}
	
	/**
	 * Set the roles property to update when an agent is chosen
	 * 
	 * @param object RadioMatrix $rolesProperty
	 * @return void
	 * @access public
	 * @since 8/13/08
	 */
	public function setRolesProperty (RadioMatrix $rolesProperty) {
		$this->rolesProperty = $rolesProperty;
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
		$results = parent::getResults($searchTerm);
		
		return $results;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 * @since 11/27/07
	 */
	public function update ($fieldName) {
		$agentMgr = Services::getService("Agent");
		$idMgr = Services::getService("Id");
		
		if (!isset($this->rolesProperty))
			throw new OperationFailedException('Roles property must be set to use this component.');
		
		foreach ($this->buttons as $id => $button) {
			$key = $this->getKey($id);
			$button->update($fieldName."_".$key);
			if ($button->getAllValues()) {
				$agentId = $idMgr->getId($id);
				$agent = $agentMgr->getAgentOrGroup($agentId);
				$this->rolesProperty->addField($agentId->getIdString(), $agent->getDisplayName(), 'no_access');
			}
		}
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
				
				// Create a button for each result.
				if (!isset($this->buttons[$result->getIdString()])) {
					$this->buttons[$result->getIdString()] = WEventButton::withLabel('+');
					$this->buttons[$result->getIdString()]->setParent($this->parent);	
				}
					
				print $this->buttons[$result->getIdString()]->getMarkup($fieldName.'_'.$this->getKey($result->getIdString()));
				
				print "\n\t\t\t</td>";
				print "\n\t\t</tr>";
				
				$colorKey = intval(!$colorKey);
			}
			print "\n\t</table>";
		}
	}
	
	/**
	 * Answer the key for an Id
	 * 
	 * @param string $id
	 * @return string
	 * @access protected
	 * @since 8/13/08
	 */
	protected function getKey ($id) {
		return preg_replace('/[^a-z0-9_-]/i', '_', $id);
	}
}

?>