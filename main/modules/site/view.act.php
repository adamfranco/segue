<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.6 2006/01/26 21:15:19 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(POLYPHONY."/main/library/Basket/BasketManager.class.php");

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

/**
 * display the site.
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.6 2006/01/26 21:15:19 adamfranco Exp $
 */
class viewAction 
	extends Action
{
		
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
			
		
		
		// Add the rendered site.
		$mainScreen->add(
			$nodeRenderer->renderSite(),
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