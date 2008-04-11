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
 * @version $Id: SimpleThemeWhite.class.php,v 1.37 2008/04/11 17:03:07 achapin Exp $
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
// 		$collection = new StyleCollection("body", null, "Global Style", "Style settings affecting the overall look and feel.");
// 		$collection->addSP(new BackgroundColorSP("#FFF8C6"));
// 		//$collection->addSP(new BackgroundImageSP("http://slug.middlebury.edu/~achapin/segue2/themes/SimpleThemeWhite/images/black-gray.jpg"));
// 		$collection->addSP(new ColorSP("#FFF"));
// 		$collection->addSP(new FontFamilySP("Verdana, sans-serif"));
// 		$collection->addSP(new FontSizeSP("90%"));
// 		$collection->addSP(new PaddingSP("0px"));
// 		$collection->addSP(new MarginSP("10px"));
// 		$this->addGlobalStyle($collection);
// 
// 		$collection = new StyleCollection("a", null, "Link Style", "Style settings affecting the look and feel of links.");
// 		$collection->addSP(new TextDecorationSP("none"));
// 		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new CursorSP("pointer"));
// // 		$collection->addSP(new FontWeightSP("bold"));
// 		$this->addGlobalStyle($collection);
// // 
// 		$collection = new StyleCollection("a:hover", null, "Link Hover Style", "Style settings affecting the look and feel of hover links.");
// 		$collection->addSP(new TextDecorationSP("underline"));
// 		$this->addGlobalStyle($collection);
// // 
// 		
// 		$collection = new StyleCollection(".thumbnail_image", null, "Thumbnail Images", "Style settings affecting the look and feel of Thumbnail images.");
// 		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$this->addGlobalStyle($collection);
// 		
// 		$collection = new StyleCollection(".thumbnail_icon", null, "Thumbnail Icons", "Style settings affecting the look and feel of Thumbnail icons.");
// 		$collection->addSP(new BorderSP("0px", "solid", "#000"));
// 		$this->addGlobalStyle($collection);
// 		// =====================================================================
// 		// Block 1 style
// 		$collection = new StyleCollection("*.block1", "block1", "Block 1", "The main block where normally all of the page content goes in.");
// // 		$collection->addSP(new BackgroundColorSP("#DDD"));
// // 		$collection->addSP(new PaddingSP("10px"));
// // 		$collection->addSP(new MarginSP("10px"));
// 		$this->addStyleForComponentType($collection, BLOCK, 1);

		// =====================================================================
		// Append 1 style
// 		$collection = new CornersStyleCollection("*.append1", "append1", "append 1", "A style for append new... links.");
// 		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
// 		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
// 		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
// 		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
// 		
// 		$this->addStyleForComponentType($collection, APPEND, 2);
// 
// 		$collection = new StyleCollection("*.append1 a", "append1", "append1 Links", "Properties of links");
// 		$this->addStyleForComponentType($collection, APPEND, 2);


		
		// =====================================================================
		// Block 2 style
		$collection = new CornersStyleCollection("*.block2", "block2", "Block 2", "A 2nd level block. Used for standard content");
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

		$collection = new StyleCollection("*.block2 a", "block2", "Block 2 Links", "Properties of links");
// 		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 2);
	
		// =====================================================================
		// Block 3 style
		$collection = new CornersStyleCollection("*.block3", "block3", "Block 3", "A 3rd level block. Used for emphasized content such as Wizards.");
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
		
