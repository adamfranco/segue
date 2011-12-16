<?php
/**
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.48 2008/03/25 19:41:45 adamfranco Exp $
 */
 
/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 1/13/06
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTextBlockPlugin.class.php,v 1.48 2008/03/25 19:41:45 adamfranco Exp $
 */
class WordpressExportTextBlockPlugin
	extends EduMiddleburyTextBlockPlugin
{

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
 		
		if ($this->hasContent()) {
			$abstractLength = intval($this->getRawDescription());
			if ($abstractLength) {
				$content = $this->cleanHTML($this->getContent());
				$start = $this->trimHTML($this->getContent(), $abstractLength, false);
				$pos = strpos($content, $start);
				if ($pos === 0) {
					$end = substr($content, strlen($start));
					print $start.' <!--more--> '.$end;
				} else {
					print $content;
				}
			} else {
				print "\n".$this->parseWikiText($this->cleanHTML($this->getContent()));
			}
		} else {
			print "\n<div class='plugin_empty'>";
			print _("No text has been added yet. ");
			print "</div>";
		}
 		
 		return ob_get_clean();
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	function getExtendedMarkup () {
 		return $this->getMarkup();
 	}
 }