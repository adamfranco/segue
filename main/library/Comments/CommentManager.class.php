<?php
/**
 * @since 6/7/07
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CommentManager.class.php,v 1.11 2007/07/13 19:59:03 adamfranco Exp $
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
 * @version $Id: CommentManager.class.php,v 1.11 2007/07/13 19:59:03 adamfranco Exp $
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
		$this->_comments = array();
		$this->_allComments = array();
		$this->_rootComments = array();
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
		$commentAsset =& $repository->createAsset(_("(untitled)"), "", $type);
		$commentContainer->addAsset($commentAsset->getId());
		$comment =& $this->getComment($commentAsset);
		
		// Clear our order caches
		unset($this->_rootComments[$id->getIdString()]);
		unset($this->_allComments[$id->getIdString()]);
		
		return $comment;
	}
	
	/**
	 * Create a reply to a comment
	 * 
	 * @param object Type $type
	 * @return object CommentNode
	 * @access public
	 * @since 7/12/07
	 */
	function &createReply ( &$parentId, &$type) {
		$parent =& $this->getComment($parentId);
		
		$repository =& $parent->_asset->getRepository();
		$replyAsset =& $repository->createAsset(_("(untitled)"), "", $type);
		$parent->_asset->addAsset($replyAsset->getId());
		$reply =& $this->getComment($replyAsset);
		
		// Clear our order caches
		unset($this->_allComments);
		
		return $reply;
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
	 * Delete a comment
	 * 
	 * @param object Id $id
	 * @return void
	 * @access public
	 * @since 7/12/07
	 */
	function deleteComment ( &$id ) {
		$comment =& $this->getComment($id);
		$asset =& $comment->_asset;
		$repository =& $asset->getRepository();
		$repository->deleteAsset($id);
		
		unset($this->_comments[$id->getIdString()]);
		unset($this->_rootComments);
		unset($this->_allComments);
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
	 * Answer the Heading for discussions
	 * 
	 * @return string
	 * @access public
	 * @since 7/10/07
	 */
	function getHeadingMarkup ( &$asset ) {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough('node');
		$harmoni->request->startNamespace('comments');
		ob_start();
		
		print _("Comments:");
		print "<a name='".RequestContext::name('top')."'></a>";
		
		$harmoni->request->forget('node');
		$harmoni->request->endNamespace();
		return ob_get_clean();
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
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough('node');
		$harmoni->request->startNamespace('comments');
		
		if (RequestContext::value('order'))
			$this->setDisplayOrder(RequestContext::value('order'));
		
		if (RequestContext::value('displayMode'))
			$this->setDisplayMode(RequestContext::value('displayMode'));
		
		if (RequestContext::value('create_new_comment')) {
			$comment =& $this->createRootComment($asset, Type::fromString(RequestContext::value('plugin_type')));
			$comment->updateSubject(RequestContext::value('title'));
			$comment->enableEditForm();
		}
		
		if (RequestContext::value('reply_parent') && RequestContext::value('plugin_type')) {
			$idManager =& Services::getService('Id');
			$comment =& $this->createReply(
				$idManager->getId(RequestContext::value('reply_parent')),
				Type::fromString(RequestContext::value('plugin_type')));
			$comment->updateSubject(RequestContext::value('title'));
			$comment->enableEditForm();
		}
		
		if (RequestContext::value('delete_comment')) {
			$idManager =& Services::getService('Id');
			$this->deleteComment($idManager->getId(RequestContext::value('delete_comment')));
		}
		
		$this->addHead();
		
		ob_start();
		
		// New comment
		print "\n<div style='float: left;'>";
		$url =& $harmoni->request->mkURL();
		$url->setValue('create_new_comment', 'true');
		print "\n\t<button ";
		print "onclick=\"CommentPluginChooser.run(this, '".$url->write()."#".RequestContext::name('current')."', ''); return false;\">";
		print _("New Comment")."</button>";
		print "\n</div>";
		
		// print the ordering form
		print "\n\n<form action='".$harmoni->request->quickURL()."#".RequestContext::name('top')."' method='post'  style='float: right; text-align: right;'>";

		
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
		
		print "\n</form>";
		
		print "\n<div style='clear: both;'> &nbsp; </div>";
		
		
		
		// Print out the Comments
		print "\n<div id='".RequestContext::name('comments')."'>";
		if ($this->getDisplayMode() == 'flat') {
			$comments =& $this->getAllComments($asset, $this->getDisplayOrder());
		} else {
			$comments =& $this->getRootComments($asset, $this->getDisplayOrder());
		}
		
		while ($comments->hasNext()) {
			$comment =& $comments->next();
			// If this is a work in progress that has not had content added yet, 
			// do not display it.
			if ($comment->hasContent() || $comment->isAuthor()) {
				print $comment->getMarkup(($this->getDisplayMode() == 'threaded')?true:false);
			}
		}
		
		print "\n</div>";
		
		$harmoni->request->forget('node');
		$harmoni->request->endNamespace();
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
		
		$subjectField = RequestContext::name('subject');
		$commentIdField = RequestContext::name('comment_id');
		print <<< END
		
		<style type='text/css'>
			img.reply_icon {
				float: left;
				clear: left;
			}
			
			.comment {
				border-top: 1px solid;
				margin-top: 5px;
			}
			
			.comment_controls {
				float: right;
				text-align: right;
			}
			
			.comment_title {
				font-weight: bold;
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
		
		<script type='text/javascript'>
		// <![CDATA[
			
			function updateCommentSubject ( form, dest ) {
				var subject = form.$subjectField.value;
				var comment_id = form.$commentIdField.value;
				
				dest.innerHTML = subject;
				var params = {
					'subject': escape(subject), 
					'comment_id': escape(comment_id)
					};
					
				var url = Harmoni.quickUrl('comments', 'update_ajax', params, 'comments');
				var req = Harmoni.createRequest();
				req.onreadystatechange = function () {
					// only if req shows "loaded"
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200) {
// 							alert(req.responseText);
						} else {
							throw new Error("There was a problem retrieving the XML data: " +
								req.statusText);
						}
					}
				} 
				
				req.open("GET", url, true);
				req.send(null);
			}
			
		// ]]>
		</script>
		
END;
		// Add our common Harmoni javascript libraries
		require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");
		
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/CenteredPanel.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/TabbedContent.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/prototype.js'></script>";
		print "\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/js_quicktags.js'></script>";
		
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/PluginChooser.js'></script>";
		print "\n\t\t<link rel='stylesheet' type='text/css' href='".MYPATH."/javascript/PluginChooser.css'/>";
		print "\n\t\t<script type='text/javascript' src='".MYPATH."/javascript/CommentPluginChooser.js'></script>";
		
		$outputHandler->setHead(ob_get_clean());
	}
}

?>