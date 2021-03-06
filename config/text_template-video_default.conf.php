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

$video = WikiResolver::instance()->getTextTemplate('video');


/*********************************************************
 * YouTube
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'youtube', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1###FMT###"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1###FMT###" type="application/x-shockwave-flash" allowfullscreen="true" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '344');
$service->setHtmlPlayerRegex('/http:\/\/www\.youtube\.com\/v\//');
$service->setHtmlIdRegex('/http:\/\/www\.youtube\.com\/v\/([a-z0-9_-]+)/i');
$service->addParam('fmt', '/^[0-9]*$/', '', '&amp;ap=%2526fmt%3D');
$service->setHtmlParamsRegex('/\&ap=%2526fmt%3D([0-9]+)/', array(1 => 'fmt'));

// Playlists
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'youtube_playlist', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.youtube.com/p/###ID###&amp;hl=en&amp;fs=1###FMT###"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/p/###ID###&amp;hl=en&amp;fs=1###FMT###" type="application/x-shockwave-flash" allowfullscreen="true" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '344');
$service->setHtmlPlayerRegex('/http:\/\/www\.youtube\.com\/p\//');
$service->setHtmlIdRegex('/http:\/\/www\.youtube\.com\/p\/([a-z0-9_-]+)/i');
$service->addParam('fmt', '/^[0-9]*$/', '', '&amp;ap=%2526fmt%3D');
$service->setHtmlParamsRegex('/\&ap=%2526fmt%3D([0-9]+)/', array(1 => 'fmt'));


/*********************************************************
 * Google Video
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'google', 
	'<embed id="VideoPlayback" style="width:###WIDTH###px;height:###HEIGHT###px" allowFullScreen="true" src="http://video.google.com/googleplayer.swf?docid=###ID###&amp;hl=en&amp;fs=true" type="application/x-shockwave-flash"> </embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '326');
$service->setHtmlPlayerRegex('/http:\/\/video\.google\.com\/googleplayer.swf/');
$service->setHtmlIdRegex('/docid=([0-9-]+)/');


/*********************************************************
 * Vimeo
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'vimeo', 
	'<object width="###WIDTH###" height="###HEIGHT###">	<param name="allowfullscreen" value="true" />	<param name="allowscriptaccess" value="always" />	<param name="movie" value="http://www.vimeo.com/moogaloop.swf?clip_id=###ID###&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" />	<embed src="http://www.vimeo.com/moogaloop.swf?clip_id=###ID###&amp;server=www.vimeo.com&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '302');
$service->setHtmlPlayerRegex('/http:\/\/www\.vimeo\.com\/moogaloop\.swf/');
$service->setHtmlIdRegex('/clip_id=([0-9]+)/');


/*********************************************************
 * Hulu
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'hulu', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.hulu.com/embed/###ID###"></param><embed src="http://www.hulu.com/embed/###ID###" type="application/x-shockwave-flash"  width="###WIDTH###" height="###HEIGHT###"></embed></object>'
));
$service->setDefaultValue('width', '512');
$service->setDefaultValue('height', '296');
$service->setHtmlPlayerRegex('/http:\/\/www\.hulu\.com\/embed\//');
$service->setHtmlIdRegex('/http:\/\/www\.hulu\.com\/embed\/([a-zA-Z0-9_-]+)/');


/*********************************************************
 * TeacherTube
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'teachertube', 	
	'<embed src="http://www.teachertube.com/skin-p/mediaplayer.swf" width="###WIDTH###" height="###HEIGHT###" type="application/x-shockwave-flash" allowfullscreen="true" menu="false" flashvars="height=###HEIGHT###&width=###WIDTH###&file=http://www.teachertube.com/flvideo/###ID###.flv&image=http://www.teachertube.com/thumb/###ID###.jpg&location=http://www.teachertube.com/skin-p/mediaplayer.swf&logo=http://www.teachertube.com/images/greylogo.swf&searchlink=http://teachertube.com/search_result.php%3Fsearch_id%3D&frontcolor=0xffffff&backcolor=0x000000&lightcolor=0xFF0000&screencolor=0xffffff&autostart=false&volume=80&overstretch=fit"></embed>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '350');
$service->setHtmlPlayerRegex('/http:\/\/www\.teachertube\.com\/(?:player\/)?[a-z0-9_-]+\/mediaplayer\.swf/');
$service->setHtmlIdRegex('/flvideo\/([a-z0-9_-]+)\.flv/i');


/*********************************************************
 * Blip TV
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'bliptv', 	
	'<embed src="http://blip.tv/play/###ID###" type="application/x-shockwave-flash" width="###WIDTH###" height="###HEIGHT###" allowscriptaccess="always" allowfullscreen="true"></embed>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '350');
$service->setHtmlPlayerRegex('/http:\/\/blip\.tv\/play\//');
$service->setHtmlIdRegex('/http:\/\/blip\.tv\/play\/([a-zA-Z0-9_-]+)/i');


/*********************************************************
 * MiddMedia
 *********************************************************/
