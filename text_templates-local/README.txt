----------------------
 About Text-Templates
----------------------

Text-templates are simple systems for creating dynamic text similar to MediaWiki templates.
Access to text-templates is done by adding the following markup to text:
	
	{{template_name}}
	
Additionally, optional named parameters can also be passed to text-templates:
	
	{{template_name|paramName=value|param2Name=value2}}

Text-templates can be useful for inserting predefined scripts or objects into HTML 
without allowing cross-site scripting attacks. They can also be used to make common
html blocks easier to display or change.

----------------------
 Writing Text Templates
----------------------
Name:
Text-template names should be lowercase letters, numbers, and underscore characters.

Location:
Text-templates that you write should go in the following directory.
	segue/text_templates-local/

File Name:
Name you template file with the template name plus '.class.php'. For the following examples
will will use the name 'video' for the template described. This means the template file
would be:
	segue/text_templates-local/video.class.php
	
Class:
Text-templates must contain a class that implements the Segue_Wiki_TextTemplate interface
which defines two methods: generate($paramList) and getHtmlMatches($text). 
It must be named with its name prepended by 'Segue_TextTemplates_'. 
Using our video example:
	
	<?php
	
	class Segue_TextTemplates_video
		implements Segue_Wiki_TextTemplate 
	{
		
		....
		
		/**
		 * Generate HTML given a set of parameters.
		 * 
		 * @param array $paramList
		 * @return string The HTML markup
		 * @access public
		 */
		public function generate (array $paramList) {
			....
		}
		
		/**
		 * Answer an array of strings in the HTML that look like this template's output
		 * and list of parameters that the HTML corresponds to. e.g:
		 * 	array(
		 *		"<img src='http://www.example.net/test.jpg' width='350px'/>" 
		 *				=> array (	'server'	=> 'www.example.net',
		 *							'file'		=> 'test.jp',
		 * 							'width'		=> '350px'))
		 * 
		 * This method may throw an UnimplementedException if this is not supported.
		 *
		 * @param string $text
		 * @return array
		 * @access public
		 */
		public function getHtmlMatches ($text) {
			....
		}
		
		....
	}
	
	?>

Configuration:
Default configuration files are to be placed in the main Segue configuration directory
named 'text_template-' followed by their name followed by '_default.conf.php'. Custom
configuration files are named the same, but followed by '.conf.php'.
Using our video example the default config file is located at:
	segue/config/text_template-video_default.conf.php

and a custom config with different settings would live at:
	segue/config/text_template-video.conf.php

You can access the instance of you template in the config file using the getTextTemplate()
method on the WikiResolver object. e.g.:

	$video = WikiResolver::instance()->getTextTemplate('video');

