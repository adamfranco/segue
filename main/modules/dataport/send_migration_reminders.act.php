<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

/**
 * Send emails to users reminding them to deal with their sites.
 * 
 * 
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 */
class send_migration_remindersAction
	extends Action
{

	/**
	 * Constructor
	 * 
	 */
	public function __construct () {
		$this->users = array();
		$this->slotsWithoutAdmins = array();
	}
		
	/**
	 * Authorization. As existence is not sensitive information, allow anonymous access.
	 *
	 * @return boolean
	 * @access public
	 * @since 3/26/08
	 */
	public function isAuthorizedToExecute () {
		// Only allow execution from the command line for anonymous
		if(php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
			return true;
		
		// Allow execution through web for admins.
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$idManager->getId("edu.middlebury.authorization.root")))
			return true;
		
		return false;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException('This command can only be run by admins or from the command-line.');
		
		header("Content-Type: text/plain");
		
		if (RequestContext::value('help') || RequestContext::value('h') || RequestContext::value('?')) {
			throw new HelpRequestedException(
"Usage:
	".$_SERVER['argv'][0]." [-h|--help] [-t|--test]
	
	-h,--help  Print out this help text.
	-t,--test  Run in test mode. Equivalent to adding `define('MIGRATION_REMINDER_EMAIL_TEST_ONLY', true);` to the config.
");
		}
		
		if (RequestContext::value('t') || RequestContext::value('test')) {
			if (defined('MIGRATION_REMINDER_EMAIL_TEST_ONLY') && !MIGRATION_REMINDER_EMAIL_TEST_ONLY)
				throw new ConfigurationErrorException(
"-t or --test was specified, but the configuration has set MIGRATION_REMINDER_EMAIL_TEST_ONLY to false.
Do not specify a value for MIGRATION_REMINDER_EMAIL_TEST_ONLY in the configuration if  you wish to
be able to switch between test mode and real mode with -t/--test.");
			
			define('MIGRATION_REMINDER_EMAIL_TEST_ONLY', true);
		}
		
		while(ob_get_level())
			ob_end_flush();
		flush();
		
		if (!defined('MIGRATION_REMINDER_EMAIL_TEST_ONLY'))
			define('MIGRATION_REMINDER_EMAIL_TEST_ONLY', false);
		
		if (MIGRATION_REMINDER_EMAIL_TEST_ONLY)
			print "In test mode. Email will be sent to ".MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT." rather than real recipients.\n";
		else
			print "Site reminders will be sent to real recipients.\n";
		
		$this->buildUserSiteList();
		$this->emailUsers();
		
		exit;
	}
	
	/**
	 * Build a list of the sites for which users are admins
	 * 
	 * @return null
	 */
	protected function buildUserSiteList () {
		$slotMgr = SlotManager::instance();
		$slots = $slotMgr->getAllSlots();
		
		$roleMgr = SegueRoleManager::instance();
		$agentMgr = Services::getService('Agent');
		$increment = round($slots->count()/100);
		$i = 0;
		$numSites = 0;
		$incomplete = 0;
		
		// Get the users who can adminster each slot.
		while ($slots->hasNext()) {
			$i++;
			if ($i % $increment == 0) {
				print '.';
				flush();
			}
			
			$slot = $slots->next();
			if (!$slot->isAlias() && $slot->siteExists()) {
				$numSites++;
				$status = $slot->getMigrationStatus();
				if ($status['type'] == 'incomplete') {
					$incomplete++;
					
					$adminIds = $roleMgr->getAgentsWithExplicitRoleAtLeast(
						$roleMgr->getRole('admin'),
						$slot->getSiteId(),
						true
					);
					$numAdmins = 0;
					foreach ($adminIds as $adminId) {
						// Skip the special groups of
						// 	edu.middlebury.institute
						// 	edu.middlebury.agents.users
						// 	edu.middlebury.agents.anonymous
						// etc.
						if (preg_match('/^edu\.middlebury\./', $adminId->getIdString()))
							continue;
						
						try {
							$agent = $agentMgr->getAgentOrGroup($adminId);
							if ($agent->isGroup())
								$numAdmins = $numAdmins + $this->addSlotForGroup($agent, $slot);
							else
								$numAdmins = $numAdmins + $this->addSlotForAgent($agent, $slot);
						} catch (UnknownIdException $e) {
						}
						
					}
					if (!$numAdmins)
						$this->slotsWithoutAdmins[] = $slot->getShortname();
				}
			}
		}
		print "\n$incomplete slots of $numSites are still marked as incomplete\n";
		print "\nSlots without Admins:\n";
		foreach ($this->slotsWithoutAdmins as $slot)
			print "\t".$slot."\n";
	}
	
	/**
	 * Email users.
	 * 
	 * @return null
	 */
	protected function emailUsers () {
		if (!defined('MIGRATION_REMINDER_EMAIL_TEST_ONLY'))
			define('MIGRATION_REMINDER_EMAIL_TEST_ONLY', false);
		if (!defined('MIGRATION_REMINDER_EMAIL_TEST_MAX'))
			define('MIGRATION_REMINDER_EMAIL_TEST_MAX', 1);
		if (!defined('MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT'))
			define('MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT', '');
				
		$total = count($this->users);
		print "\n".$total." users will be sent reminders.\n";
		$i = 0;
		foreach ($this->users as $user) {
			$i++;
			$headers = $this->getEmailHeaders($user);
			$subject = $this->getEmailSubject($user);
			$body = $this->getEmailBody($user);
			
			if (MIGRATION_REMINDER_EMAIL_TEST_ONLY)
				$to = MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT;
			else
				$to = $user['email'];
			
			print str_pad($i, strlen($total), ' ', STR_PAD_LEFT).' of '.$total.'    '.$to."\n";
			
			if (MIGRATION_REMINDER_EMAIL_TEST_ONLY && !MIGRATION_REMINDER_EMAIL_TEST_RECIPIENT) {
				print "\n---------------------------------------------------\n";
				print $to."\n\n";
				print $headers."\n\n";
				print $subject."\n\n";
				print $body."\n";
			} else {
				mail($to, $subject, $body, $headers);
			}
			
			
			if (MIGRATION_REMINDER_EMAIL_TEST_ONLY && $i >= MIGRATION_REMINDER_EMAIL_TEST_MAX)
				break;
		}
	}
	
	/**
	 * Answer the message body for a user.
	 * 
	 * @param array $user
	 * @return string
	 */
	protected function getEmailBody (array $user) {
		if (!defined('MIGRATION_REMINDER_EMAIL_MESSAGE'))
			throw new ConfigurationErrorException('MIGRATION_REMINDER_EMAIL_MESSAGE must be defined.');
		
		ob_start();
		print "\n\t<ul>";
		foreach ($user['slots'] as $slot) {
			$viewUrl = rtrim(SiteDispatcher::getBaseUrlForSlot($slot), '/').'/sites/'.$slot->getShortname();
			print "\n\t\t<li>";
			print "<a href=\"".$viewUrl."\">".$viewUrl."</a>";
			print "</li>";
		}
		print "\n\t</ul>";
		
		$message = str_replace('[[USER]]', $user['name'], MIGRATION_REMINDER_EMAIL_MESSAGE);
		$message = str_replace('[[SITE_LIST]]', ob_get_clean(), $message);
		
		return $message;
	}
	
	/**
	 * Answer the subject 
	 * 
	 * @param array $user
	 * @return string
	 */
	protected function getEmailSubject (array $user) {
		if (!defined('MIGRATION_REMINDER_EMAIL_SUBJECT'))
			throw new ConfigurationErrorException('MIGRATION_REMINDER_EMAIL_SUBJECT must be defined.');
		
		return str_replace('[[USER]]', $user['name'], MIGRATION_REMINDER_EMAIL_SUBJECT);
	}
	
	/**
	 * Answer a header string for the email.
	 * 
	 * @param array $user
	 * @return string
	 */
	protected function getEmailHeaders (array $user) {
		if (!defined('MIGRATION_REMINDER_EMAIL_FROM_NAME'))
			throw new ConfigurationErrorException('MIGRATION_REMINDER_EMAIL_FROM_NAME must be defined.');
		if (!defined('MIGRATION_REMINDER_EMAIL_FROM_MAIL'))
			throw new ConfigurationErrorException('MIGRATION_REMINDER_EMAIL_FROM_MAIL must be defined.');
		
		// To send HTML mail, the Content-type header must be set
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		
		// Additional headers
		$headers .= 'From: ' . MIGRATION_REMINDER_EMAIL_FROM_NAME ." <" . MIGRATION_REMINDER_EMAIL_FROM_MAIL . ">\r\n";
		$headers .= 'Reply-To: ' . MIGRATION_REMINDER_EMAIL_FROM_NAME ." <" . MIGRATION_REMINDER_EMAIL_FROM_MAIL . ">\r\n";
		$headers .= 'Return-Path: ' . MIGRATION_REMINDER_EMAIL_FROM_MAIL . "\r\n";
				
		return $headers;
	}
	
	/**
	 * Add a slot to a users array for an agent.
	 * 
	 * @param Agent $agent
	 * @param Slot $slot
	 * @return int
	 */
	protected function addSlotForAgent (Agent $agent, Slot $slot) {
		$agentIdString = $agent->getId()->getIdString();
		if (!isset($this->users[$agentIdString])) {
			$email = $this->getAgentEmail($agent);
			// Don't include people without email addresses
			if (empty($email))
				return 0;
			
			$this->users[$agentIdString] = array(
				'name' => $agent->getDisplayName(),
				'email' => $email,
				'slots' => array(),
			);
		}
		$this->users[$agentIdString]['slots'][] = $slot;
		return 1;
	}
	
	/**
	 * Add a slot to all users in a group.
	 * 
	 * @param Group $group
	 * @param Slot $slot
	 * @return int
	 */
	protected function addSlotForGroup (Group $group, Slot $slot) {
		$members = $group->getMembers(true);
		$i = 0;
		while ($members->hasNext()) {
			$i = $i + $this->addSlotForAgent($members->next(), $slot);
		}
		return $i;
	}
	
	/**
	 * Answer a email for an agent
	 * 
	 * @param Agent $agent
	 * @return string
	 */
	protected function getAgentEmail (Agent $agent) {
// 		print_r($agent->getProperties()); exit;
		$propertiesIterator = $agent->getProperties();
		while ($propertiesIterator->hasNext()) {
			$properties = $propertiesIterator->next();
			
			if (!is_null($properties->getProperty('EMail')))
				return $properties->getProperty('EMail');
				
			if (!is_null($properties->getProperty('email')))
				return $properties->getProperty('email');
				
			if (!is_null($properties->getProperty('Email')))
				return $properties->getProperty('Email');
		}
		return null;
	}
}

?>