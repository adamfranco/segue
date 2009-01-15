<?php
/**
 * @since 2/14/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_wiki_component.act.php,v 1.1 2008/02/14 21:15:46 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__).'/process_add_wiki_component.act.php');

/**
 * Action for adding components from wiki-links
 * 
 * @since 2/14/08
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add_wiki_component.act.php,v 1.1 2008/02/14 21:15:46 adamfranco Exp $
 */
class add_wiki_componentAction
	extends MainWindowAction
{
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 1/15/09
	 */
	public function __construct () {
		$this->_classNames = array(
			'Block' => _('Content Block'),
			'NavBlock' => _('Page'),
			'NavSection' => _('Section'),
			'SiteNavBlock' => _('Site'),
			'MenuOrganizer' => _('Pages Container'),
			'FlowOrganizer' => _('Content Container'),
			'FixedOrganizer' => _('Layout Container'),
			'SubMenu_multipart' => _('Section'),
			'SidebarSubMenu_multipart' => _('Section with Sidebar'),
			'ContentPage_multipart' => _('Page'),
			'SidebarContentPage_multipart' => _('Page with Sidebar')
			
		);
	}
	
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/14/08
	 */
	public function isAuthorizedToExecute () {
		// Allow anyone to see this page, we'll just hide the form from unauthorized people
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return mixed
	 * @access public
	 * @since 2/14/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		ob_start();
		print "<p>";
		if (isset($_SERVER['HTTP_REFERER']))
			print "<a href='".strip_tags($_SERVER['HTTP_REFERER'])."'>&laquo;"._("Go Back")."</a>";
		print "</p>";
		
		print "\n\t<p>".str_replace('%1', strip_tags(RequestContext::value('title')), _("<strong><em>%1</em></strong> does not yet exist.")).'</p>';
		
		
		$harmoni = Harmoni::instance();
		$refNode = $this->getRefNode();
		
		// If the user isn't authorized to add-children here, they aren't authorized
		// above us, so don't show the form.
		$authZ = Services::getService('AuthZ');
		$idManager = Services::getService("Id");
		
		try {
			$organizer = process_add_wiki_componentAction::getOrganizerForComponentType($refNode, 
				new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'));
			$canAddContent = $authZ->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.add_children"),
								$organizer->getQualifierId());
		} catch (OperationFailedException $e) {
			$canAddContent = false;
		}
		
		try {
			$organizer = process_add_wiki_componentAction::getOrganizerForComponentType($refNode, 
				new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart'));
			$canAddPages = $authZ->isUserAuthorized(
								$idManager->getId("edu.middlebury.authorization.add_children"),
								$organizer->getQualifierId());
		} catch (OperationFailedException $e) {
			$canAddPages = false;
		}
		
		if ($canAddContent || $canAddPages) {
			print "\n<form action='".SiteDispatcher::quickURL('ui2', 'process_add_wiki_component')."' method='post'>";
			print "\n\t<input type='hidden' name='".RequestContext::name('displayName')."' value='".strip_tags(RequestContext::value('title'))."'/>";
			print "\n\t<input type='hidden' name='".RequestContext::name('refNode')."' value='".$refNode->getId()."'/>";
			
			
			print "\n\t<p>";
			print _("Create as new ");
			
			print "\n\t\t<select class='ui2_page_select' name='".RequestContext::name('componentType')."'>";
			$allowed = array();
			if ($canAddPages) {
				$allowed[] = _("Pages and Sections");
				$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart');
				$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart');
				$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart');
				$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart');
			}
			
			if ($canAddContent) {
				$allowed[] = _("Content Blocks");
				$pluginManager = Services::getService("PluginManager");
				$allowed = array_merge($allowed, $pluginManager->getEnabledPlugins());
			}
			
			$inCat = false;
			foreach ($allowed as $type) {
				if (is_string($type)) {
					if ($inCat) {
						print "\n\t\t\t</optgroup>";
					}
					$inCat = true;
					print "\n\t\t\t<optgroup label='$type'>";
				} else {
					$this->printTypeOption($type);
				}
			}
			if ($inCat)
				print "\n\t\t\t</optgroup>";
			print "\n\t\t</select> ";
			
			print "\n\t\t &nbsp; <input type='submit' value='Go &raquo;'/>";
			print "\n\t</p>";
			
			
			
			print "\n</form>";
		} else {
			print "<p>"._("You are not authorized to create this new page.")."</p>";
		}
		
		
		$actionRows->add(
			new Block(ob_get_clean(), STANDARD_BLOCK), 
			"100%", 
			null, 
			CENTER, 
			CENTER);
	}
	
	/**
	 * Answer the reference node
	 * 
	 * @return object BlockSiteComponent
	 * @access protected
	 * @since 1/15/09
	 */
	protected function getRefNode () {
		if (!isset($this->refNode)) {
			$director = SiteDispatcher::getSiteDirector();
			$this->refNode = $director->getSiteComponentById(RequestContext::value('refNode'));
		}
		return $this->refNode;
	}
	
	/**
	 * print an option tag
	 * 
	 * @param object Type $type
	 * @return void
	 * @access private
	 * @since 12/14/07
	 */
	private function printTypeOption (Type $type) {
		print "\n\t\t\t<option value='".$type->asString()."'>";
		if (isset($this->_classNames[$type->getKeyword()]))
			print $this->_classNames[$type->getKeyword()];
		else {
			try {
				$pluginManager = Services::getService("PluginManager");
				$class = $pluginManager->getPluginClass($type);
				print call_user_func(array($class, 'getPluginDisplayName'));
			} catch (UnknownIdException $e) {
				print $type->getKeyword();
			}
		}
		print "</option>";
	}
	
}

?>