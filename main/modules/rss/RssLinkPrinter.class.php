<?php
/**
 * @since 3/11/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssLinkPrinter.class.php,v 1.4 2008/04/01 16:08:19 adamfranco Exp $
 */ 

/**
 * A class for print RSS links
 * 
 * @since 3/11/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssLinkPrinter.class.php,v 1.4 2008/04/01 16:08:19 adamfranco Exp $
 */
class RssLinkPrinter {
		
	/**
	 * Answer an HTML block of RSS feed links for the node specified
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 3/11/08
	 * @static
	 */
	public static function getLinkBlock (SiteComponent $siteComponent) {
		ob_start();
		print "\n\t<div>";
		print "\n\t\t<div>";
		$first = true;
		foreach (self::getLinks($siteComponent)  as $link) {
			
			print "\n\t\t<a href='".$link['url']."' title=\"".$link['title']."\">";
			print "\n\t\t\t<img src='".MYPATH."/images/Rss.png' alt='rss' style='border: 0; vertical-align: middle;'/></a>";
			print "\n\t\t<a href='".$link['url']."' title=\"".$link['title']."\">";
			print " ".$link['label'];
			print "\n\t\t\t</a> ";
			
			if ($first)
				print " &nbsp; &nbsp; ";
			$first = false;
			
		}
		print "\n\t\t</div>";
		print "\n\t</div>";
		return ob_get_clean();
	}
	
	/**
	 * Add the RSS links to the document Head
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/11/08
	 * @static
	 */
	public static function addHeadLinks (SiteComponent $siteComponent) {
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		
		//print tags
		ob_start();
		
		foreach (self::getLinks($siteComponent) as $link) {
			print "\n\t\t<link rel='alternate' type='application/rss+xml' title=\"".htmlentities(strip_tags($link['title']))."\" href='".$link['url']."' />";
		}
		
		$outputHandler->setHead(
			$outputHandler->getHead()
			.ob_get_clean());
	}
	
	/**
	 * Answer an array of RSS link info.
	 * 
	 * @param  object SiteComponent $siteComponent
	 * @return array
	 * @access private
	 * @since 3/11/08
	 * @static
	 */
	private static function getLinks (SiteComponent $siteComponent) {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$links = array();
		
		// Content RSS
		$links[] = array(
			'url' =>  $harmoni->request->quickUrl(
						"rss",
						"content",
						array('node' => $siteComponent->getId())),
			'label' => _('Content RSS'),
			'title' => _("Content RSS for")." ".htmlentities(strip_tags($siteComponent->getDisplayName()))
		);
		
		// Comments RSS
		$links[] = array(
			'url' =>  $harmoni->request->quickUrl(
						"rss",
						"comments",
						array('node' => $siteComponent->getId())),
			'label' => _('Discussion RSS'),
			'title' => _("Discussion RSS for")." ".htmlentities(strip_tags($siteComponent->getDisplayName()))
		);
		
		$harmoni->request->endNamespace();
		return $links;
	}
	
}

?>