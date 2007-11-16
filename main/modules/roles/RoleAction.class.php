<?php
/**
 * @since 11/15/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RoleAction.class.php,v 1.1 2007/11/16 20:25:02 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * An abstract class to provide common methods
 * 
 * @since 11/15/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RoleAction.class.php,v 1.1 2007/11/16 20:25:02 adamfranco Exp $
 */
abstract class RoleAction
	extends MainWindowAction
{
		
		/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSiteId () {
		$idManager = Services::getService("Id");
		return $idManager->getId($this->getSite()->getId());
	}
	
	/**
	 * Answer the site id.
	 * 
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSite () {
		$siteComponent = $this->getSiteComponent();
		return $siteComponent->getDirector()->getRootSiteComponent($siteComponent->getId());
	}
	
	/**
	 * Answer the qualifier Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getQualifierId () {
		$component = $this->getSiteComponent();
		return $component->getQualifierId();
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponent () {
		$idManager = Services::getService("Id");
		return $this->getSiteComponentForId(
			$idManager->getId(RequestContext::value("node")));
	}
	
	/**
	 * Answer the site component for a given Id
	 * 
	 * @param object Id $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 5/8/07
	 */
	protected function getSiteComponentForId ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id->getIdString());
	}
	
	/**
	 * Answer the site component for a given Id string
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access protected
	 * @since 6/4/07
	 */
	protected function getSiteComponentForIdString ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id);
	}
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access protected
	 * @since 4/14/06
	 */
	protected function getSiteDirector () {
			if (!isset($this->_director)) {
			/*********************************************************
			 * XML Version
			 *********************************************************/
	// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
	// 		
	// 		$this->document = new DOMIT_Document();
	// 		$this->document->setNamespaceAwareness(true);
	// 		$success = $this->document->loadXML($this->filename);
	// 
	// 		if ($success !== true) {
	// 			throwError(new Error("DOMIT error: ".$this->document->getErrorCode().
	// 				"<br/>\t meaning: ".$this->document->getErrorString()."<br/>", "SiteDisplay"));
	// 		}
	// 
	// 		$director = new XmlSiteDirector($this->document);
			
			
			/*********************************************************
			 * Asset version
			 *********************************************************/
			$repositoryManager = Services::getService('Repository');
			$idManager = Services::getService('Id');
			
			$this->_director = new AssetSiteDirector(
				$repositoryManager->getRepository(
					$idManager->getId('edu.middlebury.segue.sites_repository')));
		}
		
		return $this->_director;
	}
	
}

?>