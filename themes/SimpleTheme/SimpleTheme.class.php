<?php

require_once(HARMONI."GUIManager/Theme.class.php");

require_once(HARMONI."GUIManager/StyleCollection.class.php");
require_once(HARMONI."GUIManager/CornersStyleCollection.class.php");

require_once(HARMONI."GUIManager/StyleProperties/BackgroundColorSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BackgroundImageSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/ColorSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderTopSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderRightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderBottomSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/BorderLeftSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginLeftSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/PaddingSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontFamilySP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSizeSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontWeightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextDecorationSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/DisplaySP.class.php");

/**
 * A simple theme with rounded boxes.
 *
 * @package harmoni.gui.themes
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleTheme.class.php,v 1.4 2006/01/30 19:08:11 adamfranco Exp $
 */
class SimpleTheme extends Theme {

	/**
	 * The constructor. All initialization and Theme customization is performed
	 * here.
	 * @access public
	 **/
	function SimpleTheme() {
		$this->Theme("Simple Theme", "A simple theme with rounded boxes.");
		
		// =====================================================================
		// global Theme style
		$body =& new StyleCollection("body", null, "Global Style", "Style settings affecting the overall look and feel.");
		$body->addSP(new BackgroundColorSP("#fff"));
		$body->addSP(new ColorSP("#000"));
		//$body->addSP(new FontFamilySP("Verdana"));
		$body->addSP(new PaddingSP("0px"));
		$body->addSP(new MarginSP("1px"));
		$this->addGlobalStyle($body);

		$links =& new StyleCollection("a", null, "Link Style", "Style settings affecting the look and feel of links.");
		$links->addSP(new TextDecorationSP("underline"));
		$links->addSP(new ColorSP("#FFF"));
		$links->addSP(new FontWeightSP("bold"));
		$this->addGlobalStyle($links);
// 
// 		$links_hover =& new StyleCollection("a:hover", null, "Link Hover Style", "Style settings affecting the look and feel of hover links.");
// 		$links_hover->addSP(new TextDecorationSP("underline"));
// 		$this->addGlobalStyle($links_hover);
// 
		// =====================================================================
		// Block 1 style
		$block1 =& new StyleCollection("*.block1", "block1", "Block 1", "The main block where normally all of the page content goes in.");
// 		$block1->addSP(new BackgroundColorSP("#DDD"));
// 		$block1->addSP(new PaddingSP("10px"));
// 		$block1->addSP(new MarginSP("10px"));
		$this->addStyleForComponentType($block1, BLOCK, 1);
		
		// =====================================================================
		// Block 2 style
		$block2 =& new CornersStyleCollection("*.block2", "block2", "Block 2", "A 2nd level block. Used for standard content");
		$block2->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$block2->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$block2->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$block2->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$block2->addSP(new BackgroundColorSP("#ccc"));
		$block2->addSP(new ColorSP("#000"));
// 		$block2->addSP(new BorderSP("1px", "solid", "#000"));
		$block2->addSP(new PaddingSP("10px"));
		$block2->addSP(new MarginSP("1px"));
		$block2->addSP(new TextAlignSP("justify"));
		$this->addStyleForComponentType($block2, BLOCK, 2);

		$links =& new StyleCollection("*.block2 a", "block2", "Block 2 Links", "Properties of links");
		$links->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($links, BLOCK, 2);
	
		// =====================================================================
		// Block 3 style
		$block3 =& new CornersStyleCollection("*.block3", "block3", "Block 3", "A 3rd level block. Used for emphasized content such as Wizards.");
		$block3->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$block3->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$block3->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$block3->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$block3->addSP(new BackgroundColorSP("#AAA9A9"));
		$block3->addSP(new ColorSP("#000"));
// 		$block3->addSP(new BorderSP("1px", "solid", "#000"));
		$block3->addSP(new PaddingSP("10px"));
		$block3->addSP(new MarginSP("1px"));
		$block3->addSP(new TextAlignSP("justify"));
		$this->addStyleForComponentType($block3, BLOCK, 3);
		
		
		
		// =====================================================================
		// Block 4 style
		$block4 =& new CornersStyleCollection("*.block4", "block4", "Block 4", "A 4th level block. Used for alerts and highlit dialog boxes.");
		$block4->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$block4->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$block4->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$block4->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$block4->addSP(new BackgroundColorSP("#FD9453"));
		$block4->addSP(new ColorSP("#FFF"));
// 		$block4->addSP(new BorderSP("1px", "solid", "#000"));
		$block4->addSP(new PaddingSP("10px"));
		$block4->addSP(new MarginSP("1px"));
		$block4->addSP(new TextAlignSP("justify"));
		$this->addStyleForComponentType($block4, BLOCK, 4);
		
		
		
		// =====================================================================
		// Heading 1 style
		$heading1 =& new CornersStyleCollection("*.heading1", "heading1", "Heading 1", "A 1st level heading.");
		$heading1->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$heading1->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$heading1->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$heading1->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$heading1->addSP(new BackgroundColorSP("#666666"));
// 		$heading1->addSP(new BorderSP("1px", "solid", "#000"));
		$heading1->addSP(new PaddingSP("10px"));
		$heading1->addSP(new MarginSP("1px"));
		$heading1->addSP(new TextAlignSP("justify"));		
		$heading1->addSP(new ColorSP("#fff"));
		$heading1->addSP(new FontSizeSP("175%"));
		$this->addStyleForComponentType($heading1, HEADING, 1);

		
		// =====================================================================
		// Heading 2 style
		$heading2 =& new CornersStyleCollection("*.heading2", "heading2", "Heading 2", "A 2nd level heading.");
		$heading2->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$heading2->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$heading2->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$heading2->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$heading2->addSP(new BackgroundColorSP("#AAA9A9"));
// 		$heading2->addSP(new BorderSP("1px", "solid", "#000"));
		$heading2->addSP(new PaddingSP("10px"));
		$heading2->addSP(new MarginSP("1px"));
		$heading2->addSP(new TextAlignSP("justify"));		
		$heading2->addSP(new ColorSP("#fff"));
		$heading2->addSP(new FontSizeSP("125%"));
		$this->addStyleForComponentType($heading2, HEADING, 2);


		// =====================================================================
		// Header 1 style
		$header1 =& new CornersStyleCollection("*.header1", "header1", "Header 1", "A 1st level header.");
		$header1->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$header1->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$header1->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$header1->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		
		$header1->addSP(new BackgroundColorSP("#666666"));
// 		$header1->addSP(new BorderSP("1px", "solid", "#000"));
		$header1->addSP(new PaddingSP("10px"));
		$header1->addSP(new MarginSP("1px"));
		$header1->addSP(new TextAlignSP("justify"));
		
		$header1->addSP(new ColorSP("#fff"));
// 		$header1->addSP(new FontSizeSP("200%"));
		$this->addStyleForComponentType($header1, HEADER, 1);


		// =====================================================================
		// Footer 1 style
		$footer1 =& new CornersStyleCollection("*.footer1", "footer1", "Footer 1", "A 1st level footer.");
		$footer1->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$footer1->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$footer1->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$footer1->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		$footer1->addSP(new BackgroundColorSP("#666666"));
// 		$footer1->addSP(new BorderSP("1px", "solid", "#000"));
		$footer1->addSP(new PaddingSP("10px"));
		$footer1->addSP(new MarginSP("1px"));
		$footer1->addSP(new TextAlignSP("right"));
		
		$footer1->addSP(new ColorSP("#fff"));
		$footer1->addSP(new FontSizeSP("75%"));
		$this->addStyleForComponentType($footer1, FOOTER, 1);

		
		// =====================================================================
		// Menu 1 style
		$menu1 =& new CornersStyleCollection("*.menu1", "menu1", "Menu 1", "A 1st level menu.");
		$menu1->setBorderUrl("TopLeft", MYPATH."/themes/SimpleTheme/images/corner_TL.gif");
		$menu1->setBorderUrl("TopRight", MYPATH."/themes/SimpleTheme/images/corner_TR.gif");
		$menu1->setBorderUrl("BottomLeft", MYPATH."/themes/SimpleTheme/images/corner_BL.gif");
		$menu1->setBorderUrl("BottomRight", MYPATH."/themes/SimpleTheme/images/corner_BR.gif");
		
		$menu1->addSP(new BackgroundColorSP("#FD9453"));
		$menu1->addSP(new ColorSP("#FFF"));
// 		$menu1->addSP(new BorderSP("1px", "solid", "#000"));
		$menu1->addSP(new PaddingSP("10px"));
		$menu1->addSP(new MarginSP("1px"));
		$menu1->addSP(new TextAlignSP("justify"));
		$this->addStyleForComponentType($menu1, MENU, 1);
		
		// =====================================================================
		// Menu Heading 1 style
		$menuHeading1 =& new StyleCollection("*.menuHeading1", "menuHeading1", "Menu Heading 1", "A 1st level menu heading.");
		$menuHeading1->addSP(new DisplaySP("block"));
		$menuHeading1->addSP(new BackgroundColorSP("#FD9453"));
		$menuHeading1->addSP(new PaddingSP("5px"));
		//$menuHeading1->addSP(new FontWeightSP("bold"));
		$this->addStyleForComponentType($menuHeading1, MENU_ITEM_HEADING, 1);
		
		// =====================================================================
		// Menu Heading 2 style
		$menuHeading2 =& new StyleCollection("*.menuHeading2", "menuHeading2", "Menu Heading 2", "A 2nd level menu heading.");
		$menuHeading2->addSP(new DisplaySP("block"));
		$menuHeading2->addSP(new BackgroundColorSP("#FD9453"));
		$menuHeading2->addSP(new PaddingSP("5px"));
		//$menuHeading2->addSP(new FontWeightSP("bold"));
		$menuHeading2->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($menuHeading2, MENU_ITEM_HEADING, 2);
		
		// =====================================================================
		// Menu Heading 3 style
		$menuHeading3 =& new StyleCollection("*.menuHeading3", "menuHeading3", "Menu Heading 3", "A 3st level menu heading.");
		$menuHeading3->addSP(new DisplaySP("block"));
		$menuHeading3->addSP(new BackgroundColorSP("#FD9453"));
		$menuHeading3->addSP(new PaddingSP("5px"));
		//$menuHeading3->addSP(new FontWeightSP("bold"));
		$menuHeading3->addSP(new MarginLeftSP("20px"));
		$menuHeading3->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuHeading3, MENU_ITEM_HEADING, 3);
		
