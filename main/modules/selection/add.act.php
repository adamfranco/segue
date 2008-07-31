<?php
/**
 * @since 7/31/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * Add an item to the selection and return the new selection contents as XML
 * 
 * @since 7/31/08
 * @package segue.selection
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class addAction
	extends XmlAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/31/08
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		return TRUE;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	public function execute () {
		
		try {
			if (!RequestContext::value('id'))
				throw new InvalidArgumentException("Id is expected.");
			
			$selection = Segue_Selection::instance();
			$director = SiteDispatcher::getSiteDirector();
			$component = $director->getSiteComponentById(RequestContext::value('id'));
			$selection->addSiteComponent($component);
			
			$this->start();
			
			$selection->reset();
			while($selection->hasNext()) {
				$siteComponent = $selection->nextSiteComponent();
				print "\n\t<siteComponent type='".$siteComponent->getComponentClass()."' ";
				print "id='".$siteComponent->getId()."' ";
				print "displayName='".addslashes(str_replace('"', '&quot', 
				preg_replace('/\s+/', ' ',
					strip_tags($siteComponent->getDisplayName()))))."' ";
				print "/>";
			}
			
			$this->end();
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e);
			$this->error($e->getMessage(), get_class($e));
		}
		
	}
}

?>