/*
$service = $video->addService(new Segue_TextTemplates_Video_MiddMediaService(
        'middmedia',
        '<embed src="http://middmedia.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddmedia.middlebury.edu%2Fvod%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27###GENERATED_ID###%27###SPLASH_IMAGE_URL###%7D" width="###WIDTH###" height="###HEIGHT###" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '300');
$service->setHtmlPlayerRegex('/src=[\'"]http:\/\/middmedia\.middlebury\.edu\/flowplayer\/FlowPlayerLight\.swf/');
$service->setHtmlIdRegex('/
	# param name
	videoFile%3A%27
	
	# optional type and delimiter
	(?:
		(?: mp3|mp4)
		(?: :|%3A)
	)?
	
	# Directory followed by a slash
	(?: [a-zA-Z0-9_%\.-]+)
	(?: \/|%2F)
	
	
	# File-name with optional extension
	(
		[a-zA-Z0-9_%\.-]+
		(?: \.[a-zA-Z0-9]+)?
	)
	
	# end quote
	%27
	
/x');
$service->setParamRegex('id', '/^[a-z0-9%\._\s-]+\.[a-z0-9]+$/i');

$service->addParam('dir', '/^[a-z0-9\._-]+$/i', 'unknown_dir');
$harmoni = Harmoni::instance();
$service->addParam('splash_image_url', 
'/^((https?:\/\/[a-z0-9\.\/_&=%+~-]*.jpg)|('.str_replace('.', '\.', str_replace('/', '\/', $harmoni->request->quickURL('repository', 'viewfile'))).'[a-z0-9\.\/_&=%+~-]*))?$/i', '', '%2CsplashImageFile%3A%27', '%27');
$service->setHtmlParamsRegex('/
	# param name
	videoFile%3A%27
	
	# optional type and delimiter
	(?:
		(?: mp3|mp4)
		(?: :|%3A)
	)?
	
	# Directory followed by a slash
	([a-zA-Z0-9_%\.-]+)
	(?: \/|%2F)
	
	
	# File-name with extension
	([a-zA-Z0-9_%\.-]+\.[a-zA-Z0-9]+)
	
	# end quote
	%27
	
/x', 
	array(1 => 'dir'));
*/

/*********************************************************
 * Redirected Middtube
 *********************************************************/
