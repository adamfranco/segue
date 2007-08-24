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
require_once(HARMONI."GUIManager/StyleProperties/PaddingLeftSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontFamilySP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontSizeSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/FontWeightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/TextDecorationSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/DisplaySP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/CursorSP.class.php");

require_once(HARMONI."GUIManager/StyleProperties/LetterSpacingSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/WordSpacingSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/LineHeightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginRightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginTopSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginBottomSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/PaddingRightSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/PaddingTopSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/PaddingBottomSP.class.php");

/**
 * A simple theme with rounded boxes.
 *
 * @package harmoni.gui.themes
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleThemeWhite.class.php,v 1.3 2007/08/24 20:36:47 achapin Exp $
 */
class SimpleThemeWhite extends Theme {

	/**
	 * The constructor. All initialization and Theme customization is performed
	 * here.
	 * @access public
	 **/
	function SimpleThemeWhite($imagePath = null) {
		if (is_null($imagePath))
			$imagePath = MYPATH."/themes/SimpleThemeWhite/images/";
		
		$this->Theme("Simple Theme", "A simple theme with rounded boxes.");
// 		
// 		// =====================================================================
// 		// global Theme style
// 		$collection =& new StyleCollection("body", null, "Global Style", "Style settings affecting the overall look and feel.");
// 		$collection->addSP(new BackgroundColorSP("#FFF8C6"));
// 		//$collection->addSP(new BackgroundImageSP("http://slug.middlebury.edu/~achapin/segue2/themes/SimpleThemeWhite/images/black-gray.jpg"));
// 		$collection->addSP(new ColorSP("#FFF"));
// 		$collection->addSP(new FontFamilySP("Verdana, sans-serif"));
// 		$collection->addSP(new FontSizeSP("90%"));
// 		$collection->addSP(new PaddingSP("0px"));
// 		$collection->addSP(new MarginSP("10px"));
// 		$this->addGlobalStyle($collection);
// 
// 		$collection =& new StyleCollection("a", null, "Link Style", "Style settings affecting the look and feel of links.");
// 		$collection->addSP(new TextDecorationSP("none"));
// 		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new CursorSP("pointer"));
// // 		$collection->addSP(new FontWeightSP("bold"));
// 		$this->addGlobalStyle($collection);
// // 
// 		$collection =& new StyleCollection("a:hover", null, "Link Hover Style", "Style settings affecting the look and feel of hover links.");
// 		$collection->addSP(new TextDecorationSP("underline"));
// 		$this->addGlobalStyle($collection);
// // 
// 		
// 		$collection =& new StyleCollection(".thumbnail_image", null, "Thumbnail Images", "Style settings affecting the look and feel of Thumbnail images.");
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$this->addGlobalStyle($collection);
// 		
// 		$collection =& new StyleCollection(".thumbnail_icon", null, "Thumbnail Icons", "Style settings affecting the look and feel of Thumbnail icons.");
// 		$collection->addSP(new BorderSP("0px", "solid", "#000"));
// 		$this->addGlobalStyle($collection);
// 		// =====================================================================
// 		// Block 1 style
// 		$collection =& new StyleCollection("*.block1", "block1", "Block 1", "The main block where normally all of the page content goes in.");
// // 		$collection->addSP(new BackgroundColorSP("#DDD"));
// // 		$collection->addSP(new PaddingSP("10px"));
// // 		$collection->addSP(new MarginSP("10px"));
// 		$this->addStyleForComponentType($collection, BLOCK, 1);
		
		// =====================================================================
		// Block 2 style
		$collection =& new CornersStyleCollection("*.block2", "block2", "Block 2", "A 2nd level block. Used for standard content");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#FFFFFF"));
// 		$collection->addSP(new ColorSP("#000"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#666666"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));
		$this->addStyleForComponentType($collection, BLOCK, 2);

		$collection =& new StyleCollection("*.block2 a", "block2", "Block 2 Links", "Properties of links");
// 		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 2);
	
		// =====================================================================
		// Block 3 style
		$collection =& new CornersStyleCollection("*.block3", "block3", "Block 3", "A 3rd level block. Used for emphasized content such as Wizards.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#fff"));
// 		$collection->addSP(new ColorSP("#000"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
		$this->addStyleForComponentType($collection, BLOCK, 3);
		
// 		$collection =& new StyleCollection("*.block3 .thumbnail", "block3", "Block 4 Links", "Properties of links");
// 		$collection->addSP(new BorderSP("2px", "solid", "#000"));
// 		$this->addStyleForComponentType($collection, BLOCK, 3);
		
		
		
