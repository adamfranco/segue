<?php
/**
 * @since 10/25/07
 * @package segue.media
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.3 2008/03/26 18:23:18 adamfranco Exp $
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
 * @version $Id: delete.act.php,v 1.3 2008/03/26 18:23:18 adamfranco Exp $
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
			
			$fileAssetId = $fileAsset->getId();
			$contentAsset = $this->getContentAsset();
			
			$repository = $fileAsset->getRepository();
			$repository->deleteAsset($fileAsset->getId());
			
			// Log the success or failure
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log = $loggingManager->getLogForWriting("Segue");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$message = "File deleted with id '".$fileAssetId->getIdString()."'.";

				$item = new AgentNodeEntryItem("Media Library", $message);
				$item->addNodeId($fileAssetId);
				$item->addNodeId($contentAsset->getId());
				
				$idManager = Services::getService("Id");
				$director = AssetSiteDirector::forAsset($contentAsset);
				$site = $director->getRootSiteComponent($contentAsset->getId()->getIdString());
				$item->addNodeId($idManager->getId($site->getId()));
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			$error = ob_get_clean();
			if ($error)
				$this->error($error);
		} catch (Exception $e) {
			$this->error($e->getMessage());
		}
		
		$this->start();
		print $this->getQuota();
		// No content.
		$this->end();
	}
}

?>