<?php
/**
 * @since 10/25/07
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsTemplate.abstract.php,v 1.5 2008/01/25 18:47:03 adamfranco Exp $
 */ 

//require_once(dirname(__FILE__)."/SeguePluginsDriver.abstract.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/modules/participation/ParticipationView.class.php");

/**
 * This class includes all of the methods that SeguePlugins can
 * or must override. All SeguePlugins must extend this class, but they should directly
 * extend either SeguePlugin or SegueAjaxPlugin.
 *
 * For a list of SeguePlugins API methods that SeguePlugins are allowed to make
 * use of, please see the SeguePluginsAPI.abstract.php
 * 
 * @since 10/25/07
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsTemplate.abstract.php,v 1.5 2008/01/25 18:47:03 adamfranco Exp $
 */
class EduMiddleburyParticipationPlugin
	extends SeguePlugin
{

	
/*********************************************************
 * Instance Methods - API - Override in Children
 *
 * Override these methods to implement the functionality of
 * a plugin.
 *********************************************************/
 	
 	/**
 	 * Answer a description of the the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	public static function getPluginDescription () {
 		return _("The Participation list plugin allows users to display a list of all the participants and members of a site.");
 	}
 	
 	/**
 	 * Answer a display name for the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginDisplayName () {
 		return _("Participation");
 	}
 	
 	/**
 	 * Answer an array of the creators of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return array of strings
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginCreators () {
 		return array("Alex Chapin");
 	}
 	
 	/**
 	 * Answer the version of the plugin.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersion () {
 		return '1.0';
 	}
 	
 	/**
 	 * Answer the latest version of the plugin available. Null if no version information
 	 * is available.
 	 * 
 	 * @return mixed a string or null
 	 * @access public
 	 * @since 12/19/07
 	 * @static
 	 */
 	public static function getPluginVersionAvailable () {
 		return null;
 	}
 	
 	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.  This is where you would make more complex data that your 
 	 * plugin needs.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function initialize () {
		$this->_node = SiteDispatcher::getCurrentRootNode();
 		$this->_view = new Participation_View($this->_node);
 		$this->_printedParticipants = array();
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function update ( $request ) {
 		// Override as needed.
 	}
 	
 	/**
 	 * Return the markup that represents the plugin.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/12/06
 	 */
 	public function getMarkup () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");		
		
		// check if user is editor and thus can see link to participant information panel
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			SiteDispatcher::getCurrentNode()->getQualifierId()))
		{
			$this->_showTrackLink = true;
		} else {
			$this->_showTrackLink = false;
		}
			
		ob_start();
		
		// get all site members
		$group = $this->_node->getMembersGroup();
		
		// Direct members of the group
		$title = "<div class='participant_header'>"._("Site Members")."</div>";
		print $this->printMemberIterator($group->getMembers(false), $title);
		
		// Members of sub-groups
		$subgroups = $group->getGroups(false);
		
		while ($subgroups->hasNext()) {
			$subgroup = $subgroups->next();
			$title = "<div class='participant_group_header'>".$subgroup->getDisplayName()."</div>";
			print $this->printMemberIterator($subgroup->getMembers(false), $title);
		}
		
		// Other Participants
		$notPrintedParticipants = array();
		foreach ($this->_view->getParticipants() as $participant) {
			if (!in_array($participant->getId()->getIdString(), $this->_printedParticipants)) {
				$notPrintedParticipants[] = $participant;
			}
		}
		$title = "<br/><div class='participant_header'>"._("Other Participants")."</div>";
		print $this->printParticipants($notPrintedParticipants, $title);

 		return ob_get_clean();
 	}
 	
 	/**
 	 * Print out the members in an iterator
 	 * 
 	 * @param object $groupMembers
 	 * @param string $title
 	 * @return string
 	 * @access protected
 	 * @since 2/18/09
 	 */
 	protected function printMemberIterator ($groupMembers, $title) {
 		$members = array();
		while ($groupMembers->hasNext()) {
			$members[] = $groupMembers->next();
		}
		return $this->printParticipants($members, $title);
 	}
 	
 	/**
 	 * Print out an array of Agents or Particapnts
 	 * 
 	 * @param array $participants Agent objects or Particpation_Particpant objects
 	 * @param string $title
 	 * @return string
 	 * @access public
 	 * @since 2/18/09
 	 */
 	public function printParticipants (array $participants, $title) {
 		ob_start();
 		
 		$sortKeys = array();	
		foreach ($participants as $participant) {
			$sortKeys[] = $participant->getDisplayName();			
		}
		
		array_multisort($sortKeys, array_keys($participants), SORT_ASC, $participants);
		
		print $title;
		
		foreach ($participants as $participant) {
			print $this->printParticipant ($participant);
			$this->_printedParticipants[] = $participant->getId()->getIdString();
		}
		
		return ob_get_clean();
 	}
 	
 	/**
 	 * print out participant
 	 * 
 	 * @param object $agent
 	 * @return string
 	 * @access public
 	 * @since 2/18/09
 	 */
 	public function printParticipant ($participant) { 
 		$harmoni = Harmoni::instance();
 		$harmoni->request->startNamespace(null);
 		ob_start();
 		
		print "<div class='participant_list'>";
		
		// show link to more info only if authenticated user is an editor
		if ($this->_showTrackLink == true) {
			$trackUrl = SiteDispatcher::quickURL('participation','actions', 
			array('node' => $this->_node->getId(), 'participant' => $participant->getId()->getIdString()));				

			//print "<a href='";			
			print "<a target='_blank' href='".$trackUrl."'";
			print ' onclick="';
			print "var url = '".$trackUrl."'; ";
			print "window.open(url, 'site_map', 'width=500,height=600,resizable=yes,scrollbars=yes'); ";
			print "return false;";
			print '"';
			print ">".$participant->getDisplayName()."</a>";
		} else {
			print $participant->getDisplayName();
		}
		print "</div>";

		$harmoni->request->endNamespace();		
 		return ob_get_clean();		
 	}
 	
 	/**
 	 * Return the markup that represents the plugin in and expanded form.
 	 * This method will be called when looking at a "detail view" of the plugin
 	 * where the representation of the plugin will be the focus of the page
 	 * rather than just one of many elements.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedMarkup () {
		return $this->getMarkup();
 	}
 	
 	/**
 	 * Answer the label to use when linking to the plugin's extented markup.
 	 * For a text-based plugin this may be the default, 'read more >>', for
 	 * an image plugin it might be something like "Large View", etc.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/23/07
 	 */
 	public function getExtendedLinkLabel () {
 		return _("details &raquo;");
 	}
 	
 	/**
 	 * Generate a plain-text or HTML description string for the plugin instance.
 	 * This may simply be a stored 'raw description' string, it could be generated
 	 * from other content in the plugin instance, or some combination there-of.
 	 * Override this method in your plugin as needed.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 5/22/07
 	 */
 	public function generateDescription () {
 		return $this->getRawDescription();
 	}
 	
 	/**
 	 * Answer true if this instance of a plugin 'has content'. This method is called
 	 * to determine if the plugin instance is ready to be 'published' or is a newly-created
 	 * placeholder awaiting content addition. If the plugin has no appreciable 
 	 * difference between have content or not, this method should return true. For
 	 * example: an interactive calendar plugin should probably be 'published' 
 	 * whether or not events have been added to it.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 7/13/07
 	 */
 	public function hasContent () {
 		// Override as needed
 		return true;
 	}
 	
 	/**
 	 * Answer an array of MediaFiles that should be referenced along with the plugin
 	 * representation in RSS feed enclosures or other similar uses.
 	 *
 	 * Throw an UnimplementedException if not implemented.
 	 * 
 	 * @return array of MediaFile objects
 	 * @access public
 	 * @since 8/27/08
 	 */
 	public function getRelatedMediaFiles () {
 		// Override if supported.
 		throw new UnimplementedException();
 	}
 	
 	/*********************************************************
 	 * The following three methods allow plugins to work within
 	 * the "Segue Classic" user interface.
 	 *
 	 * If plugins do not support the wizard directly, then their
 	 * markup with 'show controls' enabled will be put directly 
 	 * in the wizard.
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin natively supports editing via wizard components.
 	 * Override to return true if you implement the getWizardComponent(), 
 	 * and updateFromWizard() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 5/9/07
 	 */
 	public function supportsWizard () {
 		return false;
 	}
 	/**
 	 * Return the a {@link WizardComponent} to allow editing of your
 	 * plugin in the Wizard.
 	 * 
 	 * @return object WizardComponent
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function getWizardComponent () {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
 	
 	/**
 	 * Update the component from an array of values
 	 * 
 	 * @param array $values
 	 * @return void
 	 * @access public
 	 * @since 5/8/07
 	 */
 	public function updateFromWizard ( $values ) {
 		print "<p>Override ".__CLASS__."::".__FUNCTION__."() to enable editing of your pluggin in Segue Classic Mode.</p>";
 	}
 	
 	/*********************************************************
 	 * The following methods are used to support versioning of
 	 * the plugin instance
 	 *********************************************************/
 	/**
 	 * Answer true if this plugin supports versioning. 
 	 * Override to return true if you implement the exportVersion(), 
 	 * and applyVersion() methods.
 	 * 
 	 * @return boolean
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function supportsVersioning () {
 		return false;
 	}
 	
 	/**
 	 * Answer a DOMDocument representation of the current plugin state.
 	 *
 	 * @return DOMDocument
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function exportVersion () {
 		throw new UnimplementedException();
 	}
 	
 	/**
 	 * Update the plugin state to match the representation passed in the DOMDocument.
 	 * The DOM Element passed will have been exported using the exportVersion() method.
 	 *
 	 * Do not mark a new version in the implementation of this method. If necessary this
 	 * will be done by the driver.
 	 * 
 	 * @param object DOMDocument $version
 	 * @return void
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function applyVersion (DOMDocument $version) {
 		throw new UnimplementedException();
 	}
	
	/**
 	 * Answer a string of XHTML markup that displays the plugin state representation
 	 * in the DOMDocument passed. This markup will be used in displaying a version history.
 	 * The DOM Element passed will have been exported using the exportVersion() method.
 	 * 
 	 * @param object DOMDocument $version
 	 * @return string
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function getVersionMarkup (DOMDocument $version) {
 		throw new UnimplementedException();
 	}
 	
 	/**
 	 * Answer a difference between two versions. Should return an XHTML-formatted
 	 * list or table of differences.
 	 * 
 	 * @param object DOMDocument $oldVersion
 	 * @param object DOMDocument $newVersion
 	 * @return string
 	 * @access public
 	 * @since 1/7/08
 	 */
 	public function getVersionDiff (DOMDocument $oldVersion, DOMDocument $newVersion) {
 		throw new UnimplementedException();
 	}
 	
 	/*********************************************************
 	 * The following methods are needed to support restoring
 	 * from backups and importing/exporting plugin data.
 	 *********************************************************/
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings.
 	 * Update any of the old Ids that this plugin instance recognizes to their
 	 * new value.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
 	public function replaceIds (array $idMap) {
 		throw new UnimplementedException();
 	}
 	
 	/**
 	 * Given an associative array of old Id strings and new Id strings.
 	 * Update any of the old Ids in ther version XML to their new value.
 	 * This method is only needed if versioning is supported.
 	 * 
 	 * @param array $idMap An associative array of old id-strings to new id-strings.
 	 * @param object DOMDocument $version
 	 * @return void
 	 * @access public
 	 * @since 1/24/08
 	 */
 	public function replaceIdsInVersion (array $idMap, DOMDocument $version) {
 		throw new UnimplementedException();
 	}
}

?>