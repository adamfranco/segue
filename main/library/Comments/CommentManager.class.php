<?php
/**
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentManager.class.php,v 1.4 2007/07/10 14:36:47 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/CommentNode.class.php");

if (!defined('ASC'))
	define('ASC', 'ASC');
	
if (!defined('DESC'))
	define('DESC', 'DESC');


/**
 * The CommentManager is responsible for loading, accessing, and creating comments.
 * The CommentManager is a singleton.
 * 
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentManager.class.php,v 1.4 2007/07/10 14:36:47 adamfranco Exp $
 */
class CommentManager {
		
	/**
	 * Get the instance of the object.
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the object and it is accessed only via the 
	 * CommentManager::instance() method.
	 * 
	 * @return object Harmoni
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	function &instance () {
		if (!defined("COMMENT_MANAGER_INSTANTIATED")) {
			$GLOBALS['__commentManager'] =& new CommentManager();
			define("COMMENT_MANAGER_INSTANTIATED", true);
		}
		
		return $GLOBALS['__commentManager'];
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 7/3/07
	 */
	function CommentManager () {
		$this->mediaFileType =& new Type ('segue', 'edu.middlebury', 'media_file',
				'A file that is uploaded to Segue.');
	}
	
	/**
	 * Create a new root comment attached to an asset
	 * 
	 * @param object Asset $asset The asset this thread is attached to.
	 * @param object Type $type The type of plugin to use.
	 * @return object CommentNode
	 * @access public
	 * @since 7/3/07
	 */
	function &createRootComment ( &$assetOrId, &$type ) {
		if (method_exists($assetOrId, 'getId')) {
			$asset =& $assetOrId;
			$id =& $asset->getId();
		} else {
			$repositoryManager =& Services::getService("Repository");
			$idManager =& Services::getService("Id");
			$repository =& $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			$asset =& $repository->getAsset($assetOrId);
			$id =& $assetOrId;
		}
		
		$commentContainer =& $this->_getCommentContainer($asset);
		
		$repository =& $asset->getRepository();
		$commentAsset =& $repository->createAsset("", "", $type);
		$commentContainer->addAsset($commentAsset->getId());
		$comment =& $this->getComment($commentAsset);
		
		// Clear our order caches
		$this->_rootComments[$id->getIdString()];
		$this->_allComments[$id->getIdString()];
		
		return $comment;
	}
	
	/**
	 * Answer the comment container child of the target asset.
	 * 
	 * @param object Asset $asset
	 * @return object Asset
	 * @access private
	 * @since 7/9/07
	 */
	function &_getCommentContainer ( &$asset ) {
		$commentContainerType = new Type('segue', 'edu.middlebury', 'comment_container', 'A container for Segue Comments');
		$assets =& $asset->getAssets();
		while ($assets->hasNext()) {
			$child =& $assets->next();
			if ($commentContainerType->isEqual($child->getAssetType())) {
				return $child;
			}
		}
		
		// If the comment container doesn't exist, create it.
		$repository =& $asset->getRepository();
		$commentContainerAsset =& $repository->createAsset("Comments", "Comments on the parent Asset", $commentContainerType);
		$asset->addAsset($commentContainerAsset->getId());
		
		return $commentContainerAsset;
	}
	
	/**
	 * Answer a comment object identified by Id or asset
	 * 
	 * @param mixed $assetOrId
	 * @return object CommentNode
	 * @access public
	 * @since 7/3/07
	 */
	function &getComment ( &$assetOrId ) {
		ArgumentValidator::validate($assetOrId, OrValidatorRule::getRule(
			HasMethodsValidatorRule::getRule('getId'),
			HasMethodsValidatorRule::getRule('getIdString')));
		
		if (method_exists($assetOrId, 'getId')) {
			$asset =& $assetOrId;
			$id =& $asset->getId();
		} else {
			$id =& $assetOrId;
		}
		
		// Cache the comment if needed
		if (!isset($this->_comments[$id->getIdString()])) {
			// Get the assset if we were passed an Id only
			if (!isset($asset)) {
				$repositoryManager =& Services::getService("Repository");
				$idManager =& Services::getService("Id");
				$repository =& $repositoryManager->getRepository(
					$idManager->getId("edu.middlebury.segue.sites_repository"));
				$asset =& $repository->getAsset($id);
			}
			
			$this->_comments[$id->getIdString()] =& new CommentNode($asset);
		}
		return $this->_comments[$id->getIdString()];
	}
	
