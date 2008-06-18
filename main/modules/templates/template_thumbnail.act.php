<?php
/**
 * @since 6/12/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/modules/gui2/theme_image.act.php');

/**
 * Display a template thumbnail
 * 
 * @since 6/12/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class template_thumbnailAction
	extends theme_imageAction
{
		
	/**
	 * Answer the image requested
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access protected
	 * @since 6/12/08
	 */
	protected function getImage () {
		if (!isset($this->image)) {
			$this->image = $this->getTemplate()->getThumbnail();
		}
		return $this->image;
	}
	
	/**
	 * Answer the theme
	 * 
	 * @return object Segue_Templates_Template
	 * @access protected
	 * @since 5/13/08
	 */
	protected function getTemplate () {
		if (!isset($this->template)) {
			$templateMgr = Segue_Templates_TemplateManager::instance();
			$this->template = $templateMgr->getTemplate(RequestContext::value('template'));
		}
		return $this->template;
	}
}

?>