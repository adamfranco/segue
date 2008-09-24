<?php
/**
 * @since 9/23/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UmbrellaVisitor.class.php,v 1.2 2008/04/11 20:38:59 davidfouhey Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(dirname(__FILE__)."/SiteNodeDataVisitor.class.php");

/**
 * This visitor will find the first site nav block above the node passed to it and
 * will then use a SiteNodeDataVisitor to collect data about the ids and names of all
 * of the sub-blocks of the site.
 * 
 * @since 9/23/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UmbrellaVisitor.class.php,v 1.2 2008/04/11 20:38:59 davidfouhey Exp $
 */
class UmbrellaVisitor
	implements SiteVisitor
{
	/**
	 * Set up the umbrella visitor class
	 * Basically, just initialize the instance variable _nodeData
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	function UmbrellaVisitor(){
		$this->_nodeData = array();
	}

	/**
	 * Return the gathered data on the nodes.
	 * 
	 * @return An array in 
	 * @access public
	 * @since 9/23/08
	 */
	public function getNodeData(){
		return $this->_nodeData;
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
		$parent = $siteComponent->getParentComponent();
		return $parent->acceptVisitor($this);
	}

	/**
	 *
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$parent = $siteComponent->getParentComponent();
		return $parent->acceptVisitor($this);
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
		$visitor = new SiteNodeDataVisitor;
		$siteComponent->acceptVisitor($visitor);
		$this->_nodeData = $visitor->getNodeData();	
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



