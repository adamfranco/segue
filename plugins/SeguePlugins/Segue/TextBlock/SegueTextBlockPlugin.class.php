<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueTextBlockPlugin.class.php,v 1.3 2006/01/26 18:51:41 adamfranco Exp $
 */

require_once (HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueTextBlockPlugin.class.php,v 1.3 2006/01/26 18:51:41 adamfranco Exp $
 */
class SegueTextBlockPlugin
	extends SeguePluginsAjaxPlugin
// 	extends SeguePluginsPlugin
{
		
	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function initialize () {
		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function update ( $request ) {
 		if ($this->getFieldValue('submit')) { 			
 			$this->setTitle($this->cleanHTML($this->getFieldValue('title')));
 			$this->setContent($this->cleanHTML($this->getFieldValue('content')));
 		}
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function getMarkup () {
 		ob_start();
 		
 		if ($this->getFieldValue('edit') && $this->canModify()) {
			print "\n".$this->formStartTagWithAction();
 			
 			print "\n\t<input type='text' name='".$this->getFieldName('title')."' value='".$this->getTitle()."' size='50'/>";
 			
 			print "\n\t<br/>";
 			print "\n\t<textarea name='".$this->getFieldName('content')."' rows='5' cols='50'>".$this->getContent()."</textarea>";
 			
 			print "\n\t<br/>";
 			print "\n\t<input type='submit' value='"._('Submit')."' name='".$this->getFieldName('submit')."'/>";
 			
 			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSend()."/>";
 			
			print "\n</form>";
 		} else if ($this->canView()) {
	 		print "\n".$this->getContent();
	 		if ($this->canModify()) {
				print "\n<div style='text-align: right'>";
				print "\n\t<a href=".$this->url(array('edit' => 'true')).">edit</a>";
				print "\n</div>";
			}
 		}
 		
 		return ob_get_clean();
 	}
	
}

?>