		// =====================================================================
		// Block 4 style
		$collection =& new CornersStyleCollection("*.block4", "block4", "Block 4", "A 4th level block. Used for alerts and highlit dialog boxes.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
// 		$collection->addSP(new ColorSP("#000"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));
// 		$this->addStyleForComponentType($collection, BLOCK, 4);
// 		
// 		$collection =& new StyleCollection("*.block4 a", "block4", "Block 4 Links", "Properties of links");
// 		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 4);
		
		
		// =====================================================================
		// Heading 1 style
		$collection =& new CornersStyleCollection("*.heading1", "heading1", "Heading 1", "A 1st level heading.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#B2B2B2"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
// 		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new FontSizeSP("175%"));
		$this->addStyleForComponentType($collection, HEADING, 1);

		
		// =====================================================================
		// Heading 2 style
		$collection =& new CornersStyleCollection("*.heading2", "heading2", "Heading 2", "A 2nd level heading.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#999"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
// 		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("150%"));
// 		$collection->addSP(new PaddingLeftSP("20px"));
		$this->addStyleForComponentType($collection, HEADING, 2);
		
		// =====================================================================
		// Heading 3 style
		$collection =& new CornersStyleCollection("*.heading3", "heading3", "Heading 3", "A 3rd level heading.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#aaa"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
// 		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("125%"));
// 		$collection->addSP(new PaddingLeftSP("30px"));
		$this->addStyleForComponentType($collection, HEADING, 3);
		
		// =====================================================================
		// Heading 4 style
		$collection =& new CornersStyleCollection("*.heading4", "heading4", "Heading 4", "A 4th level heading.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#aaa"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
// 		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("100%"));
// 		$collection->addSP(new PaddingLeftSP("40px"));
		$this->addStyleForComponentType($collection, HEADING, 4);


		// =====================================================================
		// Header 1 style
		$collection =& new CornersStyleCollection("*.header1", "header1", "Header 1", "A 1st level header.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
		
// 		$collection->addSP(new BackgroundColorSP("#777"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));		
// 		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("200%"));
		$this->addStyleForComponentType($collection, HEADER, 1);


		// =====================================================================
		// Footer 1 style
		$collection =& new CornersStyleCollection("*.footer1", "footer1", "Footer 1", "A 1st level footer.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
// 		$collection->addSP(new BackgroundColorSP("#777"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("right"));
// 		
// 		$collection->addSP(new ColorSP("#fff"));
// 		$collection->addSP(new FontSizeSP("75%"));
		$this->addStyleForComponentType($collection, FOOTER, 1);

		
		// =====================================================================
		// Menu 1 style
		$collection =& new CornersStyleCollection("*.menu1", "menu1", "Menu 1", "A 1st level menu.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
// 		$collection->addSP(new ColorSP("#000"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));
		$this->addStyleForComponentType($collection, MENU, 1);
		
		// =====================================================================
		// SubMenu 1 style
		$styleCollection =& new StyleCollection("*.subMenu1", "subMenu1", "SubMenu 1", "A 1st level sub-menu.");
		$styleCollection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($styleCollection, SUB_MENU, 1);
		
		// =====================================================================
		// Menu Heading 1 style
		$collection =& new StyleCollection("*.menuHeading1", "menuHeading1", "Menu Heading 1", "A 1st level menu heading.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
// 		$collection->addSP(new PaddingSP("5px"));
		//$collection->addSP(new FontWeightSP("bold"));
		$this->addStyleForComponentType($collection, MENU_ITEM_HEADING, 1);
		
		// =====================================================================
		// Menu Unselected Link 1 style
		$collection =& new StyleCollection("*.menuLink1_unselected a", "menuLink1_unselected", "Unselected Menu Link 1", "A 1st level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		$collection =& new StyleCollection("*.menuLink1_hover a:hover", "menuLink1_hover", "Menu Link 1 Hover", "A 1st level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		// =====================================================================
		// Menu Selected Link 1 style
		$collection =& new StyleCollection("*.menuLink1_selected a", "menuLink1_selected", "Selected Menu Link 1", "A 1st level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 1);
		
		// =====================================================================
		// Menu Unselected Link 2 style
		$collection =& new StyleCollection("*.menuLink2_unselected a", "menuLink2_unselected", "Unselected Menu Link ", "A 2nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		$collection =& new StyleCollection("*.menuLink2_hover a:hover", "menuLink2_hover", "Menu Link 2 Hover", "A 2nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		// =====================================================================
		// Menu Selected Link 2 style
		$collection =& new StyleCollection("*.menuLink2_selected a", "menuLink2_selected", "Selected Menu Link 2", "A 2nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 2);
		
		// =====================================================================
		// Menu Unselected Link 3 style
		$collection =& new StyleCollection("*.menuLink3_unselected a", "menuLink3_unselected", "Unselected Menu Link ", "A 3nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		$collection =& new StyleCollection("*.menuLink3_hover a:hover", "menuLink3_hover", "Menu Link 3 Hover", "A 3nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		// =====================================================================
		// Menu Selected Link 3 style
		$collection =& new StyleCollection("*.menuLink3_selected a", "menuLink3_selected", "Selected Menu Link 3", "A 3nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 3);
		
		// =====================================================================
		// Menu Unselected Link 4 style
		$collection =& new StyleCollection("*.menuLink4_unselected a", "menuLink4_unselected", "Unselected Menu Link ", "A 4nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		$collection =& new StyleCollection("*.menuLink4_hover a:hover", "menuLink4_hover", "Menu Link 4 Hover", "A 4nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		// =====================================================================
		// Menu Selected Link 4 style
		$collection =& new StyleCollection("*.menuLink4_selected a", "menuLink4_selected", "Selected Menu Link 4", "A 4nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 4);
		
		// =====================================================================
		// Menu Unselected Link 5 style
		$collection =& new StyleCollection("*.menuLink5_unselected a", "menuLink5_unselected", "Unselected Menu Link ", "A 5nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		$collection =& new StyleCollection("*.menuLink5_hover a:hover", "menuLink5_hover", "Menu Link 5 Hover", "A 5nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		// =====================================================================
		// Menu Selected Link 5 style
		$collection =& new StyleCollection("*.menuLink5_selected a", "menuLink5_selected", "Selected Menu Link 5", "A 5nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 5);
		
		// =====================================================================
		// Menu Unselected Link 6 style
		$collection =& new StyleCollection("*.menuLink6_unselected a", "menuLink6_unselected", "Unselected Menu Link ", "A 6nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("50px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 6);
		
		$collection =& new StyleCollection("*.menuLink6_hover a:hover", "menuLink6_hover", "Menu Link 6 Hover", "A 6nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 6);
		
		// =====================================================================
		// Menu Selected Link 6 style
		$collection =& new StyleCollection("*.menuLink6_selected a", "menuLink6_selected", "Selected Menu Link 6", "A 6nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("50px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 6);
	}

	/**
	 * Returns all CSS code: The CSS code for the Theme, the various component types,
	 * the theme component and all sub-components (if any). Theme styles should come
	 * first, followed by individual component's styles to allow the latter to take
	 * precedence.
	 * @access public
	 * @param string tabs This is a string (normally a bunch of tabs) that will be
	 * prepended to each text line. This argument is optional but its usage is highly 
	 * recommended in order to produce a nicely formatted HTML output.
	 * @return string CSS code.
	 **/
	function getCSS($tabs = "") {
	
	
	
		return "

			body {
				background-color: #FFF8C6;
				color: #000;
				font-family: Verdana, sans-serif;
				font-size:12px;
				padding: 0px;
				margin: 10px;
			}

			a {
				text-decoration: none;
				color: #990000;
				cursor: pointer;
			}

			a:hover {
				text-decoration: underline;
			}

			.thumbnail_image {
				border: 1px solid #000;
			}

			.thumbnail_icon {
				border: 0px solid #000;
			}


			.block2 {
				padding: 1px;
				border: 0px;
			}
			.block2Content {
				background-color: #FFFFFF;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			
			
			.block2TopCorners, .block2BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.block2Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.block2BorderTL, .block2BorderTR, .block2BorderBL, .block2BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.block2BorderTL, .block2BorderBL {
				float: left;
				clear: both;
			}
			.block2BorderTR, .block2BorderBR {
				float: right;
				clear: right;
			}
			.block2BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.block2BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.block2BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.block2BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.block2BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .block2BorderTL {
				 margin-left: 0px;
			}
			.block2BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .block2BorderTR {
				 margin-right: 0px;
			}
			.block2BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block2BorderBL {
				 margin-left: -0px;
			}
			.block2BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block2BorderBR {
				 margin-left: 0px;
			}

			*.block2 a {
				color: #000;
			}

			*.block3 {
				padding: 1px;
				border: 0px;
			}
			*.block3Content {
				background-color: #fff;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			.block3TopCorners, .block3BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.block3Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.block3BorderTL, .block3BorderTR, .block3BorderBL, .block3BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.block3BorderTL, .block3BorderBL {
				float: left;
				clear: both;
			}
			.block3BorderTR, .block3BorderBR {
				float: right;
				clear: right;
			}
			.block3BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.block3BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.block3BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.block3BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.block3BorderTL {
				 margin-left: -4px;
				 ma\rgin-left: -1px;
			}
			html>body .block3BorderTL {
				 margin-left: 0px;
			}
			.block3BorderTR {
				 margin-right: -4px;
				 ma\rgin-right: -1px;
			}
			html>body .block3BorderTR {
				 margin-right: 0px;
			}
			.block3BorderBL {
				 margin-left: -3px;
				 ma\rgin-left: 0px;
			}
			html>body .block3BorderBL {
				 margin-left: -0px;
			}
			.block3BorderBR {
				 margin-left: -3px;
				 ma\rgin-left: 0px;
			}
			html>body .block3BorderBR {
				 margin-left: 0px;
			}

			*.block4 {
				padding: 1px;
				border: 0px;
			}
			*.block4Content {
				background-color: #eeeeee;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			.block4TopCorners, .block4BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.block4Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.block4BorderTL, .block4BorderTR, .block4BorderBL, .block4BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.block4BorderTL, .block4BorderBL {
				float: left;
				clear: both;
			}
			.block4BorderTR, .block4BorderBR {
				float: right;
				clear: right;
			}
			.block4BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.block4BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.block4BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.block4BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.block4BorderTL {
				 margin-left: -4px;
				 ma\rgin-left: -1px;
			}
			html>body .block4BorderTL {
				 margin-left: 0px;
			}
			.block4BorderTR {
				 margin-right: -4px;
				 ma\rgin-right: -1px;
			}
			html>body .block4BorderTR {
				 margin-right: 0px;
			}
			.block4BorderBL {
				 margin-left: -3px;
				 ma\rgin-left: 0px;
			}
			html>body .block4BorderBL {
				 margin-left: -0px;
			}
			.block4BorderBR {
				 margin-left: -3px;
				 ma\rgin-left: 0px;
			}
			html>body .block4BorderBR {
				 margin-left: 0px;
			}

			*.block4 a {
				color: #000;
			}

			*.heading1 {
				padding: 1px;
				border: 0px;
			}
			*.heading1Content {
				background-color: #B2B2B2;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				color: #000;
				font-size: 150%;
				margin: 0px;
			}
			.heading1TopCorners, .heading1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.heading1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.heading1BorderTL, .heading1BorderTR, .heading1BorderBL, .heading1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.heading1BorderTL, .heading1BorderBL {
				float: left;
				clear: both;
			}
			.heading1BorderTR, .heading1BorderBR {
				float: right;
				clear: right;
			}
			.heading1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.heading1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.heading1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.heading1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.heading1BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .heading1BorderTL {
				 margin-left: 0px;
			}
			.heading1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .heading1BorderTR {
				 margin-right: 0px;
			}
			.heading1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading1BorderBL {
				 margin-left: -0px;
			}
			.heading1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading1BorderBR {
				 margin-left: 0px;
			}

			*.heading2 {
				padding: 1px;
				border: 0px;
			}
			*.heading2Content {
				background-color: #F5F5F5;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				color: #000000;
				font-size: 125%;
				padding-left: 20px;
				margin: 0px;
			}
			.heading2TopCorners, .heading2BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.heading2Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.heading2BorderTL, .heading2BorderTR, .heading2BorderBL, .heading2BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.heading2BorderTL, .heading2BorderBL {
				float: left;
				clear: both;
			}
			.heading2BorderTR, .heading2BorderBR {
				float: right;
				clear: right;
			}
			.heading2BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.heading2BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.heading2BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.heading2BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.heading2BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .heading2BorderTL {
				 margin-left: 0px;
			}
			.heading2BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .heading2BorderTR {
				 margin-right: 0px;
			}
			.heading2BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading2BorderBL {
				 margin-left: -0px;
			}
			.heading2BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading2BorderBR {
				 margin-left: 0px;
			}

			*.heading3 {
				padding: 1px;
				border: 0px;
			}
			*.heading3Content {
				background-color: #aaa;
				border: 1px solid #000;
				padding: 10px;
				text-align: left;
				color: #fff;
				font-size: 125%;
				padding-left: 30px;
				margin: 0px;
			}
			.heading3TopCorners, .heading3BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.heading3Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.heading3BorderTL, .heading3BorderTR, .heading3BorderBL, .heading3BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.heading3BorderTL, .heading3BorderBL {
				float: left;
				clear: both;
			}
			.heading3BorderTR, .heading3BorderBR {
				float: right;
				clear: right;
			}
			.heading3BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.heading3BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.heading3BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.heading3BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.heading3BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .heading3BorderTL {
				 margin-left: 0px;
			}
			.heading3BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .heading3BorderTR {
				 margin-right: 0px;
			}
			.heading3BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading3BorderBL {
				 margin-left: -0px;
			}
			.heading3BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading3BorderBR {
				 margin-left: 0px;
			}

			*.heading4 {
				padding: 1px;
				border: 0px;
			}
			*.heading4Content {
				background-color: #aaa;
				border: 1px solid #000;
				padding: 10px;
				text-align: left;
				color: #fff;
				font-size: 100%;
				padding-left: 40px;
				margin: 0px;
			}
			.heading4TopCorners, .heading4BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.heading4Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.heading4BorderTL, .heading4BorderTR, .heading4BorderBL, .heading4BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.heading4BorderTL, .heading4BorderBL {
				float: left;
				clear: both;
			}
			.heading4BorderTR, .heading4BorderBR {
				float: right;
				clear: right;
			}
			.heading4BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.heading4BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.heading4BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.heading4BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.heading4BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .heading4BorderTL {
				 margin-left: 0px;
			}
			.heading4BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .heading4BorderTR {
				 margin-right: 0px;
			}
			.heading4BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading4BorderBL {
				 margin-left: -0px;
			}
			.heading4BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .heading4BorderBR {
				 margin-left: 0px;
			}

			*.header1 {
				padding: 1px;
				border: 0px;
			}
			*.header1Content {
				background-color: #FFFFFF;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				color: #000000;
				margin: 0px;
			}
			.header1TopCorners, .header1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.header1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.header1BorderTL, .header1BorderTR, .header1BorderBL, .header1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.header1BorderTL, .header1BorderBL {
				float: left;
				clear: both;
			}
			.header1BorderTR, .header1BorderBR {
				float: right;
				clear: right;
			}
			.header1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.header1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.header1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.header1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.header1BorderTL {
				 margin-left: -4px;
				 ma\rgin-left: -1px;
			}
			html>body .header1BorderTL {
				 margin-left: 0px;
			}
			.header1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .header1BorderTR {
				 margin-right: 0px;
			}
			.header1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .header1BorderBL {
				 margin-left: -0px;
			}
			.header1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .header1BorderBR {
				 margin-left: 0px;
			}

			*.footer1 {
				padding: 1px;
				border: 0px;
			}
			*.footer1Content {
				background-color: #D3D3D3;
				border: 1px solid #979797;
				padding: 10px;
				text-align: right;
				color: #fff;
				font-size: 75%;
				margin: 0px;
			}
			.footer1TopCorners, .footer1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.footer1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.footer1BorderTL, .footer1BorderTR, .footer1BorderBL, .footer1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.footer1BorderTL, .footer1BorderBL {
				float: left;
				clear: both;
			}
			.footer1BorderTR, .footer1BorderBR {
				float: right;
				clear: right;
			}
			.footer1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.footer1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.footer1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.footer1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.footer1BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .footer1BorderTL {
				 margin-left: 0px;
			}
			.footer1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .footer1BorderTR {
				 margin-right: 0px;
			}
			.footer1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .footer1BorderBL {
				 margin-left: -0px;
			}
			.footer1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .footer1BorderBR {
				 margin-left: 0px;
			}

			*.menu1 {
				padding: 1px;
				border: 0px;
			}
			*.menu1Content {
				background-color: #eeeeee;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			.menu1TopCorners, .menu1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.menu1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/
			.menu1BorderTL, .menu1BorderTR, .menu1BorderBL, .menu1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.menu1BorderTL, .menu1BorderBL {
				float: left;
				clear: both;
			}
			.menu1BorderTR, .menu1BorderBR {
				float: right;
				clear: right;
			}
			.menu1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.menu1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.menu1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.menu1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.menu1BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .menu1BorderTL {
				 margin-left: 0px;
			}
			.menu1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .menu1BorderTR {
				 margin-right: 0px;
			}
			.menu1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menu1BorderBL {
				 margin-left: -0px;
			}
			.menu1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menu1BorderBR {
				 margin-left: 0px;
			}

			*.subMenu1 {
				margin-left: 10px;
			}

			*.menuHeading1 {
				display: block;
				background-color: #eeeeee;
				padding: 5px;
			}

			*.menuLink1_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				font-size: 100%;
			}

			*.menuLink1_hover a:hover {
				background-color: #ccc;
				text-decoration: none;
			}
			}

			*.menuLink1_selected a {
				display: block;
				background-color: #ccc;
				color: #000;
				padding: 5px;
				font-size: 100%;
			}

			*.menuLink2_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				margin-left: 10px;
			}

			*.menuLink2_hover a:hover {
				background-color: #ccc;
				text-decoration: none;
			}

			*.menuLink2_selected a {
				display: block;
				background-color: #ccc;
				text-decoration: none;
				color: #000;
				padding: 5px;
				margin-left: 10px;
			}

			*.menuLink3_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				margin-left: 20px;
			}

			*.menuLink3_hover a:hover {
				background-color: #ccc;
			}

			*.menuLink3_selected a {
				display: block;
				background-color: #ccc;
				color: #000;
				padding: 5px;
				margin-left: 20px;
			}

			*.menuLink4_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				margin-left: 30px;
			}

			*.menuLink4_hover a:hover {
				background-color: #ccc;
			}

			*.menuLink4_selected a {
				display: block;
				background-color: #ccc;
				color: #000;
				padding: 5px;
				margin-left: 30px;
			}

			*.menuLink5_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				margin-left: 40px;
			}

			*.menuLink5_hover a:hover {
				background-color: #ccc;
			}

			*.menuLink5_selected a {
				display: block;
				background-color: #ccc;
				color: #000;
				padding: 5px;
				margin-left: 40px;
			}

			*.menuLink6_unselected a {
				display: block;
				background-color: #eeeeee;
				color: #000;
				padding: 5px;
				margin-left: 50px;
			}

			*.menuLink6_hover a:hover {
				background-color: #ccc;
			}

			*.menuLink6_selected a {
				display: block;
				background-color: #ccc;
				color: #000;
				padding: 5px;
				margin-left: 50px;
			}
			
/*********************************************************
 * Breadcrumbs
 *********************************************************/

	breadcrumbs {
		text-align: right;
		font-size: 10px;
	}

		
/*********************************************************
 * User Interface 1 CSS
 *********************************************************/
	.ui1_controls {
		text-align: right;
		font-size: 10px;
	}

	.ui1_controls a {
		text-align: right;
		font-size: 10px;
	}
	
/*********************************************************
 * Portal UI CSS
 *********************************************************/
	form.add_slot_form {
		font-size: smaller;
		float: right;
	}

	.portal_list_slotname {
		float: right;
	}
		
";
	}

}

?>