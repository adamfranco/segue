<?php
/**
 * @since 3/10/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: comments.act.php,v 1.2 2008/03/31 20:07:47 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");
require_once(MYDIR."/main/modules/rss/content.act.php");

/**
 * generate RSS feed of comments in content blocks
 * 
 * @since 3/10/08
 * @package segue.rss
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: comments.act.php,v 1.2 2008/03/31 20:07:47 adamfranco Exp $
 */  
class commentsAction
	extends contentAction

{
	
	/*********************************************************
	 * Vistor methods
	 *********************************************************/

	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
	
		// check to see if user is authorized to view block
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");	
		if (!$authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view_comments"), 
			$idManager->getId($siteComponent->getId())))
		{
			return;
		}
		
		$harmoni = Harmoni::instance();
	
				//get all comments for site component
		$commentsManager = CommentManager::instance();
		$comments = $commentsManager->getAllComments($siteComponent->getAsset());
		
		while ($comments->hasNext()) {
			$comment = $comments->next();
			$item = $this->addItem(new RSSItem);
			$item->setTitle($comment->getSubject());
			$item->setLink(SiteDispatcher::quickURL("view","html",array("node" => $siteComponent->getId()))."#comment_".$comment->getIdString(), true);
			$item->setPubDate($comment->getModificationDate());
			
			$agentMgr = Services::getService("Agent");
			$agent = $comment->getAuthor();
			$item->setAuthor($agent->getDisplayName());
			
			
			$item->setCommentsLink(SiteDispatcher::quickURL("view","html",array("node" => $siteComponent->getId())));
						
			$pluginMgr = Services::getService("PluginManager");
			$plugin = $pluginMgr->getPlugin($comment->getAsset());
			
			$item->setDescription($plugin->executeAndGetMarkup());
			
			// MediaFile eclosures.
			try {
				foreach ($plugin->getRelatedMediaFiles() as $file) {
					$item->addEnclosure($file->getUrl(), $file->getSize()->value(), $file->getMimeType());
				}
			} catch (UnimplementedException $e) {
			}
		}

	}
	

	
}

?>