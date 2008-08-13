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
 * A custom search field and results for adding agents to the site-wide roles list
 * 
 * @since 8/13/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class AddSiteAgentSearchField
	extends WSearchField
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 8/13/08
	 */
	public function __construct () {
		parent::__construct();
		$this->setSearchSource(new AddSiteAgentSearchSource ($this));
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
		$this->searchSource->setRolesProperty($rolesProperty);
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
		parent::update($fieldName);
		
		$this->searchSource->update($fieldName);
	}
	
}

?>