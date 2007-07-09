<?php
/**
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentNode.class.php,v 1.2 2007/07/09 20:06:44 adamfranco Exp $
 */ 

/**
 * A CommentNode is an asset that may contain comments. The root of a comment-hierarchy
 * is a CommentNode, but not a Comment itself. Comments extend CommentNodes. CommentNode
 * provides access to authorization and settings for Commenting as well as child comments.
 * 
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentNode.class.php,v 1.2 2007/07/09 20:06:44 adamfranco Exp $
 */
class CommentNode {
		
	/**
	 * Constructor
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 6/7/07
	 */
	function CommentNode ( &$asset ) {
		$this->_asset =& $asset;
		$this->_enableEditForm = false;
	}
	
	/**
	 * Answer the Id.
	 * 
	 * @return object Id
	 * @access public
	 * @since 7/3/07
	 */
	function &getId () {
		return $this->_asset->getId();
	}
	
	/**
	 * Answer the id string
	 * 
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getIdString () {
		$id =& $this->getId();
		return $id->getIdString();
	}
	
	/**
	 * Answer the date this comment was posted
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/3/07
	 */
	function &getCreationDate () {
		return $this->_asset->getCreationDate();
	}
	
	/**
	 * Answer the date that the comment was modified
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/3/07
	 */
	function &getModificationDate () {
		return $this->_asset->getModificationDate();
	}
	
	/**
	 * Answer the subject of the comment.
	 * 
	 * @return string
	 * @access public
	 * @since 7/3/07
	 */
	function getSubject () {
		return $this->_asset->getDisplayName();
	}
	
	/**
	 * Answer the comment body.
	 * 
	 * @return string
	 * @access public
	 * @since 7/3/07
	 */
	function getBody () {
		// Only return a body if we are authorized to view the comment
		$azManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		if ($azManager->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.view_comments"),
			$this->getId()))
		{
			$pluginManager =& Services::getService('PluginManager');
			$plugin =& $pluginManager->getPlugin($this->_asset);
			
			// We've just checked our view permission, so use true
			$plugin->setCanViewFunction(create_function('$plugin', 'return true;'));
			
			// If we are authorized to comment, are the comment author, and there are
			// no replies yet, allow us to edit the comment
			if ($azManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.comment"),
					$this->getId())
				&& $this->isAuthor()
				&& $this->numReplies() === 0)
			{
				$plugin->setCanModifyFunction(create_function('$plugin', 'return true;'));
				return $plugin->executeAndGetMarkup(true);
			} else {
				$plugin->setCanModifyFunction('$plugin', 'return false;');
				return $plugin->executeAndGetMarkup(false);
			}
		} else {
			return _("You are not authorized to view this comment.");
		}
	}
	
	/**
	 * Answer the number of replies to this comment
	 * 
	 * @return integer
	 * @access public
	 * @since 7/3/07
	 */
	function numReplies () {
		$replies =& $this->getReplies();
		return $replies->count();
	}
	
	/**
	 * Answer the replies in ascending or descending time.
	 * 
	 * @param string $order The constant ASC or DESC for ascending time (oldest 
	 *			first) or decending time (recent first).
	 * @return iterator
	 * @access public
	 * @since 7/3/07
	 */
	function &getReplies ( $order = ASC ) {
		// Load the replies, their creation times into arrays for caching and 
		// easy sorting.
		if (!isset($this->_replies)) {
			$this->_replyIds = array();
			$this->_replyTimes = array();
			
			$mediaFileType =& new Type ('segue', 'edu.middlebury', 'media_file',
				'A file that is uploaded to Segue.');
				
			$children =& $this->_asset->getAssets();
			
			while ($children->hasNext()) {
				$child =& $children->next();
				if (!$mediaFileType->isEqual($child->getAssetType())) {
					$dateTime =& $child->getCreationDate();
					$this->_replyIds[] =& $child->getId();
					$this->_replyTimes[] = $dateTime->asString();
				}
			}
		}
		
		// Sort the reply Ids based on time.
		array_multisort($this->_replyIds, $this->_replyTimes, 
			(($order == ASC)?SORT_ASC:SORT_DESC));
		
		$null = null;
		$replies = new HarmoniIterator($null);
		$commentManager =& CommentManager::instance();
		foreach ($this->_replyIds as $id) {
			$replies->add($commentManager->getComment($id));
		}
		
		return $replies;
	}
	
	/**
	 * Flag the edit form to be displayed
	 * 
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function enableEditForm () {
		$this->_enableEditForm = true;
	}
	
	/**
	 * Answer the Agent that represents the author of the comment.
	 * 
	 * @return object Agent
	 * @access public
	 * @since 7/5/07
	 */
	function &getAuthor () {
		$agentManager =& Services::getService('Agent');
		
		if ($this->_asset->getCreator()) {
			return $agentManager->getAgent($this->_asset->getCreator());
		} else {
			$idManager =& Services::getService('Id');
			return $agentManager->getAgent($idManager->getId('edu.middlebury.agents.anonymous'));
		}
	}
	
	/**
	 * Answer true if the current user is the author of the comment
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/5/07
	 */
	function isAuthor () {
		$author =& $this->getAuthor();
		$authorId =& $author->getId();
		
		$idManager =& Services::getService('Id');
		$anonId =& $idManager->getId('edu.middlebury.agents.anonymous');
		if ($anonId->isEqual($authorId))
			return false;
		
		$authN =& Services::getService("AuthN");
		$agentM =& Services::getService("Agent");
		$authTypes =& $authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			if ($authorId->isEqual($authN->getUserId($authType))) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer the markup for this comment.
	 * 
	 * @param boolean $showThreadedReplies
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getMarkup ($showThreadedReplies) {
		ob_start();
		print "\n\t<div class='comment' id='".$this->getIdString()."'>";
		
		print "\n\t\t<div class='comment_display'>";
		print "\n\t\t\t<div class='comment_title'>";
		print $this->getSubject();
		print "\n\t\t\t</div>";
		
		print "\n\t\t\t<div class='comment_byline'>";
		$author =& $this->getAuthor();
		$date =& $this->getCreationDate();
		$dateString = $date->dayOfWeekName()." ".$date->monthName()." ".$date->dayOfMonth().", ".$date->year();
		$time =& $date->asTime();
		print str_replace('%1', $author->getDisplayName(),
				str_replace('%2', $dateString,
					str_replace('%3', $time->string12(),
						_("by %1 on %2 at %3"))));
		print "\n\t\t\t</div>";
		
		print "\n\t\t\t<div class='comment_body'>";
		print $this->getBody();
		print "\n\t\t\t</div>";
		print "\n\t\t</div>";
		
				
		if ($showThreadedReplies) {
			print "\n\t\t<div class='comment_replies'>";
			
			$replies =& $this->getReplies(ASC);
			while ($replies->hasNext()) {
				$reply =& $replies->next();
				print "\n\t\t\t\t<img src='".MYPATH."/icons/reply_indent.png' class='reply_icon'/>";
				print "\n\t\t\t<div class='comment_reply'>";
				print $reply->getMarkup(true);
				
				print "\n\t\t\t</div>";
			}
			
			print "\n\t\t</div>";
		}
		
		print "\n\t</div>";
		return ob_get_clean();
	}
}

?>