// 		$collection = new StyleCollection("*.block3 .thumbnail", "block3", "Block 4 Links", "Properties of links");
// 		$collection->addSP(new BorderSP("2px", "solid", "#000"));
// 		$this->addStyleForComponentType($collection, BLOCK, 3);
		
		
		
		// =====================================================================
		// Block 4 style
		$collection = new CornersStyleCollection("*.block4", "block4", "Block 4", "A 4th level block. Used for alerts and highlit dialog boxes.");
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
// 		$collection = new StyleCollection("*.block4 a", "block4", "Block 4 Links", "Properties of links");
// 		$collection->addSP(new ColorSP("#000"));
		$this->addStyleForComponentType($collection, BLOCK, 4);
		
		
		// =====================================================================
		// Heading 1 style
		$collection = new CornersStyleCollection("*.heading1", "heading1", "Heading 1", "A 1st level heading.");
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
		$collection = new CornersStyleCollection("*.heading2", "heading2", "Heading 2", "A 2nd level heading.");
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
		$collection = new CornersStyleCollection("*.heading3", "heading3", "Heading 3", "A 3rd level heading.");
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
		$collection = new CornersStyleCollection("*.heading4", "heading4", "Heading 4", "A 4th level heading.");
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
		$collection = new CornersStyleCollection("*.header1", "header1", "Header 1", "A 1st level header.");
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
		$collection = new CornersStyleCollection("*.footer1", "footer1", "Footer 1", "A 1st level footer.");
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
// 		$collection = new CornersStyleCollection("*.menu1", "menu1", "Menu 1", "A 1st level menu.");
// 		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
// 		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
// 		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
// 		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");
		
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
// 		$collection->addSP(new ColorSP("#000"));
//  		$collection->addSP(new BorderSP("1px", "solid", "#000"));
// 		$collection->addSP(new PaddingSP("10px"));
// 		$collection->addSP(new MarginSP("1px"));
// 		$collection->addSP(new TextAlignSP("left"));
//		$this->addStyleForComponentType($collection, MENU, 1);

		
		// =====================================================================
		// SubMenu 1 style
		$styleCollection = new StyleCollection("*.subMenu1", "subMenu1", "SubMenu 1", "A 1st level sub-menu.");
		$styleCollection->addSP(new MarginLeftSP("15px"));
		$this->addStyleForComponentType($styleCollection, SUB_MENU, 1);
		
		// =====================================================================
		// Menu Heading 1 style
//		$collection = new StyleCollection("*.menuHeading1", "menuHeading1", "Menu Heading 1", "A 1st level menu heading.");
		$collection = new CornersStyleCollection("*.menuHeading1", "menuHeading1", "Menu 1", "A 1st level menu.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");		
		
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
// 		$collection->addSP(new PaddingSP("5px"));
		//$collection->addSP(new FontWeightSP("bold"));
		$this->addStyleForComponentType($collection, MENU_ITEM_HEADING, 1);
		
		// =====================================================================
		// Menu Unselected Link 1 style
		$collection = new StyleCollection("*.menuLink1_unselected a", "menuLink1_unselected", "Unselected Menu Link 1", "A 1st level unselected menu link.");
		$collection = new CornersStyleCollection("*.menuLink1_unselected", "menuLink1_unselected", "Menu 1", "A 1st level menu.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");

// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		$collection = new StyleCollection("*.menuLink1_hover a:hover", "menuLink1_hover", "Menu Link 1 Hover", "A 1st level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 1);
		
		// =====================================================================
		// Menu Selected Link 1 style
		$collection = new StyleCollection("*.menuLink1_selected a", "menuLink1_selected", "Selected Menu Link 1", "A 1st level selected menu link.");
		$collection = new CornersStyleCollection("*.menuLink1_selected", "menuLink1_selected", "Menu 1", "A 1st level menu.");
		$collection->setBorderUrl("TopLeft", $imagePath."corner_TL.gif");
		$collection->setBorderUrl("TopRight", $imagePath."corner_TR.gif");
		$collection->setBorderUrl("BottomLeft", $imagePath."corner_BL.gif");
		$collection->setBorderUrl("BottomRight", $imagePath."corner_BR.gif");

// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new FontSizeSP("larger"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 1);
		
		// =====================================================================
		// Menu Unselected Link 2 style
		$collection = new StyleCollection("*.menuLink2_unselected a", "menuLink2_unselected", "Unselected Menu Link ", "A 2nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		$collection = new StyleCollection("*.menuLink2_hover a:hover", "menuLink2_hover", "Menu Link 2 Hover", "A 2nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 2);
		
		// =====================================================================
		// Menu Selected Link 2 style
		$collection = new StyleCollection("*.menuLink2_selected a", "menuLink2_selected", "Selected Menu Link 2", "A 2nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("10px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 2);
		
		// =====================================================================
		// Menu Unselected Link 3 style
		$collection = new StyleCollection("*.menuLink3_unselected a", "menuLink3_unselected", "Unselected Menu Link ", "A 3nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		$collection = new StyleCollection("*.menuLink3_hover a:hover", "menuLink3_hover", "Menu Link 3 Hover", "A 3nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 3);
		
		// =====================================================================
		// Menu Selected Link 3 style
		$collection = new StyleCollection("*.menuLink3_selected a", "menuLink3_selected", "Selected Menu Link 3", "A 3nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("20px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 3);
		
		// =====================================================================
		// Menu Unselected Link 4 style
		$collection = new StyleCollection("*.menuLink4_unselected a", "menuLink4_unselected", "Unselected Menu Link ", "A 4nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		$collection = new StyleCollection("*.menuLink4_hover a:hover", "menuLink4_hover", "Menu Link 4 Hover", "A 4nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 4);
		
		// =====================================================================
		// Menu Selected Link 4 style
		$collection = new StyleCollection("*.menuLink4_selected a", "menuLink4_selected", "Selected Menu Link 4", "A 4nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("30px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 4);
		
		// =====================================================================
		// Menu Unselected Link 5 style
		$collection = new StyleCollection("*.menuLink5_unselected a", "menuLink5_unselected", "Unselected Menu Link ", "A 5nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		$collection = new StyleCollection("*.menuLink5_hover a:hover", "menuLink5_hover", "Menu Link 5 Hover", "A 5nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 5);
		
		// =====================================================================
		// Menu Selected Link 5 style
		$collection = new StyleCollection("*.menuLink5_selected a", "menuLink5_selected", "Selected Menu Link 5", "A 5nd level selected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("40px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_SELECTED, 5);
		
		// =====================================================================
		// Menu Unselected Link 6 style
		$collection = new StyleCollection("*.menuLink6_unselected a", "menuLink6_unselected", "Unselected Menu Link ", "A 6nd level unselected menu link.");
// 		$collection->addSP(new DisplaySP("block"));
// 		$collection->addSP(new BackgroundColorSP("#eeeeee"));
//  		$collection->addSP(new ColorSP("#000"));
// 		$collection->addSP(new PaddingSP("5px"));
// 		$collection->addSP(new MarginLeftSP("50px"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 6);
		
		$collection = new StyleCollection("*.menuLink6_hover a:hover", "menuLink6_hover", "Menu Link 6 Hover", "A 6nd level menu link hover behavior.");
// 		$collection->addSP(new BackgroundColorSP("#ccc"));
		$this->addStyleForComponentType($collection, MENU_ITEM_LINK_UNSELECTED, 6);
		
		// =====================================================================
		// Menu Selected Link 6 style
		$collection = new StyleCollection("*.menuLink6_selected a", "menuLink6_selected", "Selected Menu Link 6", "A 6nd level selected menu link.");
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
		
