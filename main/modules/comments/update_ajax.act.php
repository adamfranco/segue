<?php
/**
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_ajax.act.php,v 1.2 2007/09/04 15:07:42 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(MYDIR."/main/library/Comments/CommentManager.class.php");

/**
 * 
 * 
 * @package segue.comments
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: update_ajax.act.php,v 1.2 2007/09/04 15:07:42 adamfranco Exp $
 */
class update_ajaxAction 
	extends Action
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/11/07
	 */
	function isAuthorizedToExecute () {
		$comment = $this->getComment();
		return $comment->canModify();
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/11/07
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough('node');
		$harmoni->request->startNamespace('comments');
		
		$comment = $this->getComment();
		
		if (RequestContext::value('subject')) {
			$comment->updateSubject(RequestContext::value('subject'));
		}

		
		header("Content-type: text/xml");
		print "<comment>\n";
		print "\t<subject><![CDATA[";
		print $comment->getSubject();
		print "]]></subject>\n";
		
		if (RequestContext::value('threading') == 'threaded')
			$markup = $comment->getMarkup(TRUE);
		else
			$markup = $comment->getMarkup(FALSE);
				
		print "\t<markup><![CDATA[";
		// CDATA sections cannot contain ']]>' and therefor cannot be nested
		// get around this by replacing the ']]>' tags in the markup.
		print preg_replace('/\]\]>/', '}}>', $markup);
		print "]]></markup>\n";
		
	
		print "</comment>";
		
		$harmoni->request->forget('node');
		$harmoni->request->endNamespace();
		
		exit();
	}
	
	/**
	 * Answer the comment object
	 * 
	 * @return object
	 * @access public
	 * @since 7/11/07
	 */
	function getComment () {
		$idManager = Services::getService("Id");
		$commentId = $idManager->getId(
			RequestContext::value('comment_id'));
		$commentManager = CommentManager::instance();
		return $commentManager->getComment($commentId);
	}
}

?>