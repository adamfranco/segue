<?php
/**
 * @since 2/28/08
 * @package segue.logs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: usage.act.php,v 1.1 2008/02/29 20:04:07 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * This action displays some usage statistics
 * 
 * @since 2/28/08
 * @package segue.logs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: usage.act.php,v 1.1 2008/02/29 20:04:07 adamfranco Exp $
 */
class usageAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/28/08
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 2/28/08
	 */
	function getHeadingText () {
		return _("Segue Usage Statistics");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/28/08
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$actionRows = $this->getActionRows();
		ob_start();
		print "\n<form action='".$harmoni->request->quickURL()."' method='get'>";
		
		print "\n\t<input name='".RequestContext::name('interval_size')."' size='3' value='";
		if (RequestContext::value('interval_size'))
			print RequestContext::value('interval_size');
		else
			print '3';
		print "'/>";
		
		if (RequestContext::value('interval_unit'))
			$val = RequestContext::value('interval_unit');
		else
			$val = 'MONTH';
		$units = array('DAY', 'WEEK', 'MONTH', 'YEAR');
		print "\n\t<select name='".RequestContext::name('interval_unit')."'>";
		foreach ($units as $unit) {
			print "\n\t\t<option value='".$unit."'";
			if ($val == $unit)
				print " selected='selected'";
			print ">".ucfirst(strtolower($unit.'s'))."</option>";
		}
		print "\n\t</select>";
		
		print "\n\t<input type='submit'/>";
		
		print "\n</form>";
		print "\n<p>";
		$params = array();
		if (RequestContext::value('interval_size'))
			$params['interval_size'] = RequestContext::value('interval_size');
		if (RequestContext::value('interval_unit'))
			$params['interval_unit'] = RequestContext::value('interval_unit');
		print "<img src='".$harmoni->request->quickURL("logs", "usage_graph", $params)."' alt='"._("Usage Graph")."'/>";
		print "</p>";
		
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}	
	
}

?>