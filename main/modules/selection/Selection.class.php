<?php
/**
 * @since 7/31/08
 * @package segue.basket
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * The Segue selection is based on the Polyphony Basket, but is customized to deal
 * with Site-nodes rather than generic Assets.
 * 
 * @since 7/31/08
 * @package segue.basket
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_Selection
	extends OrderedSet
{
		
/*********************************************************
 * Class Methods - Instance-Creation/Singlton
 *********************************************************/
 	
	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset($_SESSION['__selection'])) {
			$_SESSION['__selection'] = new Segue_Selection();
		}
		
		return $_SESSION['__selection'];
	}

/*********************************************************
 * Instance Methods
 *********************************************************/	

	/**
	 * The constructor.
	 * @access public
	 * @return void
	 **/
	private function __construct() {
		$idManager = Services::getService("Id");
		$this->OrderedSet($idManager->getId("__selection"));	
	}
	
	/**
	 * Answer the link to add a particular SiteComponent to the selection
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return string XHTML
	 * @access public
	 * @since 7/31/08
	 */
	public function getAddLink ( SiteComponent $siteComponent ) {
		$this->addHeadJavascript();
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("selection");
		ob_start();
		
		print "<a ";
		print " style='cursor: pointer;'";
		print " onclick=\"Segue_Selection.instance().addComponent({";
		print	"id: '".$siteComponent->getId()."', ";
		print 	"type: '".$siteComponent->getComponentClass()."', ";
		print	"displayName: '"
			.addslashes(str_replace('"', '&quot', 
				preg_replace('/\s+/', ' ',
					strip_tags($siteComponent->getDisplayName()))))."', ";
// 		print 	"description: '".$siteComponent->getDescription()."'";
		print "});\"";
		print ">"._('+ Selection');
		print "</a>";
		
		$harmoni->request->endNamespace();				
		return ob_get_clean();
	}
	
	/**
	 * Add the javascript to the document head
	 * 
	 * @return void
	 * @access protected
	 * @since 7/31/08
	 */
	protected function addHeadJavascript () {
		$harmoni = Harmoni::instance();
		if (!$harmoni->getAttachedData('Segue_Selection_headJsAdded')) {
			$harmoni = Harmoni::instance();
			
			ob_start();
			
			print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/FixedPanel.js'></script>";
			print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/Selection.css' />";
			print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/Selection.js'></script>";
			
			print "\n\t\t<script type='text/javascript'>";
			print "\n\t\t// <![CDATA[ ";
			
			
			// Load up the JS Selection with info from our session.
			$this->reset();
			if ($this->hasNext()) {
				$director = SiteDispatcher::getSiteDirector();
				
				while ($this->hasNext()) {	
					$id = $this->next();
					$siteComponent = $director->getSiteComponentById($id);
					
					try {
						if ($authZ->isUserAuthorized(
							$idManager->getId("edu.middlebury.authorization.view"), $id))
						{
							print "\n\t\t\tSegue_Selection.instance().loadComponent({";
							print	"id: '".$siteComponent->getId()."', ";
							print 	"type: '".$siteComponent->getComponentClass()."', ";
							print	"displayName: '"
								.addslashes(str_replace('"', '&quot', 
									preg_replace('/\s+/', ' ',
										strip_tags($siteComponent->getDisplayName()))))."', ";
					// 		print 	"description: '".$siteComponent->getDescription()."'";
							print "});'";
						}
					} catch (UnknownIdException $e) {
						// Let assets out of the purvue of our authorization manager slide.
					}
				}
				$this->reset();
			}
			
			print "\n\t\t// ]]> ";
			print "\n\t\t</script>";
			
			$outputHandler = $harmoni->getOutputHandler();
			$outputHandler->setHead(
				$outputHandler->getHead().ob_get_clean());
			
			$harmoni->attachData('Segue_Selection_headJsAdded', true);
		}
	}
}

?>