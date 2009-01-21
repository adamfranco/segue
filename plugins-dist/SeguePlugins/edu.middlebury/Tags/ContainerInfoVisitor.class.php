<?php
/**
 * @since 4/10/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ContainerInfoVisitor.class.php,v 1.2 2008/04/11 20:38:59 davidfouhey Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * This visitor will return all of the blocks above the site component passed to it and their ids.
 * This visitor class is no longer used by the tag plugin. It has been replaced by the 
 * UmbrellaVisitor class, which uses the SiteNodeDataVisitor class.
 * 
 * @since 4/10/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ContainerInfoVisitor.class.php,v 1.2 2008/04/11 20:38:59 adamfranco Exp $
 */
class ContainerInfoVisitor
	implements SiteVisitor
{

	function ContainerInfoVisitor(){
		$this->_names = array();
		$this->_ids = array();	
	}

	public function addData($node,$prefix=""){
		$this->_names[] = $prefix.$node->getDisplayName();
		$this->_ids[] = $node->getId();
	}	

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/1/08
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$this->addData($siteComponent,"b");
		$parent = $siteComponent->getParentComponent();
		return $parent->acceptVisitor($this);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/1/08
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		return $this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		return $this->visitBlock($siteComponent);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */

	public function visitSiteNavBlock( SiteNavBlockSiteComponent $siteComponent ) {
		$this->addData($siteComponent,"SiteNavBlock");
		$retArray = array();
		$retArray[] = array_reverse($this->_names);
		$retArray[] = array_reverse($this->_ids);
		return $retArray;
	}


        /**
         * Visit a fixed organizer
         *
         * @param object FixedOrganizerSiteComponent $organizer
         * @return boolean
         * @access public
         * @since 8/1/08
         */
	 public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {    
                $parent = $siteComponent->getParentComponent();
                return $parent->acceptVisitor($this);
        }

        /**
         * Visit a fixed organizer
         *
         * @param object FixedOrganizerSiteComponent $organizer
         * @return boolean
         * @access public
         * @since 8/1/08
         */
        public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
                $parent = $siteComponent->getParentComponent();
                return $parent->acceptVisitor($this);
        }

        /**
         * Visit a flow organizer
         *
         * @param object FlowOrganizerSiteComponent
         * @return boolean
         * @access public
         * @since 8/1/08
         */
        public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
                $parent = $siteComponent->getParentComponent();
                return $parent->acceptVisitor($this);
        }

        /**
         * Visit a menu organizer
         *
         * @param object MenuOrganizerSiteComponent
         * @return boolean
         * @access public
         * @since 8/1/08
         */
        public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
                $parent = $siteComponent->getParentComponent();
                return $parent->acceptVisitor($this);
        }
	



}






?>
