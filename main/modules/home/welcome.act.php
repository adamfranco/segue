<?php
/**
 * @package concerto.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.3 2006/01/13 18:36:32 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package concerto.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: welcome.act.php,v 1.3 2006/01/13 18:36:32 adamfranco Exp $
 */
class welcomeAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Welcome to Concerto");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		ob_start();
		print "<p>";
		print _("<strong>Concerto</strong> is a digital assets management tool developed at Middlebury College.");
		print "</p>\n<p>";
		print _("The two main parts of <strong>Concerto</strong> are the <em>Collections</em> of digital <em>Assets</em> and the <em>Exhibitions</em> of <em>Slide-Shows</em>. Click on the links to the left to start exploring <strong>Concerto</strong>.");
		print "</p>\n<p>";
		print _("Some <em>Collections</em>, <em>Exhibitions</em>, <em>Assets</em>, and <em>Slide-Shows</em> may be restricted to certain users or groups of users. Log in above to ensure your greatest access to all parts of the system.");
		print "</p>";
		
		$actionRows->add(
			new Block(ob_get_contents(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
		ob_end_clean();
	}	
}

?>