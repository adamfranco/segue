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

$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'youtube', 
	'<object width="###WIDTH###" height="###HEIGHT###"><param name="movie" value="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/###ID###&amp;hl=en&amp;fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="###WIDTH###" height="###HEIGHT###"></embed></object>'
// 	'<object type="application/x-shockwave-flash" data="http://www.youtube.com/v/###ID###" width="###WIDTH###" height="###HEIGHT###" wmode="transparent"><param name="movie" value="http://www.youtube.com/v/###ID###" /></object>'
));
$service->setDefaultValue('width', '425');
$service->setDefaultValue('height', '344');
$service->setHtmlRegex('/
(?: <object|<embed )				# opening tag

(?: [^>]* width=[\'"]([0-9]+)(?: px)?[\'"])?	# optional width attribute
(?: [^>]* height=[\'"]([0-9]+)(?: px)?[\'"])?	# optional height attribute

(?: [^>]* style=[\'"]			# optional style-based width and height
	width:\s?([0-9]+)(?: px)?	# optional width attribute
	[;\s]*
	height:\s?([0-9]+)(?: px)?	# optional height attribute
	[;\s]*
[\'"])?

.*

(?: src|data)=[\'"]http:\/\/www.youtube.com\/v\/([a-z0-9_-]+)	# Match the Id in a YouTube URL

(?: .* width=[\'"]([0-9]+)(?: px)?[\'"])?	# optional width attribute
(?: .* height=[\'"]([0-9]+)(?: px)?[\'"])?	# optional height attribute

.*

(?: <\/object>|<\/embed> )			# closing tag
/ixs',
	array(
		1 => 'width',
		2 => 'height',
		3 => 'width',
		4 => 'height',
		5 => 'id',
		6 => 'width',
		7 => 'height'
	));
	
$service = $video->addService(new Segue_TextPlugins_Video_Service(
	'google', 
	'<embed id="VideoPlayback" style="width:###WIDTH###px;height:###HEIGHT###px" allowFullScreen="true" src="http://video.google.com/googleplayer.swf?docid=###ID###&amp;hl=en&amp;fs=true" type="application/x-shockwave-flash"> </embed>'
));
$service->setDefaultValue('width', '400');
$service->setDefaultValue('height', '326');
$service->setHtmlRegex('/
(?: <object|<embed )				# opening tag

(?: [^>]* width=[\'"]([0-9]+)(?: px)?[\'"])?	# optional width attribute
(?: [^>]* height=[\'"]([0-9]+)(?: px)?[\'"])?	# optional height attribute

(?: [^>]* style=[\'"]			# optional style-based width and height
	width:\s?([0-9]+)(?: px)?	# optional width attribute
	[;\s]*
	height:\s?([0-9]+)(?: px)?	# optional height attribute
	[;\s]*
[\'"])?

.*

(?: src|data)=[\'"]http:\/\/video.google.com\/googleplayer.swf\?docid=([0-9]+)	# Match the Id in a GoogleVideo URL

(?: .* width=[\'"]([0-9]+)(?: px)?[\'"])?	# optional width attribute
(?: .* height=[\'"]([0-9]+)(?: px)?[\'"])?	# optional height attribute

.*

(?: <\/object>|<\/embed> )			# closing tag
/ixs',
	array(
		1 => 'width',
		2 => 'height',
		3 => 'width',
		4 => 'height',
		5 => 'id',
		6 => 'width',
		7 => 'height'
	));