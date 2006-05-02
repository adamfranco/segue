<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.11 2006/05/02 20:24:17 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

require_once(HARMONI."GUIManager/Components/Header.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");
require_once(HARMONI."GUIManager/Container.class.php");

require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");

require_once(HARMONI."GUIManager/StyleProperties/FloatSP.class.php");

require_once(MYDIR."/main/modules/window/display.act.php");

/**
 * display the site.
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.11 2006/05/02 20:24:17 adamfranco Exp $
 */
class viewAction 
	extends displayAction
{
	/**
	 * If true, editing controls will be displayed
	 * @var boolean $_showControls;  
	 * @access private
	 * @since 2/22/06
	 */
	var $_showControls = false;
		
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function &execute ( &$harmoni ) {
		
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
			
		$assetIdString = RequestContext::value('node');
		$assetId =& $idManager->getId($assetIdString);
		$asset =& $repository->getAsset($assetId);
		$nodeRenderer =& NodeRenderer::forAsset($asset, $null = null);
		
		
		$harmoni =& Harmoni::instance();
		$outputHandler =& $harmoni->getOutputHandler();
		$head = $outputHandler->getHead();
		if (preg_match('/<title>.*<\/title>/', $head))
			$head = preg_replace('/<title>.*<\/title>/', 
				'<title>'.$nodeRenderer->getSiteTitle().'</title>', $head);
		else
			$head .= "<title>".$asset->getDisplayName()."</title>";
		$outputHandler->setHead($head);
		
				
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		
		
		$mainScreen =& new Container($yLayout, BLOCK, BACKGROUND_BLOCK);
		
	// :: Top Row ::
		$headRow =& $mainScreen->add(
			new Container($xLayout, HEADER, 1), 
			"100%", null, CENTER, TOP);
		
		$headRow->add(new UnstyledBlock("<h1>".$nodeRenderer->getSiteTitle()."</h1>"), 
			null, null, LEFT, TOP);
		
		$rightHeadColumn =& $headRow->add(
			new Container($yLayout, BLANK, 1), 
			null, null, CENTER, TOP);
		
		$rightHeadColumn->add($this->getLoginComponent(), 
				null, null, RIGHT, TOP);
		
		ob_start();
		print "\n<div style='font-size: small; vertical-align: top; text-align: right; height:30px;'>";
		print "\n\t<a href='".$harmoni->request->quickURL("home", "welcome")."'>";
		print _("home");
		print "</a>\n</div>";
		$rightHeadColumn->add(new UnstyledBlock(ob_get_clean()), 
				null, null, RIGHT, TOP);
		
		if ($this->_showControls) {
			$siteRenderer =& $nodeRenderer->getSiteRenderer();
			$siteRenderer->setShowControls($this->_showControls);
			
			ob_start();
			print "\n\t<a href='";
			print $harmoni->request->quickURL("site", "view", array('node' => RequestContext::value('node')));
			print "' style='border: 1px solid; padding: 2px; text-align: center; text-decoration: none; margin: 2px;'>";
			print _("Hide Controls");
			print "</a>";
			$rightHeadColumn->add(new UnstyledBlock(ob_get_clean().$siteRenderer->getSettingsForm()), 
				null, null, RIGHT, BOTTOM);
		} else {
			ob_start();
			print "\n\t<a href='";
			print $harmoni->request->quickURL("site", "editview", array('node' => RequestContext::value('node')));
			print "' style='border: 1px solid; padding: 2px; text-align: center; text-decoration: none; margin: 2px;'>";
			print _("Show Controls");
			print "</a>";
			$rightHeadColumn->add(new UnstyledBlock(ob_get_clean()), 
				null, null, RIGHT, BOTTOM);
		}
			
		
		
		// Add the rendered site.
		$mainScreen->add(
			$nodeRenderer->renderSite($this->_showControls),
			"100%", null, CENTER, TOP);
		
		
				
	// :: Footer ::
		$footer =& $mainScreen->add(
			new Container (new XLayout, FOOTER, 1),
			"100%", null, RIGHT, BOTTOM);
		
		$helpText = "<a target='_blank' href='";
		$helpText .= $harmoni->request->quickURL("help", "browse_help");
		$helpText .= "'>"._("Help")."</a>";
		$footer->add(new UnstyledBlock($helpText), "50%", null, LEFT, BOTTOM);
		
		$footerText = "Segue v.2.0-Alpha &copy;2006 Middlebury College: <a href=''>";
		$footerText .= _("credits");
		$footerText .= "</a>";
		$footer->add(new UnstyledBlock($footerText), "50%", null, RIGHT, BOTTOM);
		



		return $mainScreen;
	}	
}

?>