/*
$service = $video->addService(new Segue_TextTemplates_Video_MiddTubeService(
        'middtube',
        '<embed src="http://middmedia.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddmedia.middlebury.edu%2Fvod%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27###GENERATED_ID###%27###SPLASH_IMAGE_URL###%7D" width="###WIDTH###" height="###HEIGHT###" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '300');
$service->setHtmlPlayerRegex('/src=[\'"]http:\/\/middtube\.middlebury\.edu\/flowplayer\/FlowPlayerLight\.swf/');
$service->setHtmlIdRegex('/
	# param name
	videoFile%3A%27
	
	# optional type and delimiter
	(?:
		(?: mp3|mp4)
		(?: :|%3A)
	)?
	
	# Directory followed by a slash
	(?: [a-zA-Z0-9_%\.-]+)
	(?: \/|%2F)
	
	
	# File-name with optional extension
	(
		[a-zA-Z0-9_%\.-]+
		(?: \.[a-zA-Z0-9]+)?
	)
	
	# end quote
	%27
	
/x');
$service->setParamRegex('id', '/^[a-z0-9:%\._\s-]+$/i');

$service->addParam('user', '/^[a-z0-9\._-]+$/i', 'unknown_dir');
$harmoni = Harmoni::instance();
$service->addParam('splash_image_url', 
'/^((https?:\/\/[a-z0-9\.\/_&=%+~-]*.jpg)|('.str_replace('.', '\.', str_replace('/', '\/', $harmoni->request->quickURL('repository', 'viewfile'))).'[a-z0-9\.\/_&=%+~-]*))?$/i', '', '%2CsplashImageFile%3A%27', '%27');
$service->setHtmlParamsRegex('/
	# param name
	videoFile%3A%27
	
	# optional type and delimiter
	(?:
		(?: mp3|mp4)
		(?: :|%3A)
	)?
	
	# Directory followed by a slash
	([a-zA-Z0-9_%\.-]+)
	(?: \/|%2F)
	
	
	# File-name with extension
	([a-zA-Z0-9_%\.-]+\.[a-zA-Z0-9]+)
	
	# end quote
	%27
	
/x', 
	array(1 => 'user'));
*/

/*********************************************************
 * MiddTube
 *********************************************************/
/*
$service = $video->addService(new Segue_TextTemplates_Video_Service(
        'middtube',
        '<embed src="http://middtube.middlebury.edu/flowplayer/FlowPlayerLight.swf?config=%7Bembedded%3Atrue%2CstreamingServerURL%3A%27rtmp%3A%2F%2Fmiddtube%2Emiddlebury%2Eedu%3A1935%2F###USER###%27%2CautoPlay%3Afalse%2Cloop%3Afalse%2CinitialScale%3A%27fit%27%2CvideoFile%3A%27###ID###%27###SPLASH_IMAGE_URL###%7D" width="###WIDTH###" height="###HEIGHT###" scale="fit" bgcolor="#111111" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '300');
$service->setHtmlPlayerRegex('/src=[\'"]http:\/\/middtube\.middlebury\.edu\/flowplayer\/FlowPlayerLight\.swf/');
$service->setHtmlIdRegex('/videoFile%3A%27(((mp3|mp4)(:|%3A))?[a-zA-Z0-9_-]+)%27/');
$service->setParamRegex('id', '/^(mp3:|mp4:)?[a-z0-9\._\s-]+$/i');

$service->addParam('user', '/^[a-z0-9\._-]+$/', 'unknown_user');
$harmoni = Harmoni::instance();
$service->addParam('splash_image_url', 
'/^((https?:\/\/[a-z0-9\.\/_&=%+~-]*.jpg)|('.str_replace('.', '\.', str_replace('/', '\/', $harmoni->request->quickURL('repository', 'viewfile'))).'[a-z0-9\.\/_&=%+~-]*))?$/i', '', '%2CsplashImageFile%3A%27', '%27');
$service->setHtmlParamsRegex('/%3A1935%2F([a-zA-Z0-9_-]+)/', array(1 => 'user'));
*/


/*********************************************************
 * Unknown Sources
 *********************************************************/
// This will match all other embedded flash and replace with with a notice.
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'unknown', 
	'<div>'._('Your video (from an untrusted source: ###ID###) was stripped for security purposes. Please contact the Segue administrator to enable video from this source.').'</div>'
));
$service->setParamRegex('id', '/[a-z0-9\._-]+/');
$service->setHtmlPlayerRegex('/.*/');
$service->setHtmlIdRegex('/https?:\/\/([^\/?]+)/');