		// =====================================================================
		// Menu Heading 4 style
		$menuHeading4 =& new StyleCollection("*.menuHeading4", "menuHeading4", "Menu Heading 4", "A 4th level menu heading.");
		$menuHeading4->addSP(new DisplaySP("block"));
		$menuHeading4->addSP(new BackgroundColorSP("#FD9453"));
		$menuHeading4->addSP(new PaddingSP("5px"));
		//$menuHeading4->addSP(new FontWeightSP("bold"));
		$menuHeading4->addSP(new MarginLeftSP("30px"));
		$menuHeading3->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuHeading4, MENU_ITEM_HEADING, 4);
		
		// =====================================================================
		// Menu Unselected Link 1 style
		$menuLink1_unselected =& new StyleCollection("*.menuLink1_unselected a", "menuLink1_unselected", "Unselected Menu Link 1", "A 1st level unselected menu link.");
		$menuLink1_unselected->addSP(new DisplaySP("block"));
		$menuLink1_unselected->addSP(new BackgroundColorSP("#FD9453"));
		$menuLink1_unselected->addSP(new ColorSP("#FFF"));
		$menuLink1_unselected->addSP(new PaddingSP("5px"));
		$menuLink1_unselected->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($menuLink1_unselected, MENU_ITEM_LINK_UNSELECTED, 1);
		
		$menuLink1_hover =& new StyleCollection("*.menuLink1_hover a:hover", "menuLink1_hover", "Menu Link 1 Hover", "A 1st level menu link hover behavior.");
		$menuLink1_hover->addSP(new BackgroundColorSP("#C77441"));
		$this->addStyleForComponentType($menuLink1_hover, MENU_ITEM_LINK_UNSELECTED, 1);
		
		// =====================================================================
		// Menu Selected Link 1 style
		$menuLink1_selected =& new StyleCollection("*.menuLink1_selected a", "menuLink1_selected", "Selected Menu Link 1", "A 1st level selected menu link.");
		$menuLink1_selected->addSP(new DisplaySP("block"));
		$menuLink1_selected->addSP(new BackgroundColorSP("#C77441"));
		$menuLink1_selected->addSP(new ColorSP("#FFF"));
		$menuLink1_selected->addSP(new PaddingSP("5px"));
		$menuLink1_selected->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($menuLink1_selected, MENU_ITEM_LINK_SELECTED, 1);
		
		// =====================================================================
		// Menu Unselected Link 2 style
		$menuLink1_unselected =& new StyleCollection("*.menuLink2_unselected a", "menuLink2_unselected", "Unselected Menu Link ", "A 2nd level unselected menu link.");
		$menuLink1_unselected->addSP(new DisplaySP("block"));
		$menuLink1_unselected->addSP(new BackgroundColorSP("#FD9453"));
		$menuLink1_unselected->addSP(new ColorSP("#FFF"));
		$menuLink1_unselected->addSP(new PaddingSP("5px"));
		$menuLink1_unselected->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($menuLink1_unselected, MENU_ITEM_LINK_UNSELECTED, 2);
		
		$menuLink1_hover =& new StyleCollection("*.menuLink2_hover a:hover", "menuLink2_hover", "Menu Link 2 Hover", "A 2nd level menu link hover behavior.");
		$menuLink1_hover->addSP(new BackgroundColorSP("#C77441"));
		$this->addStyleForComponentType($menuLink1_hover, MENU_ITEM_LINK_UNSELECTED, 2);
		
		// =====================================================================
		// Menu Selected Link 2 style
		$menuLink1_selected =& new StyleCollection("*.menuLink2_selected a", "menuLink2_selected", "Selected Menu Link 2", "A 2nd level selected menu link.");
		$menuLink1_selected->addSP(new DisplaySP("block"));
		$menuLink1_selected->addSP(new BackgroundColorSP("#C77441"));
		$menuLink1_selected->addSP(new ColorSP("#FFF"));
		$menuLink1_selected->addSP(new PaddingSP("5px"));
		$menuLink1_selected->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($menuLink1_selected, MENU_ITEM_LINK_SELECTED, 2);
		
		
		// =====================================================================
		// Menu Unselected Link 3 style
		$menuLink1_unselected =& new StyleCollection("*.menuLink3_unselected a", "menuLink3_unselected", "Unselected Menu Link ", "A 3rd level unselected menu link.");
		$menuLink1_unselected->addSP(new DisplaySP("block"));
		$menuLink1_unselected->addSP(new BackgroundColorSP("#FD9453"));
		$menuLink1_unselected->addSP(new ColorSP("#FFF"));
		$menuLink1_unselected->addSP(new PaddingSP("5px"));
		$menuLink1_unselected->addSP(new MarginLeftSP("20px"));
		$menuLink1_unselected->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuLink1_unselected, MENU_ITEM_LINK_UNSELECTED, 3);
		
		$menuLink1_hover =& new StyleCollection("*.menuLink3_hover a:hover", "menuLink3_hover", "Menu Link 3 Hover", "A 3nd level menu link hover behavior.");
		$menuLink1_hover->addSP(new BackgroundColorSP("#C77441"));
		$this->addStyleForComponentType($menuLink1_hover, MENU_ITEM_LINK_UNSELECTED, 3);
		
		// =====================================================================
		// Menu Selected Link 3 style
		$menuLink1_selected =& new StyleCollection("*.menuLink3_selected a", "menuLink3_selected", "Selected Menu Link 3", "A 3rd level selected menu link.");
		$menuLink1_selected->addSP(new DisplaySP("block"));
		$menuLink1_selected->addSP(new BackgroundColorSP("#C77441"));
		$menuLink1_selected->addSP(new ColorSP("#FFF"));
		$menuLink1_selected->addSP(new PaddingSP("5px"));
		$menuLink1_selected->addSP(new MarginLeftSP("20px"));
		$menuLink1_selected->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuLink1_selected, MENU_ITEM_LINK_SELECTED, 3);
		
		
		// =====================================================================
		// Menu Unselected Link 4 style
		$menuLink1_unselected =& new StyleCollection("*.menuLink4_unselected a", "menuLink4_unselected", "Unselected Menu Link ", "A 4th level unselected menu link.");
		$menuLink1_unselected->addSP(new DisplaySP("block"));
		$menuLink1_unselected->addSP(new BackgroundColorSP("#FD9454"));
		$menuLink1_unselected->addSP(new ColorSP("#FFF"));
		$menuLink1_unselected->addSP(new PaddingSP("5px"));
		$menuLink1_unselected->addSP(new MarginLeftSP("30px"));
		$menuLink1_unselected->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuLink1_unselected, MENU_ITEM_LINK_UNSELECTED, 4);
		
		$menuLink1_hover =& new StyleCollection("*.menuLink4_hover a:hover", "menuLink4_hover", "Menu Link 4 Hover", "A 4nd level menu link hover behavior.");
		$menuLink1_hover->addSP(new BackgroundColorSP("#C77441"));
		$this->addStyleForComponentType($menuLink1_hover, MENU_ITEM_LINK_UNSELECTED, 4);
		
		// =====================================================================
		// Menu Selected Link 4 style
		$menuLink1_selected =& new StyleCollection("*.menuLink4_selected a", "menuLink4_selected", "Selected Menu Link 4", "A 4nd level selected menu link.");
		$menuLink1_selected->addSP(new DisplaySP("block"));
		$menuLink1_selected->addSP(new BackgroundColorSP("#C77441"));
		$menuLink1_selected->addSP(new ColorSP("#FFF"));
		$menuLink1_selected->addSP(new PaddingSP("5px"));
		$menuLink1_selected->addSP(new MarginLeftSP("30px"));
		$menuLink1_selected->addSP(new FontSizeSP("smaller"));
		$this->addStyleForComponentType($menuLink1_selected, MENU_ITEM_LINK_SELECTED, 4);
	}


}

?>