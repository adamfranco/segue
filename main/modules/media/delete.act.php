<?php
/**
 * @since 10/25/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.1 2007/10/25 14:06:50 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/update.act.php");

/**
 * Delete a media asset.
 * 
 * @since 10/25/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.1 2007/10/25 14:06:50 adamfranco Exp $
 */
class deleteAction
	extends updateAction
{
		
	/**
	 * Answer the authorization function used for this action
	 * 
	 * @return object Id
	 * @access protected
	 * @since 10/25/07
	 */
	protected function getAuthorizationFunction () {
		$idManager = Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.delete");
	}
	
	/**
	 * Process the changes and build the output
	 * 
	 * @return void
	 * @access public
	 * @since 10/25/07
	 */
	public function buildContent () {
		try {
			ob_start();
			
			$fileAsset = $this->getFileAsset();
			$repository = $fileAsset->getRepository();
			$repository->deleteAsset($fileAsset->getId());
			
			$error = ob_get_clean();
			if ($error)
				$this->error($error);
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
		
		$this->start();
		// No content.
		$this->end();
	}
}

?>