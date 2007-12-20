<?php
/**
 * @since 12/20/07
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UI2.class.php,v 1.1 2007/12/20 21:51:50 adamfranco Exp $
 */ 

/**
 * This class holds some general-purpose functions for UI2
 * 
 * @since 12/20/07
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UI2.class.php,v 1.1 2007/12/20 21:51:50 adamfranco Exp $
 */
class UI2 {
		
	/**
	 * Add a supported-browser warning
	 *
	 * @return void
	 * @access public
	 * @since 12/20/07
	 * @static
	 */
	public static function addBrowserWarning () {
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		
		$polyphonyPath = POLYPHONY_PATH;
		$msieWarning = _('The \'New Mode\' editing interface does not support old versions of Microsoft Internet Explorer.\n\nPlease use the \'Classic Mode\' editing interface or use one of the following browsers:\n\tFirefox\n\tOpera\n\tSafari\n\tInternet Explorer (version 7 and later)');
		
		$warning = <<<END
		
		<script src='$polyphonyPath/javascript/brwsniff.js' type='text/javascript'></script>
		<script type='text/javascript'>
		// <![CDATA[
		
		var br = getBrowser();
		
		// Display warning for old MSIE
		if (br[0] == 'msie' && getMajorVersion(br[1]) < 7) {
			alert("$msieWarning");
		}
		
		// ]]>
		</script>

END;
		$outputHandler->setHead($outputHandler->getHead().$warning);
	}
	
}

?>