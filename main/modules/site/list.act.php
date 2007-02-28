<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.7 2007/02/28 16:35:39 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.7 2007/02/28 16:35:39 adamfranco Exp $
 */
class listAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Plugin Tests");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		$repositoryManager =& Services::getService("Repository");
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.segue.sites_repository")))
		{
			ob_start();
			print "\n\t<a href='";
			print $harmoni->request->quickURL("site", "add");
			print "' style='border: 1px solid; padding: 2px; text-align: center; text-decoration: none; margin: 2px;'>";
			print _("Create New Site");
			print "</a>";
			$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), null, null, RIGHT, CENTER);
		}
		
		$actionRows->add(new Heading(_("All Sites"), 1));
		
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		
		$siteType = new HarmoniType('segue', 
							'edu.middlebury', 
							'SiteNavBlock', 
							'An Asset of this type is the root node of a Segue site.');
		$assets =& $repository->getAssetsByType($siteType);
		
		
		$harmoni =& Harmoni::instance();
		$resultPrinter =& new IteratorResultPrinter($assets, 1, 10, "printSiteShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout("canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
	}
}

/**
 * return true if the current user can view this asset
 * 
 * @param object Asset $asset
 * @return boolean
 * @access public
 * @since 1/18/06
 */
function canView( & $asset ) {
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	
	if ($authZ->isUserAuthorizedBelow($idManager->getId("edu.middlebury.authorization.view"), $asset->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * 
 * 
 * @param <##>
 * @return <##>
 * @access public
 * @since 1/18/06
 */
function printSiteShort(& $asset, &$harmoni, $num) {
	$container =& new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC =& new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
// 	$fillContainerSC->addSP(new WidthSP("100%"));
// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
	$container->addStyle($fillContainerSC);
	
	$centered =& new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	ob_start();
	$assetId =& $asset->getId();
	print "\n\t<a href='".$harmoni->request->quickURL('site', 'view', array('node' => $assetId->getIdString()))."'>";
	print "\n\t<strong>".htmlspecialchars($asset->getDisplayName())."</strong>";
	print "\n\t</a>";
	print "\n\t<br/>"._("ID#").": ".$assetId->getIdString();
	print "\n\t<a href='".$harmoni->request->quickURL('site', 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
	print "\n\t | <a href='".$harmoni->request->quickURL('site', 'deleteComponent', array('node' => $assetId->getIdString()))."'>"._("delete")."</a>";
	
	$description =& HtmlString::withValue($asset->getDescription());
	$description->trim(25);
	print  "\n\t<div style='font-size: smaller;'>".$description->asString()."</div>";	
	
	$component =& new UnstyledBlock(ob_get_contents());
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	return $container;
}

?>