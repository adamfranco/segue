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
	extends SegueAjaxPlugin
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
 		$this->_printedParticipants = array();
 	}

 	/**
 	 * Answer the node
 	 * 
 	 * @return SiteComponent
 	 * @access private
 	 * @since 2/25/09
 	 */
 	private function getNode () {
 		if (!isset($this->_node))
 			$this->_node = SiteDispatcher::getCurrentRootNode();
 		return $this->_node;
 	}
 	
 	/**
 	 * Answer the view
 	 * 
 	 * @return Participation_View
 	 * @access private
 	 * @since 2/25/09
 	 */
 	private function getView () {
 		if (!isset($this->_view))
 			$this->_view = new Participation_View($this->getNode());
 		return $this->_view;
 	}
 	 	
 	/**
	 * Write an option
	 * 
	 * @param string $groupId
	 * @return void
	 * @access protected
	 * @since 2/4/09
	 */
	protected function getOptions () {
		$doc = new Harmoni_DOMDocument();
		$doc->preserveWhiteSpace = false;
		try {
			$doc->loadXML($this->getContent());
		} catch (DOMException $e) {
			$doc->appendChild($doc->createElement('options'));
		}
		
		if (!$doc->documentElement->nodeName == 'options')
			throw new OperationFailedException('Expection root-node "options", found "'.$doc->documentElement->nodeName.'".');
			
		return $doc;
	}
	
	/**
	 * Add a group to the list of groups to not show members
	 * 
	 * @param string $groupIdString
	 * @return void
	 * @access protected
	 * @since 2/4/09
	 */
	protected function addMembersHiddenGroup ($groupIdString) {
		// The options will look like:
		/*
		<options>
			<groupMembersHidden>
				<groupId>12345654</groupId>
				<groupId>1234sdrf4</groupId>
				<groupId>asdfsdf5654</groupId>
			</groupMemversHidden>
			<mergeMembers>all</mergeMembers>
			...
		</options>
		*/
		$doc = $this->getOptions();
		$xpath = new DOMXPath($doc);
		
		$elements = $xpath->query('/options/groupMembersHidden');
		if ($elements->length)
			$groupMembersHiddenElement = $elements->item(0);
		else
			$groupMembersHiddenElement = $doc->documentElement->appendChild($doc->createElement('groupMembersHidden'));

		$elements = $xpath->query('/options/groupMembersHidden/groupId[.="'.$groupIdString.'"]');

		if ($elements->length)
			return;
		
		$element = $groupMembersHiddenElement->appendChild($doc->createElement('groupId'));
		$element->nodeValue = $groupIdString;
		
		$this->setContent($doc->saveXMLWithWhitespace());
	}

	/**
	 * Add a group to the list of groups to not show members
	 * 
	 * @param string $groupIdString
	 * @return void
	 * @access protected
	 * @since 2/4/09
	 */
	protected function removeMembersHiddenGroup ($groupIdString) {

		$doc = $this->getOptions();
		$xpath = new DOMXPath($doc);
			
		$elements = $xpath->query('/options/groupMembersHidden/groupId[.="'.$groupIdString.'"]');
		
		foreach ($elements as $element) {
			$element->parentNode->removeChild($element);
		}
		
		$this->setContent($doc->saveXMLWithWhitespace());
	}

	/**
	 * Add a group to the list of groups to not show members
	 * 
	 * @return array
	 * @access protected
	 * @since 2/4/09
	 */
	protected function getMembersHiddenGroups () {
		
		$doc = $this->getOptions();
		$xpath = new DOMXPath($doc);
		
		$elements = $xpath->query('/options/groupMembersHidden/groupId');
		$groupIds = array();
		foreach ($elements as $element)
			$groupIds[] = $element->nodeValue;
					
		return $groupIds;
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
 		
 		if ($this->getFieldValue('submit')) {
			// get all site members
			$group = $this->getNode()->getMembersGroup();				
			// get all sub-groups in site members group
			$subgroups = $group->getGroups(false);
	
			while ($subgroups->hasNext()) {
				$subgroup = $subgroups->next();
				
				if ($this->getFieldValue($subgroup->getId()->getIdString()) != "true") {
					$this->addMembersHiddenGroup($subgroup->getId()->getIdString());
				} else {
					$this->removeMembersHiddenGroup($subgroup->getId()->getIdString());
				}
			}
		}
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
 		$harmoni = Harmoni::instance();
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
		
		$this->addHeadJavascript('ParticipantPanel.js');
		$this->addHeadCss('Participation.css');
			
		ob_start();
		
		// get all site members
		$group = $this->getNode()->getMembersGroup();
		//printpre ($group);
				
		// get all sub-groups in site members group
		$subgroups = $group->getGroups(false);
		
		// get member hidden groups
		$hiddenGroups = $this->getMembersHiddenGroups();
			
		if ($this->getFieldValue('edit') && $this->canModify()) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace(null);
			$membersUrl = SiteDispatcher::quickURL('agent', 'modify_members', array(
				'returnModule' => $this->getLocalModule(),
				'returnAction' => $this->getLocalAction()
				));
			$rolesUrl = SiteDispatcher::quickURL('roles', 'modify', array(
				'agent' => $group->getId()->getIdString(),
				'returnModule' => $this->getLocalModule(),
				'returnAction' => $this->getLocalAction()
				));
			$harmoni->request->endNamespace();
			print "\n<div class='participation_ext_link'><a href='".$membersUrl."'>"._("Add/Edit Site-Members &raquo;")."</a></div>";
			print "\n<div class='participation_ext_link'><a href='".$rolesUrl."'>"._("View/Edit Roles of Site-Members &raquo;")."</a></div>";
			
			print "<p>(".Help::link('Site-Members').")</p>";
			
			print "\n".$this->formStartTagWithAction();	
			
			if ($subgroups->hasNext()) {
				print "\n<div class='participant_group_header'>"._("Show members of following groups:")."</div>";
				
				while ($subgroups->hasNext()) {
					$subgroup = $subgroups->next();
					print "\n<div class='participant_list'>";
					print "\n\t<input name='".$this->getFieldName($subgroup->getId()->getIdString())."' value='true' type='checkbox' ";
					if (!in_array($subgroup->getId()->getIdString(), $hiddenGroups))
						print " checked";				
					print ">".$subgroup->getDisplayName();
					print "\n</div>";
				}
			}
			
			print "\n<div class='participation_buttons'>\n\t<input type='submit' value='Update' name='".$this->getFieldName('submit')."'>\n";
			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/></div>";
			print "</form>";
		
		} else if ($this->canView()) {

			// Direct members of the group
			print "<div class='participant_header'>"._("Site Members")."</div>";
			print $this->printMemberIterator($group->getMembers(false));

			// Members of subgroups
			while ($subgroups->hasNext()) {
				$subgroup = $subgroups->next();
				
				print "<div class='participant_group_header'>".$subgroup->getDisplayName()."</div>";
				
				if (!in_array($subgroup->getId()->getIdString(), $hiddenGroups)) {
					print $this->printMemberIterator($subgroup->getMembers(false));
				}
			}
			
			// Other Participants
			$notPrintedParticipants = array();
			foreach ($this->getView()->getParticipants() as $participant) {
				if (!in_array($participant->getId()->getIdString(), $this->_printedParticipants)) {
					$notPrintedParticipants[] = $participant;
				}
			}
			
			print "<br/><div class='participant_header'>"._("Other Participants")."</div>";
			print $this->printParticipants($notPrintedParticipants);
			
			if($this->shouldShowControls()){
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._('edit')."</a>";
				print "\n</div>";
			}

		}

 		return ob_get_clean();
 	}
 	

 	/**
 	 * Get all members of the site
 	 * 
 	 * @param object $groupMembers
 	 * @param string $title
 	 * @return string
 	 * @access protected
 	 * @since 2/18/09
 	 */
 	protected function getMemberCount ($groupMembers) {
 		$members = array();
		while ($groupMembers->hasNext()) {
			$members[] = $groupMembers->next();
		}
		return count($members);
 	}

 	/**
 	 * Print out the members in an iterator
 	 * 
 	 * @param object $groupMembers
 	 * @return string
 	 * @access protected
 	 * @since 2/18/09
 	 */
 	protected function printMemberIterator ($groupMembers) {
 		$members = array();
		while ($groupMembers->hasNext()) {
			$members[] = $groupMembers->next();
		}
		return $this->printParticipants($members);
 	}
 	
 	/**
 	 * Print out an array of Agents or Particapnts
 	 * 
 	 * @param array $participants Agent objects or Particpation_Particpant objects
 	 * @return string
 	 * @access public
 	 * @since 2/18/09
 	 */
 	public function printParticipants (array $participants) {
 		ob_start();
 		
 		$sortKeys = array();	
		foreach ($participants as $participant) {
			$sortKeys[] = $participant->getDisplayName();			
		}
		
		array_multisort($sortKeys, array_keys($participants), SORT_ASC, $participants);
		
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
 		
		print "\n\t<div class='participant_plugin_list'>";
		
		// show link to more info only if authenticated user is an editor
		if ($this->_showTrackLink == true) {
			print "\n\t\t<a href='#' onclick=\"ParticipantPanel.run('".addslashes($participant->getDisplayName())."', '".addslashes($participant->getId()->getIdString())."', '".addslashes($this->getNode()->getId())."', '".SiteDispatcher::quickURL('roles', 'modify', array('agent' => $participant->getId()->getIdString(), 'returnModule' => $this->getLocalModule(), 'returnAction' => $this->getLocalAction()))."'.urlDecodeAmpersands(), this); return false;\">";
			print $participant->getDisplayName()."</a>";
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
 		return true;
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
 		$wrapper = new WComponentCollection;
 		ob_start();
 		
 		$group = $this->getNode()->getMembersGroup();
		$subgroups = $group->getGroups(false);
		$hiddenGroups = $this->getMembersHiddenGroups();
 		
 		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		$membersUrl = SiteDispatcher::quickURL('agent', 'modify_members', array(
			'returnModule' => $this->getLocalModule(),
			'returnAction' => $this->getLocalAction()
			));
		$rolesUrl = SiteDispatcher::quickURL('roles', 'modify', array(
			'agent' => $group->getId()->getIdString(),
			'returnModule' => $this->getLocalModule(),
			'returnAction' => $this->getLocalAction()
			));
		$harmoni->request->endNamespace();
		print "\n<p class='participation_ext_link'><a href='".$membersUrl."'>"._("Add/Edit Site-Members &raquo;")."</a></p>";
		print "\n<p class='participation_ext_link'><a href='".$rolesUrl."'>"._("View/Edit Roles of Site-Members &raquo;")."</a></p>";
				
		if ($subgroups->hasNext()) {
			print "\n<h4>"._("Show members of following groups:")."</h4>";
			
			while ($subgroups->hasNext()) {
				$subgroup = $subgroups->next();
				
				$propertyId = md5($subgroup->getId()->getIdString());
				$property = $wrapper->addComponent($propertyId,
					WCheckBox::withLabel($subgroup->getDisplayName()));
				if (!in_array($subgroup->getId()->getIdString(), $hiddenGroups))
					$property->setChecked(true);
				
				print "\n\n[[".$propertyId."]]<br/>";
			}
		}
		
		$wrapper->setContent(ob_get_clean());
 		return $wrapper;
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
 		// get all site members
		$group = $this->getNode()->getMembersGroup();				
		// get all sub-groups in site members group
		$subgroups = $group->getGroups(false);
		while ($subgroups->hasNext()) {
			$subgroup = $subgroups->next();
			
			if (!$values[md5($subgroup->getId()->getIdString())]) {
				$this->addMembersHiddenGroup($subgroup->getId()->getIdString());
			} else {
				$this->removeMembersHiddenGroup($subgroup->getId()->getIdString());
			}
		}
 	}
 	
}

?>