	/**
	 * Answer the top-level comments attached to an asset
	 * 
	 * @param object $assetOrId
	 * @param string $order The constant ASC or DESC for ascending time (oldest 
	 *			first) or decending time (recent first).
	 * @return object Iterator
	 * @access public
	 * @since 7/3/07
	 */
	function &getRootComments ( &$assetOrId, $order = ASC ) {
		if (method_exists($assetOrId, 'getId')) {
			$asset =& $assetOrId;
		} else {
			$repositoryManager =& Services::getService("Repository");
			$idManager =& Services::getService("Id");
			$repository =& $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			$asset =& $repository->getAsset($assetOrId);
		}
		
		// Load the replies, their creation times into arrays for caching and 
		// easy sorting.
		$assetId =& $asset->getId();
		$assetIdString = $assetId->getIdString();
		if (!isset($this->_rootComments[$assetIdString])) {
			$this->_rootComments[$assetIdString] = array();
			$this->_rootComments[$assetIdString]['ids'] = array();
			$this->_rootComments[$assetIdString]['times'] = array();
			
			$commentContainer =& $this->_getCommentContainer($asset);
			$children =& $commentContainer->getAssets();
			
			while ($children->hasNext()) {
				$child =& $children->next();
				$comment =& $this->getComment($child);
				$dateTime =& $comment->getCreationDate();
				$this->_rootComments[$assetIdString]['ids'][] =& $comment->getId();
				$this->_rootComments[$assetIdString]['times'][] = $dateTime->asString();
			}
		}
		
		// Sort the comment Ids based on time.
		array_multisort($this->_rootComments[$assetIdString]['times'], 
			(($order == ASC)?SORT_ASC:SORT_DESC),
			$this->_rootComments[$assetIdString]['ids']);
		
		$null = null;
		$comments = new HarmoniIterator($null);
		foreach ($this->_rootComments[$assetIdString]['ids'] as $id)
			$comments->add($this->getComment($id));
		
		return $comments;
	}
	
	/**
	 * Answer all of the comments attached to an asset
	 * 
	 * @param object $assetOrId
	 * @param string $order The constant ASC or DESC for ascending time (oldest 
	 *			first) or decending time (recent first).
	 * @return object Iterator
	 * @access public
	 * @since 7/3/07
	 */
	function &getAllComments ( &$assetOrId, $order = ASC ) {
		if (method_exists($assetOrId, 'getId')) {
			$asset =& $assetOrId;
			$assetId =& $asset->getId();
		} else {
			$repositoryManager =& Services::getService("Repository");
			$idManager =& Services::getService("Id");
			$repository =& $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			$asset =& $repository->getAsset($assetOrId);
			$assetId =& $assetOrId;
		}
		
		// Load the replies, their creation times into arrays for caching and 
		// easy sorting.
		$assetIdString = $assetId->getIdString();
		if (!isset($this->_allComments[$assetIdString])) {
			$this->_allComments[$assetIdString] = array();
			$this->_allComments[$assetIdString]['ids'] = array();
			$this->_allComments[$assetIdString]['times'] = array();
			
			$rootComments =& $this->getRootComments($asset);
			$allComments =& new MultiIteratorIterator();
			while ($rootComments->hasNext()) {
				$allComments->addIterator(
					$this->_getDescendentComments($rootComments->next()));
			}
			
			while ($allComments->hasNext()) {
				$comment =& $allComments->next();
				$dateTime =& $comment->getCreationDate();
				$this->_allComments[$assetIdString]['ids'][] =& $comment->getId();
				$this->_allComments[$assetIdString]['times'][] = $dateTime->asSeconds();
			}
		}
		
		// Sort the comment Ids based on time.
		array_multisort($this->_allComments[$assetIdString]['times'], 
			SORT_NUMERIC, (($order == ASC)?SORT_ASC:SORT_DESC), $this->_allComments[$assetIdString]['ids']);
		
		$null = null;
		$comments = new HarmoniIterator($null);
		foreach ($this->_allComments[$assetIdString]['ids'] as $id)
			$comments->add($this->getComment($id));
		
		return $comments;
	}
	
	/**
	 * Answer all comments below and including a comment
	 * 
	 * @param object Comment $comment
	 * @return object Iterator
	 * @access public
	 * @since 7/3/07
	 */
	function &_getDescendentComments ( &$comment ) {
		ArgumentValidator::validate($comment, ExtendsValidatorRule::getRule('CommentNode'));
		
		$thisComment = array();
		$thisComment[] =& $comment;	
		$decendents =& new MultiIteratorIterator();
		$decendents->addIterator(new HarmoniIterator($thisComment));
		
		$children =& $comment->getReplies();			
		while ($children->hasNext()) {
			$child =& $children->next();
			$decendents->addIterator($this->_getDescendentComments($child));
		}
		
		return $decendents;
	}
	
