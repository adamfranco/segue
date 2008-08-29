<?php
/**
 * @since 3/14/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: map.act.php,v 1.11 2008/04/10 21:06:30 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteMapSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsHeaderFooterSiteVisitor.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");

/**
 * action for displaying site maps
 * 
 * @since 3/14/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: map.act.php,v 1.11 2008/04/10 21:06:30 adamfranco Exp $
 */
class mapAction 
	extends MainWindowAction
	implements SiteVisitor
{

	/**
	 * @var array $_siteNodes; nodes in the map 
	 * @access private
	 * @since 3/14/08
	 */
	var $_siteNodes = array();
	
	/**
	 * @var int $depth; current depth in site hierarchy 
	 * @access private
	 * @since 3/17/08
	 */
	private $depth = 0;
	
	/**
	 * @var boolean $inHeaderOrFooter; True if we are currently descended into a header or footer. 
	 * @access private
	 * @since 4/10/08
	 */
	private $inHeaderOrFooter = false;

		
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
	 * Answer a message in the case of no authorization
	 * 
	 * @return string
	 * @access public
	 * @since 3/14/08
	 */
	public function getUnauthorizedMessage () {
		$message = _("You are not authorized to view the requested node.");
		$message .= "\n<br/>";
		$authNMgr = Services::getService("AuthN");
		if (!$authNMgr->isUserAuthenticatedWithAnyType())
			$message .= _("Please log in or use your browser's 'Back' Button.");
		else
			$message .= _("Please use your browser's 'Back' Button.");
		
		return $message;
	}
	
		/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 3/14/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
				
		ob_start();
		
		print "
<script type='text/javascript'>
// <![CDATA[

	function toggleSiteMapChildren(node) {
		for (var i = 0; i < node.parentNode.childNodes.length; i++) {
			var child = node.parentNode.childNodes[i];
			if (child.className == 'children') {
				if (child.style.display != 'none') {
					child.style.display = 'none';
					node.innerHTML = '+';
				} else {
					child.style.display = 'block';
					node.innerHTML = '-';
				}
			}
		}
	}
	
	function expandAllSiteMapChildren(node) {
		var divElements = node.getElementsByTagName('div');
		for (var i = 0; i < divElements.length; i++) {
			var div = divElements[i];
			if (div.className == 'children') {
				div.style.display = 'block';
			}
			
			if (div.className == 'expand') {
				div.innerHTML = '-';
			}
		}
	}
	
	function collapseAllSiteMapChildren(node) {
		var divElements = node.getElementsByTagName('div');
		for (var i = 0; i < divElements.length; i++) {
			var div = divElements[i];
			if (div.className == 'children') {
				div.style.display = 'none';
			}
			
			if (div.className == 'expand') {
				div.innerHTML = '+';
			}
		}
	}


// ]]>
</script>
		
		";
		
		print "\n<div class='siteMap'>";
		
		print "\n\t<button onclick='expandAllSiteMapChildren(document.get_element_by_id(\"site_children\"));'>"._("Expand All")."</button>";
		print "\n\t<button onclick='collapseAllSiteMapChildren(document.get_element_by_id(\"site_children\"));'>"._("Collapse All")."</button>";
						
		$rootSiteComponent = SiteDispatcher::getCurrentRootNode();
		
		$this->isHeaderFooterVisitor = new isHeaderFooterSiteVisitor();
		
	//	$currentNode = $this->getNodeId();
		//printpre ($currentNode);				
				
		$rootSiteComponent->acceptVisitor($this);
		print "\n</div>";
		
		$actionRows->add ( new Block(ob_get_clean(), STANDARD_BLOCK));
		
	}
	
	/**
	 * answer a string of tabs
	 * 
	 * @return string
	 * @access protected
	 * @since 3/17/08
	 */
	protected function getTabs () {
		$tabs = "\n";
		for ($i=0; $i < $this->depth; $i++)
			$tabs .= "\t";
		return $tabs;
	}
	
	/**
	 * Print Node Start html
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access protected
	 * @since 3/17/08
	 */
	protected function printNodeStart (SiteComponent $siteComponent) {
		print $this->getTabs();		
		print "<div class='node'>";
	}
	
	/**
	 * Print Node End html
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access protected
	 * @since 3/17/08
	 */
	protected function printNodeEnd (SiteComponent $siteComponent) {
		print $this->getTabs();
		print "</div>";
	}
	
	/**
	 * Print Node info html
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access protected
	 * @since 3/17/08
	 */
	protected function printNodeInfo (SiteComponent $siteComponent) {
		$harmoni = Harmoni::instance();
		
		print $this->getTabs()."\t";
		if ($siteComponent->getId() == SiteDispatcher::getCurrentNodeId()) {
			print "<div class='info current'>";
		} else {
			print "<div class='info'>";
		}		
		
		print $this->getTabs()."\t\t";
		print "<div class='title'>";
		$nodeUrl = SiteDispatcher::quickURL('view', 'html', array('node' => $siteComponent->getId()));
		print "<a href='".$nodeUrl."' ";
		print ' onclick="';
		print "if (window.opener) { ";
		print 		"window.opener.location = this.href; ";
		print 		"return false; ";
		print '}" ';
		print " title='"._("View this node")."'>";
		print $siteComponent->getDisplayName();
		print "</a>";
		print "</div>";
		
		$nodeDescription = HtmlString::withValue($siteComponent->getDescription());
		$nodeDescription->stripTagsAndTrim(5);
		
		print $this->getTabs()."\t\t";
		print "<div class='description'>".$nodeDescription->stripTagsAndTrim(20)."</div>";

		print $this->getTabs()."\t";
		print "</div>";
	}

	/*********************************************************
	 * Vistor methods
	 *********************************************************/

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {		
		$harmoni = Harmoni::instance();
		
		// check to see if user is authorized to view block
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view"), 
			$idManager->getId($siteComponent->getId())))
		{
			return;
		}
				
		$this->printNodeStart($siteComponent);
		print $this->getTabs()."\t";
		print "<div class='expandSpacer'>";		
		print "&nbsp;";
		print "</div>";
		$this->printNodeInfo($siteComponent);
		$this->printNodeEnd($siteComponent);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		$this->printNodeStart($siteComponent);
		
		print $this->getTabs()."\t";
		print "<div class='expand' onclick='toggleSiteMapChildren(this);'>";		
		print "-";
		print "</div>";
		
		$this->printNodeInfo($siteComponent);
		
		print $this->getTabs()."\t";
		print "<div class='children'>";
		$this->depth++;
		$this->depth++;			
				
		// sub-menu children
		if (!is_null($siteComponent->getNestedMenuOrganizer())) {		
			$siteComponent->getNestedMenuOrganizer()->acceptVisitor($this);		
		// plugin children
		} else {			
			$organizer = $siteComponent->getOrganizer();
			$organizer->acceptVisitor($this);				
		}
		
		$this->depth--;
		$this->depth--;			
		print $this->getTabs()."\t";
		print "</div>";				
		$this->printNodeEnd($siteComponent);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		$actionRows = $this->getActionRows();
		$actionRows->add(new Heading(_("Site map for: ").$siteComponent->getDisplayName(), 2));
		
		$this->printNodeStart($siteComponent);
		
		
		$this->printNodeInfo($siteComponent);
		
		//children
		print $this->getTabs()."\t";
		print "<div class='children' id='site_children'>";
		$this->depth++;
		$this->depth++;
		$organizer = $siteComponent->getOrganizer();
		$organizer->acceptVisitor($this);
		$this->depth--;
		$this->depth--;
		
		print $this->getTabs()."\t";
		print "</div>";
		
		
		$this->printNodeEnd($siteComponent);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		
		$numCells = $siteComponent->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $siteComponent->getSubcomponentForCell($i);
			if (is_object($child)) {
				$isHeaderOrFooter = false;
				if (!$this->inHeaderOrFooter 
					&& $child->acceptVisitor($this->isHeaderFooterVisitor)) 
				{
					$isHeaderOrFooter = true;
					print $this->getTabs();
					print "<div class='header_area'>"._("The following are header or footer items:");
					$this->depth++;
					$this->inHeaderOrFooter = true;
				}
								
				$child->acceptVisitor($this);
				
				if ($isHeaderOrFooter) {
					// Spacer
					print $this->getTabs();
					print "<div class='header_spacer'>&nbsp;</div>";					
					$this->depth--;
					print $this->getTabs();
					print "</div>";
					$this->inHeaderOrFooter = false;
				}
					
			}
		}
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		foreach ($siteComponent->getSortedSubcomponents() as $child) {
			$child->acceptVisitor($this);
		}
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		$this->visitFlowOrganizer($siteComponent);
	}	
			
	
}

?>