<?php
/**
 * @since 9/21/07
 * @package segue.ui1
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GeneralControlsSiteVisitor.abstract.php,v 1.1 2007/09/21 19:59:28 adamfranco Exp $
 */ 

/**
 * <##>
 * 
 * @since 9/21/07
 * @package segue.ui1
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: GeneralControlsSiteVisitor.abstract.php,v 1.1 2007/09/21 19:59:28 adamfranco Exp $
 */
abstract class GeneralControlsSiteVisitor {
	
	/**
	 * @var boolean $reorderJsPrinted;  
	 * @access private
	 * @since 9/20/07
	 */
	private $reorderJsPrinted = false;
	
	/**
	 * @var string $module;  
	 * @access private
	 * @since 9/21/07
	 */
	protected $module;
	
	/**
	 * @var string $action;  
	 * @access private
	 * @since 9/21/07
	 */
	protected $action;
		
	/**
	 * Print the Reordering Javascript to the header
	 * 
	 * @return void
	 * @access public
	 * @since 9/21/07
	 */
	public function printReorderJS () {
		if (!$this->reorderJsPrinted) {
			$js = <<<END
		<script type='text/javascript'>
		// <![CDATA[
		
			/**
			 * Show all of the reorder forms for the children of an organizer.
			 * 
			 * @param string organizerId
			 * @return void
			 * @access public
			 * @since 9/20/07
			 */
			function showReorder (organizerId) {
				var links = document.getElementsByClassName('reorder_link_' + organizerId);
				var forms = document.getElementsByClassName('reorder_form_' + organizerId);
				
				for (var i = 0; i < links.length; i++) {
					links[i].style.display = 'none';
				}
				
				for (var i = 0; i < forms.length; i++) {
					forms[i].style.display = 'block';
				}
			}
			
			/**
			 * Hide all of the reorder forms for the children of an organizer.
			 * 
			 * @param string organizerId
			 * @return void
			 * @access public
			 * @since 9/20/07
			 */
			function hideReorder (organizerId) {
				var links = document.getElementsByClassName('reorder_link_' + organizerId);
				var forms = document.getElementsByClassName('reorder_form_' + organizerId);
				
				for (var i = 0; i < links.length; i++) {
					links[i].style.display = 'inline';
				}
				
				for (var i = 0; i < forms.length; i++) {
					forms[i].style.display = 'none';
				}
			}
		
		// ]]>
		</script>
		
END;
			$harmoni = Harmoni::instance();
			$output = $harmoni->getOutputHandler();
			$output->setHead($output->getHead().$js);
		
		}
	}
	
	/**
	 * Print the reorder controls
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 5/7/07
	 */
	function printReorder ( $siteComponent ) {
		$this->printReorderLink($siteComponent);
		$this->printReorderForm($siteComponent);
	}
	
	/**
	 * Print the reorder link
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/20/07
	 */
	public function printReorderLink (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		$parent = $siteComponent->getParentComponent();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$parent->getQualifierId()))
		{			
			print "\n\t\t\t\t\t<a href='#' class='reorder_link_".$parent->getId()."' onclick=\"";
			print 	"showReorder('".$parent->getId()."'); ";
			print 	"return false; ";
			print	"\">";
			print _("reorder");
			print "</a>";
		}
	}
	
	/**
	 * Print the reorder form
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 9/20/07
	 */
	public function printReorderForm (SiteComponent $siteComponent) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$harmoni = Harmoni::instance();
		
		$parent = $siteComponent->getParentComponent();
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$parent->getQualifierId()))
		{
		
			$url = 	$harmoni->request->quickURL($this->module, 'reorder', array(
						'returnNode' => RequestContext::value('node'),
						'returnAction' => $this->action
						));
			
			$harmoni->request->startNamespace('reorder');
			
			$organizer = $siteComponent->getParentComponent();
			$myCell = $organizer->getCellForSubcomponent($siteComponent);
			
			print "\n\t\t\t\t\t<form class='ui1_controls reorder_form_".$parent->getId()."' action='".$url."' method='post' style='display: none'>";
			print "\n\t<input type='hidden' name='".RequestContext::name('node')."' value='".$siteComponent->getId()."' />";
			
			print "\n\t<select name='".RequestContext::name('position')."' onchange='this.form.submit();'>";
			for ($i = 0; $i < $organizer->getTotalNumberOfCells(); $i++) {
				print "\n\t\t<option value='$i'";
				if ($myCell == $i)
					print " selected='selected'";
				print ">".($i+1)."</option>";
			}
			print "\n\t</select>";
			print "\n\t<input type='button' onclick=\"";
			print 	"hideReorder('".$parent->getId()."'); ";
			print "\" value='"._("Cancel")."'/>";
			print "\n\t\t\t\t\t</form>";
			
			$harmoni->request->endNamespace();
		}
	}
}

?>