	/**
	 * Answer the interface markup needed to display the comments attached to the
	 * given asset.
	 * 
	 * @param object Asset $asset
	 * @return string
	 * @access public
	 * @since 7/3/07
	 */
	function getMarkup ( &$asset ) {
		if (RequestContext::value('order'))
			$this->setDisplayOrder(RequestContext::value('order'));
		
		if (RequestContext::value('displayMode'))
			$this->setDisplayMode(RequestContext::value('displayMode'));
		
		if (RequestContext::value('create_new_comment')) {
			$comment =& $this->createRootComment(Type::fromString(RequestContext::value('new_comment_type')));
			$comment->enableEditForm();
		}
		$this->addHead();
		
		ob_start();
		
		// print the ordering form
		print "\n\n<form action='#' method='post'>";
		
		print "\n\t<div style='float: left;'>";
		print "\n\t<input type='button' name='".RequestContext::name('create_new_comment')."' value='"._('Create New')."'";
		print " onclick='this.form.action = this.form.action.replace(/#.*/, \"#current_comment\"); this.form.submit();'";
		print "/>";
		
		print "\n\t\t<select name='".RequestContext::name('new_comment_type')."'/>";
		$pluginManager =& Services::getService('PluginManager');
		$plugins = $pluginManager->getEnabledPlugins();
		foreach ($plugins as $key => $pType) {
			print "\n\t\t\t<option value='".$key."'>".$pType->getKeyword()."</option>";
		}
		print "\n\t\t</select> ";
		print _('Comment');
		print "\n\t</div>";
		
		print "\n\t<div style='float: right; text-align: right;'>";
		
		print "\n\t\t<select name='".RequestContext::name('displayMode')."'/>";
		print "\n\t\t\t<option value='threaded'".(($this->getDisplayMode() == 'threaded')?" selected='selected'":"").">";
		print _("Threaded")."</option>";
		print "\n\t\t\t<option value='flat'".(($this->getDisplayMode() == 'flat')?" selected='selected'":"").">";
		print _("Flat")."</option>";
		print "\n\t\t</select>";
		
		print "\n\t\t<select name='".RequestContext::name('order')."'/>";
		print "\n\t\t\t<option value='".ASC."'".(($this->getDisplayOrder() == ASC)?" selected='selected'":"").">";
		print _("Oldest First")."</option>";
		print "\n\t\t\t<option value='".DESC."'".(($this->getDisplayOrder() == DESC)?" selected='selected'":"").">";
		print _("Newest First")."</option>";
		print "\n\t\t</select>";
		
		print "\n\t<input type='submit' value='"._("Change")."'/>";
		
		print "\n\t</div>";
		
		print "\n\t<div style='clear: both;'> &nbsp; </div>";
		
		print "\n</form>";
		
		// Print out the Comments
		print "\n<div id='".RequestContext::name('comments')."'>";
		if ($this->getDisplayMode() == 'flat') {
			$comments =& $this->getAllComments($asset, $this->getDisplayOrder());
		} else {
			$comments =& $this->getRootComments($asset, $this->getDisplayOrder());
		}
		
		while ($comments->hasNext()) {
			$comment =& $comments->next();
			print $comment->getMarkup(($this->getDisplayMode() == 'threaded')?true:false);
		}
		
		print "\n</div>";
		
		return ob_get_clean();
	}
	
	/**
	 * Set the display mode
	 * 
	 * @param string $mode
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function setDisplayMode ($mode) {
		if ($mode == 'flat' || $mode == 'threaded') {
			$_SESSION['comment_display_mode'] = $mode;
		}
	}
	
	/**
	 * Answer the display mode
	 * 
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getDisplayMode () {
		if (isset($_SESSION['comment_display_mode'])) {
			return $_SESSION['comment_display_mode'];
		} else {
			return 'threaded';
		}
	}
	
	/**
	 * Set the ordering of comments
	 * 
	 * @param string $mode
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function setDisplayOrder ($order) {
		if ($order == ASC || $order == DESC) {
			$_SESSION['comment_display_order'] = $order;
		}
	}
	
	/**
	 * Answer the display order
	 * 
	 * @return string
	 * @access public
	 * @since 7/5/07
	 */
	function getDisplayOrder () {
		if (isset($_SESSION['comment_display_order'])) {
			return $_SESSION['comment_display_order'];
		} else {
			return ASC;
		}
	}
	
	/**
	 * Add head Styles
	 * 
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	function addHead () {
		$harmoni =& Harmoni::instance();
		$outputHandler =& $harmoni->getOutputHandler();
		ob_start();
		print $outputHandler->getHead();
		
		print <<< END
		
		<style type='text/css'>
			img.reply_icon {
				float: left;
				clear: left;
			}
			
			.comment_title {
				font-weight: bold;
				border-top: 1px solid;
				margin-top: 5px;
			}
			
			.comment_replies {
				padding-left: 10px;
			}
			
			.comment_reply {
			 	margin-left: 15px;
			}
			
			.comment_byline {
				font-size: smaller;
			}
		</style>
		
END;
		$outputHandler->setHead(ob_get_clean());
	}
}

?>