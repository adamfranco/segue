<?php

/**
 * Embedded video configuration
 *
 * USAGE: Copy this file to video.conf.php to set custom values.
 *
 * @package segue.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 */

$video = WikiResolver::instance()->getTextPlugin('video');


/*********************************************************
 * YouTube
 *********************************************************/
$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'youtube', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '344');
$service->setHtmlPlayerRegex('/http:\/\/www.youtube.com\/v\//');
$service->setHtmlIdRegex('/http:\/\/www.youtube.com\/v\/([a-z0-9_-]+)/i');

// Playlists
$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'youtube_playlist', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.youtube.com/p/###ID###&amp;hl=en&amp;fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/p/###ID###&amp;hl=en&amp;fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '344');
$service->setHtmlPlayerRegex('/http:\/\/www.youtube.com\/p\//');
$service->setHtmlIdRegex('/http:\/\/www.youtube.com\/p\/([a-z0-9_-]+)/i');


/*********************************************************
 * Google Video
 *********************************************************/
$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'google', 
	'<embed id="VideoPlayback" style="width:###WIDTH###px;height:###HEIGHT###px" allowFullScreen="true" src="http://video.google.com/googleplayer.swf?docid=###ID###&amp;hl=en&amp;fs=true" type="application/x-shockwave-flash"> </embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '326');
$service->setHtmlPlayerRegex('/http:\/\/video.google.com\/googleplayer.swf/');
$service->setHtmlIdRegex('/docid=([0-9-]+)/');




/*********************************************************
 * Unknown Sources
 *********************************************************/
// This will match all other embedded flash and replace with with a notice.
$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'unknown', 
	'<div>'._('Your video (from an untrusted source: ###ID###) was stripped for security purposes. Please contact the Segue administrator to enable video from this source.').'</div>'
));
$service->setParamRegex('id', '/.+/');
$service->setHtmlPlayerRegex('/.*/');
$service->setHtmlIdRegex('/https?:\/\/([^\/?]+)/');