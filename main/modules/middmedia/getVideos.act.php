<?php
/**
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/MiddMediaAction.class.php');

/**
 * Load a list of directories for the current user and return them as an XML file
 * for the JS media library.
 * 
 * @since 1/13/09
 * @package segue.middmedia
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class getVideosAction
	extends MiddMediaAction
{
		
	/**
	 * Build the XML content for this action
	 * 
	 * @return void
	 * @access protected
	 * @since 1/14/09
	 */
	protected function buildXml () {
		foreach ($this->getVideos(RequestContext::value('directory')) as $file) {
			print "\n<file ";
			print "name=\"".$file['name']."\" ";
			print "httpUrl=\"".$file['httpurl']."\" ";
			print "rtmpUrl=\"".$file['rtmpurl']."\" ";
			print "mimeType=\"".$file['mimetype']."\" ";
			print "size=\"".$file['size']."\" ";
			print "date=\"".$file['date']."\" ";
			if (isset($file['creator']))
				print "creator=\"".$file['creator']."\" ";
			else
				print "creator=\"\" ";
			
			if (isset($file['fullframeurl']))
				print "fullframeUrl=\"".$file['fullframeurl']."\" ";
			else
				print "fullframeUrl=\"\" ";
			
			if (isset($file['thumburl']))
				print "thumbUrl=\"".$file['thumburl']."\" ";
			else
				print "thumbUrl=\"\" ";
				
			if (isset($file['splashurl']))
				print "splashUrl=\"".$file['splashurl']."\" ";
			else
				print "splashUrl=\"\" ";
			print ">";
			print "\n\t<embedCode><![CDATA[";
			print $file['embedcode'];
			print "]]></embedCode>";
			print "</file>";
		}
	}
	
}

?>