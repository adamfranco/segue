<?php
/**
 * @package segue.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: filebrowser.act.php,v 1.4 2008/04/13 18:48:45 adamfranco Exp $
 */ 

/**
 * The File browser provides a browser window into the media files available
 * 
 * @package segue.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: filebrowser.act.php,v 1.4 2008/04/13 18:48:45 adamfranco Exp $
 * @since 4/28/05
 */
class filebrowserAction
	extends Action 
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _('File Browser');
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$title = $this->getHeadingText();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('media');
		$nodeId = RequestContext::value('node');
		$harmoni->request->endNamespace();
		$POLYPHONY_PATH = POLYPHONY_PATH;
		$MYPATH = MYPATH;
		
		print <<<END
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>$title</title>

END;
	
	require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");

		print <<< END
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/CenteredPanel.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/TabbedContent.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/prototype.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/js_quicktags.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/brwsniff.js'></script>
	<script type='text/javascript' src='$MYPATH/javascript/MediaLibrary.js'></script>
	<link rel='stylesheet' type='text/css' href='$MYPATH/javascript/MediaLibrary.css'/>
	
	<script type='text/javascript'>
		// <![CDATA[
		
		MediaLibrary.prototype.onClose = function () { 
			window.close();
		}
		
		// ]]>
	</script>
	
</head>
<body onload="this.onUse = function (mediaFile) { window.opener.SetUrl(mediaFile.getUrl());}; MediaLibrary.run('$nodeId', this); ">
	
</body>
</html>


END;
		
		exit;
	}
}