<?

/**
 * Collects all of the ids and names of all of the blocks below the initially visited 
 * node.
 * 
 * 
 * @since 9/23/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNodeDataVisitor.class.php,v 1.2 2008/04/11 20:38:59 davidfouhey Exp $
 */

class SiteNodeDataVisitor
	 implements SiteVisitor{

	/**
	 * Set up the  visitor class
	 * Basically, just initialize the instance variable _currentTarget
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function siteNodeDataVisitor(){
		/* because of the visiting mechanism, we can't do a straight
		recursion with returns bubbling up So instead, we'll just
		set a current target variable so that when visiting methods
		get called by various nodes, the visiting method knows
		where to write its data*/
		$this->_currentTarget = array();
	}
	
	/**
	 * Return the gathered data
	 *
	 * Return array description: 
	 *
	 * Let $node be the returned array. 
	 * $node[0] contains an array with the name and id of the top node (in that order).
	 * $node[1..(sizeof($node)-1)] contain all of the nodes below $node
	 * stored in the same format that $node is.
	 * Thus, $node[1][0] is the name and id of the first node beneath $node. 
	 * $node[2][1][0] is the name and id of the first node beneath the second node
	 * beneath $node.
	 * 
	 * @return An array of the form
	 * @access public
	 * @since 9/23/08
	 */

	public function getNodeData() {
		return $this->_currentTarget;
	}	

	/**
	 * Get the data for a node
	 * @param node A node
	 * @return An array a, a[0] is the name of node, a[1] is the id
	 *         of node
	 * @access public
	 * @since 9/23/08
	 */
	public function getDataForNode($node){
		return array($node->getDisplayName(),$node->getId());

	}

	/**
	 * Visit a block 
	 * @param block The visited block
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitBlock(BlockSiteComponent $block){
		/* It would be silly for us to be able to point the tag 
		block at other blocks: the user can already see what tags
		are on that block, so there's no added benefit */
		return;
	}	

	/**
	 * Visit a block in a menu
	 * @param block The visited block in a menu
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitBlockInMenu( BlockSiteComponent $siteComponent ) {
		$this->visitBlock($siteComponent);

	}

	/**
	 * Visit a nav block
	 * @param block The visited nav block
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitNavBlock(NavBlockSiteComponent $navBlock){
		/* Basically, keep a copy of the pointer to our old array
		in $oldTarget, then make $this->_currentTarget a new array
		and add to that for all the children, and then append the 
		gathered data (in $this->_currentTarget) to $oldTarget, 
		effectively adding them to the target that we started out with.
		Then, we'll revert $this->_currentTarget to $oldTarget */  
		$oldTarget = $this->_currentTarget;
		$this->_currentTarget = array();
		$this->_currentTarget[] = $this->getDataForNode($navBlock);
		
		$childOrganizer = $navBlock->getOrganizer();
		$childOrganizer->acceptVisitor($this);
		$nestedMenuOrganizer = $navBlock->getNestedMenuOrganizer();
		if(!is_null($nestedMenuOrganizer)){
			$nestedMenuOrganizer->acceptVisitor($this);
		}
		$oldTarget[] = $this->_currentTarget;
		$this->_currentTarget = $oldTarget;

	}
	/**
	 * Visit a site nav block
	 * @param block The visited site nav block
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitSiteNavBlock(SiteNavBlockSiteComponent $siteNavBlock){
		$this->_currentTarget[] = $this->getDataForNode($siteNavBlock);
		$childOrganizer = $siteNavBlock->getOrganizer();
		$childOrganizer->acceptVisitor($this);
	}

	/**
	 * Visit an organizer ( a generic function for all sorts of organizers).
	 * Contrary to what one might suspect from the name, and what we're trying
	 * to do (i.e. build a tree), it appears that we really don't have to count the organizer as 
	 * a node itself with children. If this turns out to be false, then we'll have to rethink
	 * how we collect data from the site tree, because if we include these as nodes, the
	 * tree becomes unbearably messy 
	 * @param block The visited organizer
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	private function visitOrganizer( OrganizerSiteComponent $organizer){
		$numCells = $organizer->getTotalNumberOfCells();
		for($i = 0; $i < $numCells; $i++){
			$child = $organizer->getSubcomponentForCell($i);
			if(is_object($child)){
				$child->acceptVisitor($this);
			}
		}
	}

	/**
	 * Visit a fixed organizer
	 * @param block The visited fixed organizer
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitFixedOrganizer( FixedOrganizerSiteComponent $organizer){
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a nav organizer
	 * @param block The visited nav organizer
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitNavOrganizer( NavOrganizerSiteComponent $organizer){
		return $this->visitOrganizer($organizer);

	}

       /**
	 * Visit a flow organizer
	 * @param block The visited flow organizer
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
                return $this->visitOrganizer($organizer);
        }
       /**
	 * Visit a menu organizer
	 * @param block The visited menu organizer
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */
        public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {
                return $this->visitOrganizer($organizer);
        }

}
?>	
