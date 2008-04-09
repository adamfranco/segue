<?php
/**
 * @since 4/8/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueTagsAction.abstract.php,v 1.1 2008/04/09 21:27:23 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/html.act.php");

/**
 * This abstract class defines methods related to getting tags on nodes in Segue
 * 
 * @since 4/8/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueTagsAction.abstract.php,v 1.1 2008/04/09 21:27:23 achapin Exp $
 */
abstract class SegueTagsAction
	extends htmlAction
{

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/07/06
	 */
	function execute () {
		$mainScreen = new Container(new YLayout, BLOCK, BACKGROUND_BLOCK);	
		
		// implemented in parent class htmlAction
		$allWrapper = $this->addHeaderControls($mainScreen);
		
		// implemented by this class
		$this->addSiteHeader($mainScreen);
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('polyphony-tags');
		
		$this->addTagsMenu($mainScreen);
	
		// implemented by child classes
		$this->getResult($mainScreen);
		
		$harmoni->request->endNamespace();
		// 	
		//implemented in parent class htmlAction
		$this->addFooterControls($mainScreen);
		$this->mainScreen = $mainScreen;
		return $allWrapper;
	}

	/**
	 * Add the site header gui components
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	public function addSiteHeader (Component $mainScreen) {
		if ($this->isAuthorizedToExecute()) {
							
			// :: Site ::
			$rootSiteComponent = SiteDispatcher::getCurrentRootNode();

			$visitor = new TagModeSiteVisitor;
			$this->siteGuiComponent = $rootSiteComponent->acceptVisitor($visitor);
			$mainScreen->add($this->siteGuiComponent);
		} else {
			// Replace the title
			$title = "\n\t\t<title>"._("Unauthorized")."</title>";
			$outputHandler->setHead(
				preg_replace("/<title>[^<]*<\/title>/", $title, $outputHandler->getHead()));			
		
			$mainScreen->add(new Block($this->getUnauthorizedMessage(), EMPHASIZED_BLOCK),
				"100%", null, CENTER, TOP);
		}
	}
	
	/**
	 * Answer the title of this result set
	 * 
	 * @return string
	 * @access public
	 * @since 4/8/08
	 */
	abstract public function getResultTitle ();
	
	/**
	 * Answer a menu for the tagging system
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	public function addTagsMenu (Component $mainScreen) {
		$harmoni = Harmoni::instance();
		$tagManager = Services::getService("Tagging");
		ob_start();
		print "\n<table cellpadding='5' cellspacing='0'>";
		print "\n\t<tr>";
		print "\n\t\t<td colspan='3'>".$this->getResultTitle()."\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<td style='border: 1px solid;'>"._("node")."\n\t\t</td>";
		print "\n\t\t<td style='border: 1px solid;'>"._("site")."\n\t\t</td>";
		print "\n\t\t<td style='border: 1px solid;'>"._("segue")."\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";	
		
		// all nodes with tag by you
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("tagged by: ");

		if ($harmoni->getCurrentAction() != 'tags.usernodetag') {
			$url = $harmoni->request->quickURL('tags', 'usernodetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.usernodetag') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}
		
		// all nodes with tag by everone
		if ($harmoni->getCurrentAction() != 'tags.nodetag') {
			$url = $harmoni->request->quickURL('tags', 'nodetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.nodetag') {
			print "<strong>"._("everyone")."</strong>";		
		} else {
			print _("everyone");
		}
		
		print "\n\t\t</td>";		
		
		// tagged with item in site by you
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("tagged by: ");
		if ($harmoni->getCurrentAction() != 'tags.usersitetag') {
			$url = $harmoni->request->quickURL('tags', 'usersitetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.usersitetag') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}
		
			
		// tagged with item in site by everyone
		if ($harmoni->getCurrentAction() != 'tags.sitetag') {
			$url = $harmoni->request->quickURL('tags', 'sitetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.sitetag') {
			print "<strong>"._("everyone")."</strong>";			
		} else {
			print _("everyone");
		}
				
		print "\n\t\t</td>";
		
		// tagged with item in all segue by you
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("tagged by: ");
		
		if ($harmoni->getCurrentAction() != 'tags.userseguetag') {
			$url = $harmoni->request->quickURL('tags', 'userseguetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.userseguetag') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}


		// tagged with item in all segue by everyone
		if ($harmoni->getCurrentAction() != 'tags.seguetag') {
			$url = $harmoni->request->quickURL('tags', 'seguetag', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.seguetag') {
			print "<strong>"._("everyone")."</strong>";			
		} else {
			print _("everyone");
		}
	
		print "\n\t\t</td>";				
		
		print "\n\t</tr>";
		print "\n</table>";	
		
		
		// All tags on item, in site and in all of segue
		print "\n<table cellpadding='5' cellspacing='0'>";
		print "\n\t<tr>";
		print "\n\t\t<td colspan='3'>"._("All tags")."\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";
		print "\n\t\t<td style='border: 1px solid;'>"._("node")."\n\t\t</td>";
		print "\n\t\t<td style='border: 1px solid;'>"._("site")."\n\t\t</td>";
		print "\n\t\t<td style='border: 1px solid;'>"._("segue")."\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n\t<tr>";			
		// all tags on node by you and everyone
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("by: ");
		

		print _("everyone")."";		
		print "\n\t\t</td>";		
		
		// all tags in site by you and everyone
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("by: ")._("you")." | ";
		print _("everyone")."";		
		print "\n\t\t</td>";
		
		// all tags from all segue by you and everyone
		print "\n\t\t<td style='border: 1px solid;'>";
	//	print _("by: ")._("you")." | ";
		
		if ($currentUserIdString = $tagManager->getCurrentUserIdString()) {
			if ($harmoni->getCurrentAction() == 'tags.user' 
				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $currentUserIdString)) 
			{
				print ""._("by you")."";
			} else {
				$url = $harmoni->request->quickURL('tags', 'user', 
					array('agent_id' => $tagManager->getCurrentUserIdString()));
				print "<a href='".$url."'>"._("by you")."</a> | ";
			}
		}
		if ($harmoni->getCurrentAction() == 'tags.all') {
			print _("everyone");
		} else {
			$url = $harmoni->request->quickURL('tags', 'all');
			print "<a href='".$url."'>"._("everyone")."</a>";
		}
		print "\n\t\t</td>";				
		
		print "\n\t</tr>";
		print "\n</table>";		
		
		
		
		// $tagManager = Services::getService("Tagging");
// 		if ($currentUserIdString = $tagManager->getCurrentUserIdString()) {
// 			if ($harmoni->getCurrentAction() == 'tags.user' 
// 				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $currentUserIdString)) 
// 			{
// 				print ""._("your tags")." &nbsp; ";
// 			} else {
// 				$url = $harmoni->request->quickURL('tags', 'user', 
// 					array('agent_id' => $tagManager->getCurrentUserIdString()));
// 				print "<a href='".$url."'>"._("your tags")."</a> &nbsp; ";
// 			}
// 		}
// 		if ($harmoni->getCurrentAction() == 'tags.all') {
// 			print _("all tags");
// 		} else {
// 			$url = $harmoni->request->quickURL('tags', 'all');
// 			print "<a href='".$url."'>"._("all tags")."</a> &nbsp;";
// 		}
		
		if (RequestContext::value('tag')) {
// 			if ($harmoni->getCurrentAction() != 'tags.view') {
// 				$url = $harmoni->request->quickURL('tags', 'view', 
// 					array('agent_id' => $tagManager->getCurrentUserIdString(),
// 					'tag' => RequestContext::value('tag')));
// 				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by everyone"))."</a> &nbsp; ";
// 			}
		
			if ($harmoni->getCurrentAction() == 'tags.viewuser' 
				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $currentUserIdString)) 
			{
				
				print " | &nbsp; ";
				
				if (!defined('TAGGING_JS_LOADED')) {
					// Add the tagging manager script to the header
					$harmoni = Harmoni::instance();
					$outputHandler =$harmoni->getOutputHandler();
					$outputHandler->setHead($outputHandler->getHead()
						."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Tagger.js'></script>"
						."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/quicksort.js'></script>"
						."\n\t\t<link rel='stylesheet' type='text/css' href='".POLYPHONY_PATH."javascript/Tagger.css' />");
					define('TAGGING_JS_LOADED', true);
				}

				
				print "<a onclick=\"TagRenameDialog.run(new Tag('".RequestContext::value('tag')."'), this);\">"._("rename")."</a> &nbsp; ";
				
				
				print "<a onclick=\"";
				print "if (confirm('"._('Are you sure you want to delete all of your instances of this tag?')."')) { ";
				print 	"var req = Harmoni.createRequest(); ";
				print	"var url = Harmoni.quickUrl('tags', 'deleteUser', {'tag': '".RequestContext::value('tag')."'}, 'polyphony-tags'); ";
				print 	"if (req) { ";
				print		"req.onreadystatechange = function () { ";
				print 			"if (req.readyState == 4) { ";
				print				"if (req.status == 200) { ";
				print					"alert('"._('Tag successfully deleted.')."'); ";
				print					"window.location =  Harmoni.quickUrl('tags', 'user', null, 'polyphony-tags'); ";
				print				"} else { ";
				print					"alert('There was a problem retrieving the XML data: ' + req.statusText); ";
				print 				"} ";
				print			"} ";
				print 		"}; ";
				print		"req.open('GET', url, true); ";
				print 		"req.send(null); ";
				print	"} else { ";
				print 		"alert('Error: Unable to execute AJAX request. Please upgrade your browser.'); ";
				print 	"} ";
				print "} ";
				print "\">"._("delete")."</a> &nbsp; ";
				
			} else if ($tagManager->getCurrentUserIdString()) {
// 				$url = $harmoni->request->quickURL('tags', 'viewuser', 
// 					array('agent_id' => $tagManager->getCurrentUserIdString(),
// 					'tag' => RequestContext::value('tag')));
// 				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by you"))."</a> &nbsp; ";
			}
						
		}
		$tagsMenu = ob_get_clean();
		$mainScreen->add(new Block($tagsMenu, STANDARD_BLOCK), "100%", null, LEFT, TOP);
	}	
		
	/**
	 * Add display of tags
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	abstract public function getResult (Component $mainScreen);



	

}