<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(dirname(__FILE__)."/Rendering/WordpressExportSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This action will export a site to an xml file
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.8 2008/04/09 21:12:02 adamfranco Exp $
 */
class wordpressAction
	extends Action
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.view'),
			SiteDispatcher::getCurrentRootNode()->getQualifierId());
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 1/17/08
	 */
	public function execute () {
		$harmoni = Harmoni::instance();
				
		$component = SiteDispatcher::getCurrentNode();
		$site = SiteDispatcher::getCurrentRootNode();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotBySiteId($site->getId());
		
		$this->setupTextTemplates();
		
		$pluginManager = Services::getService('PluginManager');
		$audioType = new Type ('SeguePlugins', 'edu.middlebury', 'AudioPlayer');
		$pluginManager->_loadPlugins();  // Ensure that the original plugin is loaded.
		require_once(MYDIR.'/plugins-dist/SeguePlugins/edu.middlebury/AudioPlayer/WordpressExportAudioPlayerPlugin.class.php');
		$pluginManager->setPluginClass($audioType, 'WordpressExportAudioPlayerPlugin');
		
		$textType = new Type ('SeguePlugins', 'edu.middlebury', 'TextBlock');		require_once(MYDIR.'/plugins-dist/SeguePlugins/edu.middlebury/TextBlock/WordpressExportTextBlockPlugin.class.php');
		$pluginManager->setPluginClass($textType, 'WordpressExportTextBlockPlugin');
		
		try {
			// Do the export
			$visitor = new WordpressExportSiteVisitor();
			$component->acceptVisitor($visitor);
			
			// Validate the result
// 			printpre(htmlentities($visitor->doc->saveXMLWithWhitespace()));
// 			$tmp = new Harmoni_DomDocument;
// 			$tmp->loadXML($visitor->doc->saveXMLWithWhitespace());
// 			$tmp->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
// 			$visitor->doc->schemaValidateWithException(MYDIR."/doc/raw/dtds/segue2-site.xsd");
			
			header("Content-Type: text/xml");
			header('Content-Disposition: attachment; filename="'
								.basename($slot->getShortname().".xml").'"');
			$xml = $visitor->doc->saveXMLWithWhitespace();
			header('Content-Length: '.strlen($xml));
			print $xml;
		} catch (PermissionDeniedException $e) {
			return new Block(
				_("You are not authorized to export this component."),
				ALERT_BLOCK);
		} catch (Exception $e) {
			throw $e;
		}
		
		error_reporting(0);
		exit;
	}
	
	/**
	 * Answer the nodeId
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getNodeId () {
		return SiteDispatcher::getCurrentNodeId();
	}
	
	/**
	 * Set up text templates to output for wordpress.
	 * 
	 * @return null
	 */
	protected function setupTextTemplates () {
		// Initialize the text-templates
		WikiResolver::instance()->getTextTemplate('video');
		
		// Reconfigure video
		WikiResolver::instance()->replaceTextTemplate('video', new Segue_TextTemplates_video());
		$this->configureTextTemplate('video');
		
		// Reconfigure audio
		WikiResolver::instance()->replaceTextTemplate('audio', new Segue_TextTemplates_WordpressAudio());
	}
	
	/**
	 * Load any configuration files for the text-template
	 * 
	 * @param string $name
	 * @return void
	 * @access protected
	 * @since 7/16/08
	 */
	protected function configureTextTemplate ($name) {
		$name = strtolower($name);
		
		// Configure the template
		if (file_exists(MYDIR.'/config/text_template-'.$name.'-wordpress.conf.php'))
			require_once (MYDIR.'/config/text_template-'.$name.'-wordpress.conf.php');
		else if (file_exists(MYDIR.'/config/text_template-'.$name.'-wordpress_default.conf.php'))
			require_once (MYDIR.'/config/text_template-'.$name.'-wordpress_default.conf.php');
	}
}

require_once(MYDIR.'/main/library/Wiki/TextTemplate.interface.php');
require_once(MYDIR.'/text_templates-dist/video.class.php');
/**
 * This is a custom service for MiddMedia that does the extra work to configure our parameters
 * 
 * @since 1/27/09
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_Video_MiddMediaWordpressService
	extends Segue_TextTemplates_Video_MiddMediaService
{
		
	/**
	 * Based on the parameters listed, generate an identifier to use in the embed code.
	 * 
	 * @param array $params
	 * @return string
	 * @access public
	 * @since 1/27/09
	 */
	public function generateId (array $params) {
		$this->validateParam('id', $params['id']);
		$this->validateParam('dir', $params['dir']);
		
		$parts = pathinfo($params['id']);
		return rawurlencode($params['dir']).' '.rawurlencode($parts['filename'].'.'.$parts['extension']);
	}
}

require_once(MYDIR.'/text_templates-dist/audio.class.php');
/**
 * This template allows the embedding of mp3 audio in a page
 * 
 * @since 1/27/09
 * @package segue.text_templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_TextTemplates_WordpressAudio
	extends Segue_TextTemplates_audio
{
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 1/27/09
	 */
	public function generate (array $paramList) {
		if (!isset($paramList['url']))
			throw new InvalidArgumentException("url is required.");
		
		// Validate our options
		if (!preg_match('/^https?:\/\/[a-z0-9_\.\/?&=,;:%+~\s-]+$/i', $paramList['url']))
			throw new InvalidArgumentException("Invalid url.");
		
		if (preg_match('/^https?:\/\/middmedia\.middlebury\.edu\/media\/([^.]+)\/([^\/]+)$/i', $paramList['url'], $matches)) {
			return '[middmedia 0 '.$matches[1].' '.str_replace(' ', '%20', $matches[2]).']';
		}
		return '[audio:'.str_replace(' ', '%20', $paramList['url']).']';
	}
}