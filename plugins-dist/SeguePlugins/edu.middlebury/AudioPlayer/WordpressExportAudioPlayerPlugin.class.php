<?php
/**
 * @since 8/1/2008
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyAudioPlayerPlugin.class.php,v 1.19 2008/03/18 17:32:12 adamfranco Exp $
 */

/**
 * A Simple Plugin for making editable blocks of text
 * 
 * @since 8/1/2008
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2008, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyAudioPlayerPlugin.class.php,v 0.5 2008/03/18 17:32:12 davidfouhey Exp $
 */
class WordpressExportAudioPlayerPlugin
	extends EduMiddleburyAudioPlayerPlugin
{
	/**
	 * Answer the download bar.
	 * 
	 * @return string
	 * @access public
	 * @since 4/25/07
	 */
	function getDownloadBar () {
		ob_start();
		
		$file = $this->getMediaFile();
		if ($file) {
			print "\n";	
			$url = $file->getUrl();
			$id = $this->getId();

			if ($this->showDownloadLink()) {
				print "\n\t\t<div style='float: right; margin-top: 12px;'>";
				print "\n\t\t<img src='".MYPATH."/images/downarrow.gif' align='top' width='15' height='15' alt='"._('download')."'/>";
				print "\n\t\t\t<a style='text-decoration: none;' href='";
				print $file->getUrl();
				print "'>";
				print "<strong>"._("Download")."</strong>";
				print "</a>";
				
				$size = $file->getSize();
				
				if ($size->value()) {
					$sizeString = $size->asString();
				} else {
					$sizeString = _("unknown size");
				}
				print "\n\t\t<span style='font-size: 90%;'>(".$sizeString.")</span>";
				print "\n\t</div>";	
			}


			print "\n<div style='float: left;'>";			
            print "\n\t[audio:".$url."]";
			print "<br/>\n";
			
			print "\n</div>";
			
			print "<div style='clear: both; margin-bottom: 6px;'>";
			print $this->getCitation($file);
			print "\n</div>";
			
			

			
		} else {
			print "\n<div class='plugin_empty'>";
			print _("No file has been selected yet. ");
			print "</div>";
		}
		
		return ob_get_clean();
	}
}

