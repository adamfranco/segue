<?php
/**
 * @since 3/24/08
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AttributionPrinter.class.php,v 1.2 2008/03/25 14:58:02 adamfranco Exp $
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
 * @version $Id: AttributionPrinter.class.php,v 1.2 2008/03/25 14:58:02 adamfranco Exp $
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
		if ($this->shouldShowCreateDate() || $this->shouldShowCreatorName()) {
			print "\n<div class='attribution_line'>";
			print $this->getCreationLine();
			print "</div>";
		}
		
		if ($this->shouldShowModificationDate() || $this->shouldShowEditorName()) {
			print "\n<div class='attribution_line'>";
			print $this->getModificationLine();
			print "</div>";
		}
		
		if ($this->block->showAttribution() == 'all_editors') {
			print "\n<div class='attribution_line'>";
			print $this->getContributorsLine();
			print "</div>";		
		}
		
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
		
		// get author name
		$name = $this->getCreator();
		
		// get creation date and format
		$dateTime = $this->getDateTime($this->block->getCreationDate());
		
		if ($showCreateDate && $showCreatorName) {			
			print "added by ".$name." on ".$dateTime;			
		} else if ($showCreateDate) {
			print "added on ".$dateTime;
		} else if ($showCreatorName) {
			print "added by ".$name;
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
		
		// get last editor name
		$contributors = $this->getEditors();
		if (count($contributors) > 0) 
			$name = $contributors[0];
		else
			$showEditorName = false;
				
		// get modification date and format
		$dateTime = $this->getDateTime($this->block->getModificationDate());
		
		if ($showModificationDate && $showEditorName) {			
			print "updated by ".$name." on ".$dateTime;			
		} else if ($showModificationDate) {
			print "updated on ".$dateTime;
		} else if ($showEditorName) {
			print "updated by ".$name;
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
		print " ";
		
		if ($this->block->showAttribution() === 'all_editors') {	
			$contributors = $this->getEditors();
			print "contributors: ";
			$n = 1;
			foreach ($contributors as $contributor) {	
				print $contributor;
				if ($n < count($contributors)) print "; ";
				$n++;
			}
			
			return;
		}
		
		if ($this->shouldShowCreatorName()) {
			$this->getCreationLine();
			if ($this->shouldShowEditorName())
				print ";";
		}
		if ($this->shouldShowEditorName())
			$this->getModificationLine();
			
	}		


	/**
	 * answer whether or not to show create date
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowCreateDate() {
		return ($this->block->showDates() === 'creation_date' 
					|| $this->block->showDates() === 'both');
	}

	/**
	 * answer whether or not to show creator name
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowCreatorName() {
		return ($this->block->showAttribution() === 'creator' 
					|| $this->block->showAttribution() === 'both');
	}

	/**
	 * answer whether or not to show modification date
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowModificationDate() {
		return ($this->block->showDates() === 'modification_date' 
					|| $this->block->showDates() === 'both');
		
	}

	/**
	 * answer whether or not to show editor name
	 * 
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function shouldShowEditorName() {
		if ($this->block->showAttribution() === 'last_editor')
			return true;
		
		if ($this->block->showAttribution() === 'both') {
			$editors = $this->getEditors();
			$creator = $this->getCreator();
			if (isset($editors[0]) && $editors[0] != $creator) 
				return true;
		}
		
		return false;
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
		
		$creator = $this->getCreator();
		if (!in_array($creator, $contributors)) {
			$contributors[] = $creator;
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
	 * @return string
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