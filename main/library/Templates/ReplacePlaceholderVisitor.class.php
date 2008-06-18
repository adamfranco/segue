<?php
/**
 * @since 6/11/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR.'/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php');

/**
 * This visitor replaces all occurances of placeholders in the site with supplied
 * strings.
 * 
 * @since 6/11/08
 * @package segue.templates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Templates_ReplacePlaceholderVisitor
	implements SiteVisitor
{
		
	/**
	 * Constructor
	 * 
	 * @param string $displayName
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 6/11/08
	 */
	public function __construct ($displayName, $description) {
		$this->displayName = $displayName;
		$this->description = $description;
	}
	
	/**
	 * Replace the placeholders in a string
	 * 
	 * @param string $inputString
	 * @return string
	 * @access protected
	 * @since 6/11/08
	 */
	protected function replacePlaceholders ($inputString) {
		$string = str_replace('#SITE_NAME#', $this->displayName, $inputString);
		$string = str_replace('#SITE_DESCRIPTION#', $this->description, $string);
		return $string;
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$orig = $siteComponent->getDisplayName();
		$new = $this->replacePlaceholders($orig);
		if ($orig != $new)
			$siteComponent->updateDisplayName($new);
		
// 		$orig = $siteComponent->getDescription();
// 		$new = $this->replacePlaceholders($orig);
// 		if ($orig != $new)
// 			$siteComponent->updateDescription($new);
			
		$asset = $siteComponent->getAsset();
		$orig = $asset->getContent()->asString();
		$new = $this->replacePlaceholders($orig);
		if ($orig != $new)
			$asset->updateContent(Blob::withValue($new));
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$orig = $siteComponent->getDisplayName();
		$new = $this->replacePlaceholders($orig);
		if ($orig != $new)
			$siteComponent->updateDisplayName($new);
		
		$orig = $siteComponent->getDescription();
		$new = $this->replacePlaceholders($orig);
		if ($orig != $new)
			$siteComponent->updateDescription($new);
		
		// Traverse down
		$childOrganizer = $siteComponent->getOrganizer();
		$childOrganizer->acceptVisitor($this);
			
		$nestedMenuOrganizer = $siteComponent->getNestedMenuOrganizer();
		if (!is_null($nestedMenuOrganizer)) {
			$nestedMenuOrganizer->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$this->visitNavBlock($siteComponent);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$child->acceptVisitor($this);
			}
		}
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		$this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			$child->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/11/08
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		$this->visitFlowOrganizer($siteComponent);
	}
	
}

?>