<?php
/**
 * @since 4/8/08
 * @package segue.modules.tags
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueTagsAction.abstract.php,v 1.5 2008/04/10 19:18:03 achapin Exp $
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
 * @version $Id: SegueTagsAction.abstract.php,v 1.5 2008/04/10 19:18:03 achapin Exp $
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
		try {
			$mainScreen->add($this->getSiteHeader());
		} catch (UnimplementedException $e) {
		}
		
		$taggingColumns = $mainScreen->add(new Container(new XLayout, BLANK, 1));	
		
		$harmoni = Harmoni::instance();
		SiteDispatcher::passthroughContext();
		$harmoni->request->startNamespace('polyphony-tags');
		
				
//		$this->addTagsMenu($mainScreen);
		$taggingColumns->add($this->getItemsMenu(), "100px", null, LEFT, TOP);
		
		// implemented by child classes
		//$this->getResult($mainScreen);
		 $taggingColumns->add($this->getResult(), null, null, LEFT, CENTER);
		 
 		$taggingColumns->add($this->getTagsMenu(), "100px", null, LEFT, TOP);

		
				
		$harmoni->request->endNamespace();
		SiteDispatcher::forgetContext();
		
		//implemented in parent class htmlAction
		$this->addFooterControls($mainScreen);
		$this->mainScreen = $mainScreen;
		return $allWrapper;
	}

	/**
	 * Add the site header gui components
	 * 
	 * @return Component
	 * @access public
	 * @since 4/7/08
	 */
	public function getSiteHeader () {
		if ($this->isAuthorizedToExecute()) {
							
			// :: Site ::
			$rootSiteComponent = SiteDispatcher::getCurrentRootNode();

			$visitor = new TagModeSiteVisitor;
			$this->siteGuiComponent = $rootSiteComponent->acceptVisitor($visitor);
			//$mainScreen->add($this->siteGuiComponent);
			
			return $this->siteGuiComponent;
			
		} else {
			// Replace the title
			$title = "\n\t\t<title>"._("Unauthorized")."</title>";
			$outputHandler->setHead(
				preg_replace("/<title>[^<]*<\/title>/", $title, $outputHandler->getHead()));			
		
		// 	$mainScreen->add(new Block($this->getUnauthorizedMessage(), EMPHASIZED_BLOCK),
// 				"100%", null, CENTER, TOP);
				
			return new Block($this->getUnauthorizedMessage(), EMPHASIZED_BLOCK);
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
	public function getItemsMenu () {
		ob_start();
		
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * Answer a menu for the tagging system
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	public function getTagsMenu () {
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
			$url = SiteDispatcher::quickURL('tags', 'usernodetag', 
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
			$url = SiteDispatcher::quickURL('tags', 'nodetag', 
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
			$url = SiteDispatcher::quickURL('tags', 'usersitetag', 
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
			$url = SiteDispatcher::quickURL('tags', 'sitetag', 
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
			$url = SiteDispatcher::quickURL('tags', 'userseguetag', 
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
			$url = SiteDispatcher::quickURL('tags', 'seguetag', 
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

		print "\n\t\t<td style='border: 1px solid;'>";
		print _("by: ");
		// all tags on node by you 
		if ($harmoni->getCurrentAction() != 'tags.usernode') {
			$url = SiteDispatcher::quickURL('tags', 'usernode', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.usernode') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}

		
		// all tags on node by everyone 
		if ($harmoni->getCurrentAction() != 'tags.node') {
			$url = SiteDispatcher::quickURL('tags', 'node', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.node') {
			print "<strong>"._("everyone")."</strong>";			
		} else {
			print _("everyone");
		}
		print "\n\t\t</td>";		
		
		// all tags in site by you 
		print "\n\t\t<td style='border: 1px solid;'>";
		print _("by: ");
		
		// all tags in site by you 
		if ($harmoni->getCurrentAction() != 'tags.usersite') {
			$url = SiteDispatcher::quickURL('tags', 'usersite', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.usersite') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}

		
		// all tags in site by everyone 
		if ($harmoni->getCurrentAction() != 'tags.site') {
			$url = SiteDispatcher::quickURL('tags', 'site', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.site') {
			print "<strong>"._("everyone")."</strong>";			
		} else {
			print _("everyone");
		}
		
		print "\n\t\t</td>";
		

		print "\n\t\t<td style='border: 1px solid;'>";
		print _("by: ");
		
		// all tags from all segue by you
		if ($harmoni->getCurrentAction() != 'tags.usersegue') {
			$url = SiteDispatcher::quickURL('tags', 'usersegue', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("you"))."</a> | ";
		} else if ($harmoni->getCurrentAction() == 'tags.usersegue') {
			print "<strong>"._("you")."</strong> | ";		
		} else {
			print _("you | ");
		}
		
		// all tags from all segue by everyone
		if ($harmoni->getCurrentAction() != 'tags.segue') {
			$url = SiteDispatcher::quickURL('tags', 'segue', 
				array('agent_id' => $tagManager->getCurrentUserIdString(),
				'tag' => RequestContext::value('tag')));
			print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("everyone"))."</a>";
		} else if ($harmoni->getCurrentAction() == 'tags.segue') {
			print "<strong>"._("everyone")."</strong>";			
		} else {
			print _("everyone");
		}
		
		print "\n\t\t</td>";				
		
		print "\n\t</tr>";
		print "\n</table>";		
		
		
		if (RequestContext::value('tag')) {

			if (in_array($harmoni->request->getRequestedAction(), array('usernodetag', 'usersitetag', 'userseguetag'))
				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $tagManager->getCurrentUserIdString())) 
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

				
				print "<a onclick=\"TagRenameDialog.run(new Tag('".RequestContext::value('tag')."'), this, '".$harmoni->request->getRequestedAction()."');\">"._("rename")."</a> &nbsp; ";
				
				
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
// 				$url = SiteDispatcher::quickURL('tags', 'viewuser', 
// 					array('agent_id' => $tagManager->getCurrentUserIdString(),
// 					'tag' => RequestContext::value('tag')));
// 				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by you"))."</a> &nbsp; ";
			}
						
		}
//		$tagsMenu = new Component(ob_get_clean(), BLANK, 2);		
// 		$tagsContainer = $mainScreen->add(new Container(new XLayout, BLOCK, 2), "50%", null, RIGHT, TOP);
// 		$tagsContainer->add($tagsMenu, "100%", null, LEFT, TOP);
		
		$tagsMenu = ob_get_clean();
		//$mainScreen->add(new Block($tagsMenu, STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		return new Block($tagsMenu, STANDARD_BLOCK);
	}	
		
	/**
	 * Add display of tags
	 * 
	 * @param Component $mainScreen
	 * @return void
	 * @access public
	 * @since 4/7/08
	 */
	abstract public function getResult ();
	

}