/* In the CSS below, the numbers used are the following:
    1px: the width of the border
    3px: a fudge factor needed for IE5/win (see below)
    4px: the width of the border (1px) plus the 3px IE5/win fudge factor
    14px: the width or height of the border image
*/


		/*********************************************************
		 * general styles
		 *********************************************************/


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
			
			.plugin_content a {
				text-decoration: underline;
			}

		/*********************************************************
		 * Append 1 
		 *********************************************************/

			.append1 {
				padding: 1px;
				border: 0px;
			}
			.append1Content {
				background-color: #FFFFFF;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			
			
			.append1TopCorners, .append1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.append1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


			.append1BorderTL, .append1BorderTR, .append1BorderBL, .append1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.append1BorderTL, .append1BorderBL {
				float: left;
				clear: both;
			}
			.append1BorderTR, .append1BorderBR {
				float: right;
				clear: right;
			}
			.append1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.append1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.append1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.append1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.append1BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .append1BorderTL {
				 margin-left: 0px;
			}
			.append1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .append1BorderTR {
				 margin-right: 0px;
			}
			.append1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .append1BorderBL {
				 margin-left: -0px;
			}
			.append1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .append1BorderBR {
				 margin-left: 0px;
			}

			*.append1 a {
				color: #000;
			}
			
		/*********************************************************
		 * Block 2
		 *********************************************************/

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



		/*********************************************************
		 * Block 3
		 *********************************************************/

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
				 margin-left: -1px;
			}
			html>body .block3BorderTL {
				 margin-left: 0px;
			}
			.block3BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .block3BorderTR {
				 margin-right: 0px;
			}
			.block3BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block3BorderBL {
				 margin-left: -0px;
			}
			.block3BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block3BorderBR {
				 margin-left: 0px;
			}

		/*********************************************************
		 * Block 4
		 *********************************************************/

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
				 margin-left: -1px;
			}
			html>body .block4BorderTL {
				 margin-left: 0px;
			}
			.block4BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .block4BorderTR {
				 margin-right: 0px;
			}
			.block4BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block4BorderBL {
				 margin-left: -0px;
			}
			.block4BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .block4BorderBR {
				 margin-left: 0px;
			}

			*.block4 a {
				color: #000;
			}

		/*********************************************************
		 * Heading 1
		 *********************************************************/

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

		/*********************************************************
		 * Heading 2
		 *********************************************************/

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

		/*********************************************************
		 * Heading 3
		 *********************************************************/

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

		/*********************************************************
		 * Heading 4
		 *********************************************************/

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
			
		/*********************************************************
		 * MenuHeading 1
		 *********************************************************/

			.menuHeading1 {
				padding: 1px;
				border: 0px;
			}
			.menuHeading1Content {
				background-color: #FFFFFF;
				color: #000;
				border: 1px solid #979797;
				padding: 10px;
				text-align: left;
				margin: 0px;
			}
			
			
			.menuHeading1TopCorners, .menuHeading1BottomCorners {
				margin: 0px;
				padding: 0px;
			}
			.menuHeading1Spacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}


			.menuHeading1BorderTL, .menuHeading1BorderTR, .menuHeading1BorderBL, .menuHeading1BorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.menuHeading1BorderTL, .menuHeading1BorderBL {
				float: left;
				clear: both;
			}
			.menuHeading1BorderTR, .menuHeading1BorderBR {
				float: right;
				clear: right;
			}
			.menuHeading1BorderTL {
				margin: 0px 0px 0px 0px;
			}
			.menuHeading1BorderTR {
				margin: -0px -0px 0px 0px;
			}
			.menuHeading1BorderBL {
				margin: -14px 0px 0px 0px;
			}
			.menuHeading1BorderBR {
				margin: -14px 0px 0px 0px;
			}
			.menuHeading1BorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .menuHeading1BorderTL {
				 margin-left: 0px;
			}
			.menuHeading1BorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .menuHeading1BorderTR {
				 margin-right: 0px;
			}
			.menuHeading1BorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuHeading1BorderBL {
				 margin-left: -0px;
			}
			.menuHeading1BorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuHeading1BorderBR {
				 margin-left: 0px;
			}

			*.menuHeading1 a {
				color: #000;
			}


		/*********************************************************
		 * Header 1
		 *********************************************************/

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
				 margin-left: -1px;
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

		/*********************************************************
		 * Footer 1
		 *********************************************************/

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


		/*********************************************************
		 * Menu 1
		 *********************************************************/
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
				margin-left: 17px;
			}
			

			*.menuHeading1 {
				display: block;
				background-color: #eeeeee;
				padding: 1px;
			}

		/*********************************************************
		 * Menu 1 unselected content
		 *********************************************************/

			*.menuLink1_unselected {
				padding: 1px;
				border: 0px;
			}
			
			*.menuLink1_unselectedContent {
				background-color: #d3d3d3;;
				border: 1px solid #979797;
				padding: 5px;
				text-align: left;
				color: #000000;
				font-size: 100%;
				padding-left: 5px;
				margin: 0px;
			}
			
		/*********************************************************
		 * Menu 1 hover
		 *********************************************************/

			*.menuLink1_hover a:hover {
				display: block;
				background-color: #F5F5F5;
				text-decoration: none;
			}
			
		/*********************************************************
		 * Menu 1 selected content
		 *********************************************************/

			*.menuLink1_selected  {
				padding: 1px;
				border: 0px;
			}

			*.menuLink1_selectedContent {
				background-color: #F5F5F5;
				border: 1px solid #979797;
				padding: 5px;
				text-align: left;
				color: #000000;
				font-size: 100%;
				padding-left: 10px;
				margin: 0px;
			}


		/*********************************************************
		 * Menu 1 unselected box
		 *********************************************************/

			.menuLink1_unselectedTopCorners, .menuLink1_unselectedBottomCorners {
				margin: 0px;
				padding: 0px;
			}
			
			.menuLink1_unselectedSpacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}
			
			.menuLink1_unselectedBorderTL, .menuLink1_unselectedBorderTR, .menuLink1_unselectedBorderBL, .menuLink1_unselectedBorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.menuLink1_unselectedBorderTL, .menuLink1_unselectedBorderBL {
				float: left;
				clear: both;
			}
			.menuLink1_unselectedBorderTR, .menuLink1_unselectedBorderBR {
				float: right;
				clear: right;
			}
			.menuLink1_unselectedBorderTL {
				margin: 0px 0px 0px 0px;
			}
			.menuLink1_unselectedBorderTR {
				margin: -0px -0px 0px 0px;
			}
			.menuLink1_unselectedBorderBL {
				margin: -14px 0px 0px 0px;
			}
			.menuLink1_unselectedBorderBR {
				margin: -14px 0px 0px 0px;
			}
			.menuLink1_unselectedBorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .menuLink1_unselectedBorderTL {
				 margin-left: 0px;
			}
			.menuLink1_unselectedBorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .menuLink1_unselectedBorderTR {
				 margin-right: 0px;
			}
			.menuLink1_unselectedBorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuLink1_unselectedBorderBL {
				 margin-left: -0px;
			}
			.menuLink1_unselectedBorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuLink1_unselectedBorderBR {
				 margin-left: 0px;
			}						
			
		/*********************************************************
		 * Menu 1 selected box
		 *********************************************************/


			.menuLink1_selectedTopCorners, .menuLink1_selectedBottomCorners {
				margin: 0px;
				padding: 0px;
			}
			
			.menuLink1_selectedSpacer {
				margin: 0px; padding: 0px; border: 0px;
				clear: both;
				font-size: 1px; line-height: 1px;
			}
			
			.menuLink1_selectedBorderTL, .menuLink1_selectedBorderTR, .menuLink1_selectedBorderBL, .menuLink1_selectedBorderBR {
				width: 14px; height: 14px;
				padding: 0px; border: 0px;
				z-index: 99;
			}
			.menuLink1_selectedBorderTL, .menuLink1_selectedBorderBL {
				float: left;
				clear: both;
			}
			.menuLink1_selectedBorderTR, .menuLink1_selectedBorderBR {
				float: right;
				clear: right;
			}
			.menuLink1_selectedBorderTL {
				margin: 0px 0px 0px 0px;
			}
			.menuLink1_selectedBorderTR {
				margin: -0px -0px 0px 0px;
			}
			.menuLink1_selectedBorderBL {
				margin: -14px 0px 0px 0px;
			}
			.menuLink1_selectedBorderBR {
				margin: -14px 0px 0px 0px;
			}
			.menuLink1_selectedBorderTL {
				 margin-left: -4px;
				 margin-left: -1px;
			}
			html>body .menuLink1_selectedBorderTL {
				 margin-left: 0px;
			}
			.menuLink1_selectedBorderTR {
				 margin-right: -4px;
				 margin-right: -1px;
			}
			html>body .menuLink1_selectedBorderTR {
				 margin-right: 0px;
			}
			.menuLink1_selectedBorderBL {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuLink1_selectedBorderBL {
				 margin-left: -0px;
			}
			.menuLink1_selectedBorderBR {
				 margin-left: -3px;
				 margin-left: 0px;
			}
			html>body .menuLink1_selectedBorderBR {
				 margin-left: 0px;
			}
			

		/*********************************************************
		 * Menu 2
		 *********************************************************/

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
 * Help
 *********************************************************/
.help_text {
	border: 1px solid #111;
	margin: 5px;
	padding: 5px;
	padding-left: 10px;
	padding-right: 10px;
	font-size: smaller;
}

/*********************************************************
 * Status | Commands | Comments | Dates | Attribution
 *********************************************************/
	.seguelinks {
		text-align: left;
		font-size: 10px;
		margin-left: 10px;
	}

	.commands {
		text-align: right;
		font-size: 10px;
		margin-right: 10px;
		padding-bottom: 3px;
	}

	.breadcrumbs {
		text-align: left;
		font-size: 10px;
		margin-right: 10px;
		height: 16px;
		
	}
	
	.breadcrumbs a {
		text-decoration: none;
	}

	.breadcrumbs a:hover {
		text-decoration: underline;
	}	
	.comments {
		float: right; 
		clear: both;
		margin-left: 10px;
		font-size: 12px;
	}

	.history {
		float: right; 
		clear: both;
		margin-left: 10px;
		font-size: 12px;
	}
	

	.attribution {
		float: right; 
		clear: both;
		margin-left: 10px;
		font-size: 10px;
	}
	
	.attribution_line {
		float: right; 
		clear: both;
		margin-left: 10px;
		font-size: 10px;
	}	


		
/*********************************************************
 * UI1 CSS
 *********************************************************/

	.ui1_controls {
		clear: both;
		text-align: right;
		font-size: 10px;
	}

	.ui1_controls a {

	}
	
/*********************************************************
 * UI2 CSS
 *********************************************************/
	.ui2_reorder {
		float: right;
		font-size: 10px;
	}

	.ui2_settingtitle {
		white-space: nowrap;
		font-size: 12px;
		font-weight: bold;
		padding: 2px;
		text-align: left;
	}
	
	.ui2_settingborder {
 		border-bottom: 1px dotted #666666;
		padding: 2px;
		margin: 2px;
 		text-align: right;
	}
	
	
	.ui2_field {
		font-size: 11px;
		border: 1px solid #666666;
	}

	.ui2_text {
		font-size: 11px;
		font-weight: normal;
	}
	
	.ui2_text_smaller {
		font-size: smaller;
	}
	
	.ui2_button {
		font-size: 12px;
		color: #000000;
		border: 1px solid #666666;
		background-color: #FFFFFF;
		margin-bottom: 5px;
		margin-right: 5px;
	}
	
/*********************************************************
 * Plugin Manager Admin CSS
 *********************************************************/
	.plugin_manager_list {
		
	}
	.plugin_manager_list td {
		vertical-align: top;
	}
	
/*********************************************************
 * Plugin History
 *********************************************************/
 	table.history_list {
 		width: 100%;
 		clear: both;
 	}
	table.history_list thead th {
		color: #777;
		background-color: #E9E9E9;
		text-align: center;
		font-weight: normal;
		padding: 5px;
	}
	
	table.history_list tbody td {
		border-bottom: 1px dotted;
		padding: 3px;
	}
	
	table.history_list tbody td.comment {
		font-size: smaller;
	}
	
	.diff_title {
		color: #777;
		background-color: #E9E9E9;
		padding: 5px;
		margin-bottom: 0px;
	}
	
	table.diff_table td.symbol {
		width: 3%;
		text-align: right;
		padding-right: 3px;
	}
	
	table.diff_table td.source {
		width: 47%;
	}
	
	table.diff_table td.diff-addedline {
		background-color: #CFC;
	}
	
	table.diff_table td.diff-deletedline {
		background-color: #FCC;
	}
	
	table.diff_table td.diff-context {
	}
	
	table.diff_table .diffchange {
		font-weight: bold;
	}
	
	table.version_compare thead th {
		white-space: nowrap;
		color: #777;
		background-color: #E9E9E9;
		padding: 5px;
		vertical-align: top;
		width: 50%;
	}
	
	table.version_compare div.version_comment {
		font-weight: normal;
		padding-top: 10px;
	}
	
	table.version_compare tbody td {
		vertical-align: top;
		border: 1px dotted #aaa;
		padding: 3px;
	}
	
/*********************************************************
 * Comments
 *********************************************************/
	div.comment_help {
		font-size: smaller;
		border: 0px;
		font-style: italic;
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
		font-size: smaller;
	}

	.portal_list_site_title {
		float: left;
	}
	
	.portal_list_controls {
		clear: both;
		float: right;
	}
	
	.portal_list_site_description {
		float: left;
		font-size: smaller;
	}
	
	.button {
		border: 1px solid;
	}
	
	input.small {
		font-size: 10px;
		vertical-align: middle;	
		border: 1px solid;
	}
	
	ul.portal_categories {
		padding-left: 0px;
		white-space: nowrap;
	}
	
	ul.portal_categories div.title {
		font-weight: bolder;
	}
	
	ul.portal_categories li {
		list-style-type: none;
		list-style-position: inside;
		margin-bottom: 15px;
		border-top: 1px dotted;
	}
	
	ul.portal_categories div.description {
		white-space: normal;
		font-size: smaller;
	}
	
	ul.portal_folders div.title {
		font-weight: normal;
	}
	
	ul.portal_categories li.current div.title {
		font-weight: bolder;
	}
	
	
	ul.portal_folders {
		margin-top: 5px;
		padding-left: 30px;
	}
	
	ul.portal_folders li {
		list-style-image:  url(".MYPATH."/images/icons/16x16/folder_open.png);
		list-style-position: outside;
		margin-bottom: 5px;
		border: 0px;
	}

/*********************************************************
 * Dataport Styles
 *********************************************************/
	table.dataport_choose_table {
		width: 100%;
	}
	
	table.dataport_choose_table td.import_controls {
		text-align: center;
	}
	
	table.dataport_choose_table td {
		border-top: 1px dotted;
		padding-bottom: 5px;
		padding-top: 5px;
	}
	
	table.dataport_choose_table td.filled {

	}
	
	table.dataport_choose_table td.segue1slot {
		padding-right: 5px;
	}
	
	table.dataport_choose_table td.segue2slot {
		border-left: 1px dotted;
		padding-left: 5px;
		vertical-align: bottom;
	}
		
	table.dataport_choose_table td.open {
		background-color: #AFA;
	}
	
	table.dataport_choose_table div.slotname {
		float: left;
		clear: left;
		padding-left: 5px;
		font-size: 10px;
	}
	
	table.dataport_choose_table div.site_title {
		float: left;
		font-size: 10px;
	}
	
	table.dataport_choose_table div.site_info {
		float: left;

	}
	
	table.dataport_choose_table div.site_description {
		font-size: smaller;
		clear: both;
	}
	
	table.dataport_choose_table form {
		float: right;
	}
	
	table.dataport_choose_table input {
		font-size: 10px;
		border: 1px solid #555;
		background-color: transparent;
	}
	
	table.dataport_choose_table select {
		font-size: 10px;
		border: 1px solid #555;
	}
	
	

/*********************************************************
 * Site Map Styles
 *********************************************************/
	div.siteMap div.children {
		margin-left: 20px;
		clear: both;
	}
	
	div.siteMap div.current {
		font-weight: bold
	}

	div.siteMap div.header_area {
		border: 1px solid #555;
		clear: both;
		font-size: 10px;
	}
	
	div.siteMap div.header_spacer {
		clear: both;
		height: 0px;
	}

	div.siteMap div.footer_area {
		border: 1px solid #555;
		clear: both;
		font-size: 10px;
	}
	
	div.siteMap div.expandSpacer {
		float: left;
		width: 12px;
	}
	
	div.siteMap div.expand {
		float: left;
		width: 12px;
		cursor: pointer;
	}
	
	div.siteMap div.title {
		float: left;
		padding-right: 30px;
	}
	
	div.siteMap div.node {
		border-top: 1px dotted #aaa;
		clear: both;
	}
	
	div.siteMap div.description {
		font-size: 10px;
		font-weight: normal;
		color: #999999;
		padding-top: 2px;		
	}
	
	div.siteMap button {
		font-size: 9px;
		border: 1px solid #555;
		background-color: transparent;
		margin-bottom: 5px;
		margin-right: 5px;
		float: right;
	}
	
/*********************************************************
 * Tagging Styles
 *********************************************************/
	.tagging_header {
		font-size: 10px;
		margin-bottom: 5px;
		padding-bottom: 3px;
		border-bottom: 1px solid #000000;
	}
	
	.tagging_options  {
		font-size: 9px;
		border-bottom: 1px dotted #aaa;
		padding-top: 2px;
		padding-bottom: 2px;
	}
	
	.tagging_options_sel  {
		font-size: 9px;
		font-weight: bold;
		border: 1px solid #aaa;
		background-color: #FFF8C6;
		padding-right: 3px;
		padding-left: 3px;
		padding-bottom: 2px;
		margin: 1px;
	}
	
	.rename_options  {
		text-align: center;
		font-size: 10px;
		border: 1px solid #aaa;
		margin-top: 10px;
		padding-top: 2px;
		padding-bottom: 2px;
	}


/*********************************************************
 * Wizard Styles
 *********************************************************/
	table.radio_matrix th {
		background-color: #aaa;
	}
	
	table.radio_matrix th.spacer {
		background-color: #f0f0f0;
	}
	
	table.radio_matrix .parent {
		background-color: #ccc;
	}
	
	table.radio_matrix .leaf {
		background-color: #f5f5f5;
	}
	
	table.radio_matrix th.option a:hover {
		cursor: help;
	}
	
	table.radio_matrix td {
		text-align: center;
	}
	
	table.search_results {
		border: 0px;
		width: 100%;
		margin-top: 10px;
	}
	table.search_results tr.search_result_item td.color0 {
		background-color: #ddd;
	}
	table.search_results tr.search_result_item td.action_button {
		text-align: right;
	}
		
";
	}

}

?>