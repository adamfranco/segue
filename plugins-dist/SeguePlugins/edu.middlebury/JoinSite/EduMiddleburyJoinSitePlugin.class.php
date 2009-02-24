<?php
/**
 * @since 2/18/09
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This plugin allows for self-registration to a site.
 * 
 * @since 2/18/09
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class EduMiddleburyJoinSitePlugin
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
 		
 		return str_replace('%1', Help::link('Site-Members'), _("This plugin allows users to self-register as members of the site. After users click the <strong>Join</strong> button an email will be sent to site administrators asking them to approve the request. Upon aproval the user will be automatically added to the 'Site-Members' group. See the Site-Members %1 for more details."));
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
 		return _("Join Site");
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
 		return array("Adam Franco");
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
 		return '0.1';
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
 		// Initialize our data store.
		$this->doc = new Harmoni_DOMDocument;
		$this->doc->preserveWhiteSpace = false;
		if (strlen($this->getContent())) {
			try {
				$this->doc->loadXML($this->getContent());
			} catch (DOMException $e) {
				$this->doc->loadXML("<JoinSitePlugin></JoinSitePlugin>");
			}
	 	} else
	 		$this->doc->loadXML("<JoinSitePlugin></JoinSitePlugin>");
	 			
 		$this->xpath = new DOMXPath($this->doc);
 		
//  		printpre(htmlentities($this->doc->saveXMLWithWhitespace()));
 		
 		
 		// Initialize our current user.
 		$authNMgr = Services::getService("AuthN");
		$agentMgr = Services::getService("Agent");
 		$this->currentUser = $agentMgr->getAgent($authNMgr->getFirstUserId());
 		
 		// Authorizations
 		$this->setCanModifyFunction(array($this, 'canApproveRequests'));
 		
 		$this->messages = array();
 	}
 	
 	/**
 	 * Answer true if the user can modify this plugin
 	 * 
 	 * @param <##>
 	 * @return boolean
 	 * @access public
 	 * @since 2/19/09
 	 */
 	public function canApproveRequests () {
 		$authZ = Services::getService("AuthZ");
 		return $authZ->isUserAuthorized(
 			new HarmoniId('edu.middlebury.authorization.view_authorizations'),
 			new HarmoniId($this->getId()));
 	}
 	
 	/**
 	 * Add Messages to the message queue
 	 * 
 	 * @param string $message
 	 * @return void
 	 * @access protected
 	 * @since 2/19/09
 	 */
 	protected function addMessage ($message) {
 		$this->messages[] = $message;
 	}
 	
 	/**
 	 * Print out our messages
 	 * 
 	 * @return void
 	 * @access protected
 	 * @since 2/19/09
 	 */
 	protected function printMessages () {
 		if (!count($this->messages))
 			return;
 		print "\n<ul>";
 		foreach ($this->messages as $message) {
 			print "\n\t<li>$message</li>";
 		}
 		print "\n</ul>";
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
 		try {
			if ($this->canModify() && $this->getFieldValue('change')) {
				$agentMgr = Services::getService("Agent");
				$agent = $agentMgr->getAgent(new HarmoniId($this->getFieldValue('agent_id')));
					
				if ($this->getFieldValue('change') == 'approve') {
					$this->approveAgent($agent);
					$this->addMessage(str_replace('%1', $agent->getDisplayName(), _('%1 approved and added to site-members. %1 has been notified by email.')));
				} else if ($this->getFieldValue('change') == 'deny') {
					$this->denyAgent($agent);
					$this->addMessage(str_replace('%1', $agent->getDisplayName(), _('Request by %1 to join this site denied. %1 has been notified by email.')));
				}
			}
		} catch (OperationFailedException $e) {
			$this->addMessage($e->getMessage());
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
 		ob_start();
 		
 		$this->printMessages();
 		
 		switch ($this->getFieldValue('mode')) {
 			case 'join':
 				if ($this->currentUser->getId()->isEqual(new HarmoniId('edu.middlebury.agents.anonymous'))) {
 					// Prompt for login or registration
 					print "<h4>"._("Please log in:")."</h4>";
 					
 					$action = new displayAction;
 					print "\n<div style='margin-left: 30px;'>";
 					print $action->getLoginFormHtml();
 					print "\n</div>";
 					
 					print "<h4>"._("Or create a visitor account:")."</h4>";
 					print "\n<div style='margin-left: 30px;'>";
 					print $action->getVisitorRegistrationLink();
 					print "\n</div>";
 					
 					
 					
 				} else if ($this->isAwaitingApproval($this->currentUser->getId())) {
 					print _("Your request has already been submitted and is awaiting approval from the site administrator.");
 					print "\n<br/>";
 					print _("You will receive an email when your request is approved.");
 				} else {
 					// Add to the queue
 					$this->join();
 					print _("Your request has been submitted and is awaiting approval from the site administrator.");
 					print "\n<br/>";
 					print _("You will receive an email when your request is approved.");
 				}
 				break;
 			default:
 				if ($this->isUserMember()) {
 					print "<button disabled='disabled'>"._('Join Site')."</button>";
 				} else {
					print "\n<button onclick='window.location = \"".$this->url(array('mode' => 'join'))."\".urlDecodeAmpersands();'>"._('Join Site')."</button>";
				}
 				
				if ($this->canModify()) {
					$awaitingApproval = $this->getAgentsAwaitingApproval();
					if (count($awaitingApproval)) {
						print "\n<h4>"._('Awaiting Approval').'</h4>';
						print "\n<ul>";
						foreach ($awaitingApproval as $agent) {
							print "\n\t<li>";
							print $agent->getDisplayName();
							
							print "\n\t\t<a href='";
							print $this->url(array('change' => 'approve', 'agent_id' => $agent->getId()->getIdString()));
							print "'>"._('Approve')."</a>";
							
							print "\n\t\t<a href='";
							print $this->url(array('change' => 'deny', 'agent_id' => $agent->getId()->getIdString()));
							print "'>"._('Deny')."</a>";
							
							print "\n\t</li>";
						}
						print "\n</ul>";
					} else {
						print "\n<h4>"._('No users are currently awaiting approval').'</h4>';
					}
				}
		}
 		
 		
 		return ob_get_clean();
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
 		return _("Self registration to the site.");
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

	
/*********************************************************
 * Internal methods
 *********************************************************/

	 /**
	 * Read an option
	 * 
	 * @param string $key
	 * @return string
	 * @access protected
	 * @since 2/4/09
	 */
	protected function readOption ($key) {		
		$elements = $this->xpath->query('/JoinSitePlugin/options/'.$key);
			
		if ($elements->length && strlen($elements->item(0)->nodeValue))
			return $elements->item(0)->nodeValue;
				
		if (isset($this->_defaults[$key]))
			return $this->_defaults[$key];
		
		throw new OperationFailedException('No default specified for "'.$key.'".', 9784689);
	}
	
	/**
	 * Write an option
	 * 
	 * @param string $key
	 * @param string $val
	 * @return void
	 * @access protected
	 * @since 2/4/09
	 */
	protected function writeOption ($key, $val) {
		// The options will look like:
		/*
<JoinSitePlugin>
	<options>
		<targetNodeId>12345</targetNodeId>
		<defaultSortMethod>alpha</defaultSortMethod>
		<defaultDisplayType>cloud</defaultDisplayType>
	</options>
</JoinSitePlugin>
		*/
		
		if (!in_array($key, $this->_allowedOptions))
			throw new InvalidArgumentException("Unknown option, $key");
		
		// Fetch the existing element or create a new one for this key
		$elements = $this->xpath->query('/JoinSitePlugin/options/'.$key);
		if ($elements->length)
			$element = $elements->item(0);
		else {
			$optionsElements = $this->xpath->query('/JoinSitePlugin/options');
			if ($optionsElements->length)
				$optionsElement = $optionsElements->item(0);
			else
				$optionsElement = $this->doc->documentElement->appendChild($this->doc->createElement('options'));
			
			$element = $optionsElement->appendChild($this->doc->createElement($key));
		}
		
		
		// Set the value and save
		$element->nodeValue = $val;
		$this->setContent($this->doc->saveXMLWithWhitespace());
	}
	
	/**
	 * Answer an array of agents needing approval
	 * 
	 * @return array
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getAgentsAwaitingApproval () {		
		$elements = $this->xpath->query('/JoinSitePlugin/approvalQueue/agent');
		$agentMgr = Services::getService('Agent');
		
		$agents = array();
		foreach ($elements as $element) {
			try {
				$agents[] = $agentMgr->getAgent(new HarmoniId($element->getAttribute('id')));
			} catch (UnknownIdException $e) {
				throw $e; //temporary, in the future, lets log this or display an error message.
			}
		}
		return $agents;
	}
	
	/**
	 * Sign up the current user.
	 * 
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function join () {
		if ($this->currentUser->getId()->isEqual(new HarmoniId('edu.middlebury.agents.anonymous')))
 			throw new PermissionDeniedException("You must log in to join this site.");
 					
 		if ($this->isAwaitingApproval($this->currentUser->getId()))
			throw new OperationFailedException("Your request has already been submitted, you will receive an email upon approval.");
		
		if ($this->isUserMember())
			throw new OperationFailedException("You are already a member of this site.");
			
		$this->addToApprovalQueue($this->currentUser->getId());
		$this->sendApprovalWaitingNotice($this->currentUser);
	}
	
	/**
	 * Approve an agent
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function approveAgent (Agent $agent) {
		if (!$this->isAwaitingApproval($agent->getId()))
			throw new OperationFailedException("Agent '".$agent->getDisplayName()."' is not awaiting approval.");
		
		// Add the agent to the Site-Members group.
		$group = $this->getSiteMembersGroup();
		if ($group->contains($agent, false)) {
			$this->removeFromApprovalQueue($agent->getId());
			return;
		}
		$group->add($agent);
		
		// Send the Agent an email notice of their approval.
		try {
			$this->sendApprovalNotice($agent);
		} catch (OperationFailedException $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
		}
		
		// Remove the agent from our queue.
		$this->removeFromApprovalQueue($agent->getId());
	}
	
	/**
	 * Deny an agent
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function denyAgent (Agent $agent) {		
		if (!$this->isAwaitingApproval($agent->getId()))
			throw new OperationFailedException("Agent '".$agent->getDisplayName()."' is not awaiting approval.");
		
		// Check if they are already in the Site-Members group.
		$group = $this->getSiteMembersGroup();
		if ($group->contains($agent, false)) {
			$this->removeFromApprovalQueue($agent->getId());
			throw new OperationFailedException("Agent '".$agent->getDisplayName()."' is already a site-member.");
		}
		
		// Send the Agent an email notice of their denial.
		try {
			$this->sendDenialNotice($agent);
		} catch (OperationFailedException $e) {
			HarmoniErrorHandler::logException($e, 'Segue');
		}
		
		// Remove the agent from our queue.
		$this->removeFromApprovalQueue($agent->getId());
	}
	
	/**
	 * Answer true if the agent id passed is awaiting approval
	 * 
	 * @param object Id $agentId
	 * @return boolean
	 * @access protected
	 * @since 2/19/09
	 */
	protected function isAwaitingApproval (Id $agentId) {
		$elements = $this->xpath->query('/JoinSitePlugin/approvalQueue/agent[@id="'.$agentId->getIdString().'"]');
		return ($elements->length > 0);
	}
	
	/**
	 * Add an agent to our approval queue
	 * 
	 * @param string Id $agentId
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function addToApprovalQueue (Id $agentId) {
		if ($this->isAwaitingApproval($agentId))
			throw new OperationFailedException("Already awaiting approval.");
		
		// Ensure that our queue exists
		$queueElements = $this->xpath->query('/JoinSitePlugin/approvalQueue');
		if ($queueElements->length)
			$queueElement = $queueElements->item(0);
		else
			$queueElement = $this->doc->documentElement->appendChild($this->doc->createElement('approvalQueue'));
		
		// Add to the queue
		$agentElement = $queueElement->appendChild($this->doc->createElement('agent'));
		$agentElement->setAttribute('id', $agentId->getIdString());
		$this->setContent($this->doc->saveXMLWithWhitespace());
	}
	
	/**
	 * Remove an agent from our approval queue.
	 * Throws an UnknownIdException if the Agent is not in the queue
	 * 
	 * @param object Id $agentId
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function removeFromApprovalQueue (Id $agentId) {
		$elements = $this->xpath->query('/JoinSitePlugin/approvalQueue/agent[@id="'.$agentId->getIdString().'"]');
		if (!$elements->length)
			throw new UnknownIdException("No agent queued with id, '".$agentId->getIdString()."'");
		
		$element = $elements->item(0);
		$element->parentNode->removeChild($element);
		$this->setContent($this->doc->saveXMLWithWhitespace());
	}
	
	/**
	 * Send an approval notice to an Agent
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function sendApprovalNotice (Agent $agent) {
		$subject = str_replace('%1', $this->getSiteTitle(), _('%1 membership request approved'));
		
		$body = '
	<p>'._('Your request to join %1 has been approved.').'</p>
	<p>'._('To access the site, please go to %2 and log in.').'</p>
';
		$link = '<a href="'.$this->getSiteUrl().'">'.$this->getSiteTitle().'</a>';
		$body = str_replace('%1', $this->getSiteTitle(), $body);
		$body = str_replace('%2', $link, $body);
		
		$this->sendEmail(array($agent), $subject, $body);
	}
	
	/**
	 * Send an denial notice to an Agent
	 * 
	 * @param object Agent $agent
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function sendDenialNotice (Agent $agent) {
		$subject = str_replace('%1', $this->getSiteTitle(), _('%1 membership request denied'));
		
		$body = '
	<p>'._('Your request to join %1 has been denied.').'</p>
';
		$body = str_replace('%1', $this->getSiteTitle(), $body);
		
		$this->sendEmail(array($agent), $subject, $body);
	}
	
	/**
	 * Send a notice to site administrators that a user is waiting for approval to join the site.
	 * 
	 * @param object Agent $newAgent
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function sendApprovalWaitingNotice (Agent $newAgent) {
		$subject = str_replace('%1', $this->getSiteTitle(), _('Join %1 request requires your approval.'));
		
		$body = '
	<p>'._('%2 has requested to join %1.').'</p>
	<p>'._('To approve or deny this request, please go to %5 and log in.').'</p>

';
		$manageLink = '<a href="'.$this->getManageUrl().'">'.$this->getSiteTitle().'</a>';
		
		$body = str_replace('%1', $this->getSiteTitle(), $body);
		$body = str_replace('%2', $newAgent->getDisplayName(), $body);
		$body = str_replace('%5', $manageLink, $body);
		
		$this->sendEmail($this->getAdministrators(), $subject, $body);
	}
	
	/**
	 * Send email to an array of agents
	 * 
	 * @param array $agents
	 * @param string $subject
	 * @param string $bodyHtml
	 * @return void
	 * @access protected
	 * @since 2/19/09
	 */
	protected function sendEmail (array $agents, $subject, $bodyHtml) {
		if (!count($agents))
			throw new OperationFailedException("No agents specified to email.");
		
		$message = '
<html>
<head>
	<title>'.$subject.'</title>
</head>
<body>
	'.$bodyHtml.'
</body>
</html>';
		
		// To
		$destinations = array();
		foreach ($agents as $agent) {
			try {
				$destinations[] = '"'.$agent->getDisplayName().'" <'.$this->getAgentEmail($agent).'>';
			} catch (OperationFailedException $e) {
				// Ignore problems with a single destination, in case there is one admin without an email.
				// We'll throw an exception if we can't mail anyone.
			}
		}
		if (!count($destinations))
			throw new OperationFailedException("No destination email addresses available.");
		$to = implode(', ', $destinations);
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
		// Additional Headers
		$headers .= 'To: ' .$to. "\r\n";
		$headers .= 'From: Segue Administrator <'.$_SERVER['SERVER_ADMIN'].'>' . "\r\n";
		
		if (!mail($to, $subject, $message, $headers))
			throw new OperationFailedException("Could not send email to ".$to." with subject '".$subject."'.");
	}
	
	/**
	 * Answer the title of the site
	 * 
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getSiteTitle () {
		return $this->getSiteNode()->getDisplayName();
	}
	
	/**
	 * Answer the URL of the site
	 * 
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getSiteUrl () {
		return SiteDispatcher::quickUrl('view', 'html', array('node' => $this->getSiteNode()->getId()));
	}
	
	/**
	 * Answer the URL of this plugin
	 * 
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getManageUrl () {
		return SiteDispatcher::quickUrl('view', 'html', array('node' => $this->getId()));
	}
	
	/**
	 * Answer the URL to approve an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getApprovalUrl (Agent $agent) {
		return $this->getPluginActionUrl('approve', array('agent_id' => $agent->getId()->getIdString()));
	}
	
	/**
	 * Answer the URL to denie an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getDenialUrl (Agent $agent) {
		return $this->getPluginActionUrl('deny', array('agent_id' => $agent->getId()->getIdString()));
	}
	
	/**
	 * Answer the site-members group
	 * 
	 * @return object Group
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getSiteMembersGroup () {
		return $this->getSiteNode()->getMembersGroup();
	}
	
	/**
	 * Answer the Site-node
	 * 
	 * @return SiteNavBlockSiteComponent
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getSiteNode () {
		return SiteDispatcher::getCurrentRootNode();
	}
	
	/**
	 * Answer true if the current user is already a site member
	 * 
	 * @return boolean
	 * @access protected
	 * @since 2/19/09
	 */
	protected function isUserMember () {
		return $this->getSiteMembersGroup()->contains($this->currentUser, true);
	}
	
	/**
	 * Answer an array of the administrators of this Site.
	 * 
	 * @return array of Agent objects
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getAdministrators () {
		$admins = array();
		
		$agentMgr = Services::getService('Agent');
		$roleMgr = SegueRoleManager::instance();
		
		$agentIdsWithRoles = $roleMgr->getAgentsWithRoleAtLeast($roleMgr->getRole('admin'), $this->getSiteNode()->getQualifierId(), true);
		foreach ($agentIdsWithRoles as $id) {
			// We ran into a case where roles weren't clearing when an agent
			// was deleted, log this issue and skip rather than crashing the
			// choose agent screen.
			try {
				$admins[] = $agentMgr->getAgentOrGroup($id);
			} catch (UnknownIdException $e) {
				HarmoniErrorHandler::logException($e, 'Segue');
			}
		}
		
		return $admins;
	}
	
	/**
	 * Answer the email address of an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 * @access protected
	 * @since 2/19/09
	 */
	protected function getAgentEmail (Agent $agent) {
		$properties = $agent->getProperties();		
		$email = null;
		while ($properties->hasNext()) {
			$email = $properties->next()->getProperty("email");
			if (preg_match('/^[^\s@]+@[^\s@]+$/', $email))
				return $email;
		}
		
		throw new OperationFailedException("No email found for agent, '".$agent->getDisplayName()."'.");
	}
}

?>