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
	'[youtube ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

// Playlists
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'youtube_playlist', 
	'[youtubeplaylist ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

/*********************************************************
 * Google Video
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'google', 
	'[google ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');


/*********************************************************
 * Vimeo
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'vimeo', 
	'[vimeo ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

/*********************************************************
 * Hulu
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'hulu', 
	'[hulu ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

/*********************************************************
 * TeacherTube
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'teachertube', 
	'[teachertube ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

/*********************************************************
 * Blip TV
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_Service(
	'bliptv', 
	'[bliptv ###ID### ###WIDTH###  ###HEIGHT###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');

/*********************************************************
 * MiddMedia
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_MiddMediaWordpressService(
	'middmedia', 
	'[middmedia 0 ###GENERATED_ID### width:###WIDTH###  height:###HEIGHT### splashimage:###SPLASH_IMAGE_URL###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');
$service->setParamRegex('id', '/^[a-z0-9%\._\s-]+\.[a-z0-9]+$/i');
$service->addParam('dir', '/^[a-z0-9\._-]+$/i', 'unknown_dir');
$harmoni = Harmoni::instance();
$service->addParam('splash_image_url', 
'/^((https?:\/\/[a-z0-9\.\/_&=%+~-]*.jpg)|('.str_replace('.', '\.', str_replace('/', '\/', $harmoni->request->quickURL('repository', 'viewfile'))).'[a-z0-9\.\/_&=%+~-]*))?$/i', '', '%2CsplashImageFile%3A%27', '%27');


/*********************************************************
 * Redirected Middtube
 *********************************************************/
$service = $video->addService(new Segue_TextTemplates_Video_MiddMediaWordpressService(
	'middtube', 
	'[middmedia 0 ###GENERATED_ID### width:###WIDTH###  height:###HEIGHT### splashimage:###SPLASH_IMAGE_URL###]'
));
$service->setParamRegex('width', '/^[0-9]*$/');
$service->setParamRegex('height', '/^[0-9]*$/');
$service->setDefaultValue('width', '');
$service->setDefaultValue('height', '');
$service->setParamRegex('id', '/^[a-z0-9%\._\s-]+\.[a-z0-9]+$/i');
$service->addParam('dir', '/^[a-z0-9\._-]+$/i', 'unknown_dir');
$harmoni = Harmoni::instance();
$service->addParam('splash_image_url', 
'/^((https?:\/\/[a-z0-9\.\/_&=%+~-]*.jpg)|('.str_replace('.', '\.', str_replace('/', '\/', $harmoni->request->quickURL('repository', 'viewfile'))).'[a-z0-9\.\/_&=%+~-]*))?$/i', '', '%2CsplashImageFile%3A%27', '%27');


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