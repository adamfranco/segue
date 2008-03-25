<?php
/**
 * @since 3/24/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: attributionPrinter.class.php,v 1.2 2008/03/25 13:44:07 achapin Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * These methods return date and attribution information about blocks
 * 
 * @since 3/24/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: attributionPrinter.class.php,v 1.2 2008/03/25 13:44:07 achapin Exp $
 */
class AttributionPrinter {
		
	/**
	 * @var object BlockSiteComponent 
	 * @access private
	 * @since 3/24/08
	 */
	private $block = null;

	
	/**
	 * @var object SeguePlugin
	 * @access private
	 * @since 3/24/08
	 */
	private $plugin = null;	

	/**
	 * @var object AgentManager
	 * @access private
	 * @since 3/24/08
	 */
	private $agentManager = null;	

	
	/**
	 * Constructor
	 * 
	 * @param object BlockSiteComponent $block
	 * @return null
	 * @access public
	 * @since 4/3/06
	 */
	function __construct (BlockSiteComponent $block) {
		$pluginManager = Services::getService('PluginManager');
		$this->plugin = $pluginManager->getPlugin($block->getAsset());
 		$this->agentManager = Services::getService("Agent");
		$this->block = $block;
	}
	
	/**
	 * answer the html markup for a block's attribution display
	 * 
	 * @return string attributionMarkup
	 * @access public
	 * @since 3/24/08
	 */
	public function getAttributionMarkUp () {
		
		ob_start();
		print "<div class='attribution_line'>";
		print $this->getCreationLine();
		print "</div>";
		print "<div class='attribution_line'>";
		print $this->getModificationLine();
		print "</div>";
		print "<div class='attribution_line'>";
		print $this->getContributorsLine();
		print "</div>";		
		return ob_get_clean();
	}
	
	/**
	 * answer block creation info
	 * 
	 * @return string
	 * @access private
	 * @since 3/24/08
	 */
	private function getCreationLine () {

		$showCreateDate = $this->shouldShowCreateDate();		
		$showCreatorName = $this->shouldShowCreatorName();
// 		$showCreateDate = true;	
// 		$showCreatorName = true;
		
		// get author name
		$name = $this->getCreator();
	
// 		$agent = $this->agentManager->getAgent($creatorId);	
// 		$name = $agent->getDisplayName();
		
		// get creation date and format
		$dateObject = $this->block->getCreationDate();
		$dateTime = $this->getDateTime($dateObject);
		
		
		if ($showCreateDate && $showCreatorName) {			
			print "added by ".$name." on ".$dateTime;			
		} else if ($showCreateDate) {
			print "added on ".$dateTime;
		} else if ($showCreatorName) {
			print "added by ".$name;
		} else {
			print "";
		}
			
	}

	/**
	 * answer block modification info
	 * 
	 * @return string
	 * @access private
	 * @since 3/24/08
	 */
	private function getModificationLine () {

		$showModificationDate = $this->shouldShowModificationDate();		
		$showEditorName = $this->shouldShowEditorName();
// 		$showModificationDate = true;	
// 		$showEditorName = true;
		
		// get last editor name
		$contributors = $this->getEditors();
		if (count($contributors) > 0) $name = $contributors[0];
				
		// get modification date and format
		$dateObject = $this->block->getModificationDate();
		$dateTime = $this->getDateTime($dateObject);
		

		if (isset($name)) {
			if ($showModificationDate && $showEditorName) {			
				print "updated by ".$name." on ".$dateTime;			
			} else if ($showModificationDate) {
				print "updated on ".$dateTime;
			} else if ($showEditorName) {
				print "updated by ".$name;
			} else {
				print "";
			}
		}
				
	}
	
	/**
	 * answer block contributors info
	 * 
	 * @return string
	 * @access private
	 * @since 3/24/08
	 */
	private function getContributorsLine () {
		
		if ($this->block->showAttribution() === 'all_editors') {	
			$contributors = $this->getEditors();
			print "contributors: ";
			$n = 1;
			foreach ($contributors as $contributor) {	
				print $contributor;
				if ($n < count($contributors)) print "; ";
				$n++;
			}
		}
			
	}		


	/**
	 * answer whether or not to show create date
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowCreateDate() {
		
		 if ($this->block->showDates() === 'creation_date' ||
		 	$this->block->showDates() === 'both') {
		 	return true;
		 } else {
		 	return false;
		 }
		
	}

	/**
	 * answer whether or not to show creator name
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowCreatorName() {
		
		 if ($this->block->showAttribution() === 'creator' ||
		 	$this->block->showAttribution() === 'both') {
		 	return true;
		 } else {
		 	return false;
		 }
		
	}

	/**
	 * answer whether or not to show modification date
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowModificationDate() {
		
		 if ($this->block->showDates() === 'modification_date' || $this->block->showDates() === 'both') {
		 	return true;
		 } else {
		 	return false;
		 }
		
	}

	/**
	 * answer whether or not to show editor name
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowEditorName() {
		
		if ($this->block->showAttribution() === 'last_editor' ||
		 	$this->block->showAttribution() === 'both') {
		 	
			$editors = $this->getEditors();
			$creator = $this->getCreator();
			if ($editors[0] != $creator) return true;
		}		
	}
	
	/**
	 * answer list of editors for a block
	 * 
	 * @return array 
	 * @access private
	 * @since 3/24/08
	 */
	private function getEditors () {
		$contributors = array();
		if ($this->plugin->supportsVersioning()) {
			$versions = $this->plugin->getVersions();
			foreach ($versions as $version)	{			
				$editor = $version->getAgent()->getDisplayName();
				if (!in_array($editor, $contributors)) {
					$contributors[] = $editor;
				}
			}
		}
		return $contributors;
	}

	/**
	 * answer list of editors for a block
	 * 
	 * @return array 
	 * @access private
	 * @since 3/24/08
	 */
	private function getCreator () {
	
		$creatorId = $this->block->getCreator();
		try {
			$agent = $this->agentManager->getAgent($creatorId);	
			$name = $agent->getDisplayName();
		} catch (UnknownIdException $e) {
			return null;
		}
		return $name;
	}

	/**
	 * answer a formated date
	 *
	 * @param object DateAndTime $dateObject
	 * @return strin
	 * @access private
	 * @since 3/24/08
	 */
	private function getDateTime (DateAndTime $dateObject) {
	
		$date = $dateObject->ymdString();
		$time = $dateObject->asTime();
		$time = $time->string12(false);			
		return $date." at ".$time;	
	}
	
}

?>