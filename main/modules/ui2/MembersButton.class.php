<?php
/**
 * @since 2/5/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This field will print out a membership button with an associated hidden-field
 * 
 * @since 2/5/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class MembershipButton
	extends WHiddenField
{
	/**
	 * Constructor
	 * 
	 * @param string $slotName
	 * @return void
	 * @access public
	 * @since 2/16/09
	 */
	public function __construct ($slotName) {
		ArgumentValidator::validate($slotName, NonzeroLengthStringValidatorRule::getRule());
		$this->slot = $slotName;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$this->writeHeadJS();
		
		$name = RequestContext::name($fieldName);
		
		ob_start();
		print "\n\t\t\t <button onclick='SiteMemberPanel.run(this, this.form[\"".$name."\"], \"".$this->slot."\"); return false;'>"._("Add/Remove Members")."</button>";
		$val = htmlspecialchars($this->_value, ENT_QUOTES);
		print "<input type='hidden' name='$name' id='$name' value='$val' />";
// 		print "<input type='hidden' name='$name' id='$name' value='1111342=Adam%20Franco&amp;348572=Bob%20Jones&amp;321231=Linda%20Smith' />";
		
		return ob_get_clean();
	}

	private static $headJSWritten;	
	
	/**
	 * Write needed javascript to the document head
	 * 
	 * @return void
	 * @access protected
	 * @since 2/5/09
	 */
	protected function writeHeadJS () {
		if (!isset(self::$headJSWritten)) {
			ob_start();
			print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/scriptaculous-js/lib/prototype.js'></script> ";
			print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/scriptaculous-js/src/scriptaculous.js'></script> ";
			print  "\n\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/Panel.js'></script>";
			print  "\n\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
			print  "\n\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/SiteMemberPanel.js'></script>";
			
			$harmoni = Harmoni::instance();		

			$outputHandler = $harmoni->getOutputHandler();
			$outputHandler->setHead($outputHandler->getHead().ob_get_clean());
			
			self::$headJSWritten = true;
		}
	}
	
}

?>