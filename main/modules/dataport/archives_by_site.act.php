<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/archives_by_user.act.php');

/**
 * Send emails to users reminding them to deal with their sites.
 * 
 * 
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 */
class archives_by_siteAction
	extends archives_by_userAction
{
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 3/26/08
	 */
	public function execute () {
		if (!defined('ARCHIVE_URL_CALLBACK'))
			throw new ConfigurationErrorException('ARCHIVE_URL_CALLBACK must be defined.');
		return parent::execute();
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
		
		$this->slots = array();
		
		// Get the users who can adminster each slot.
		while ($slots->hasNext()) {
			$i++;
			if ($i % $increment == 0) {
				print '.';
				flush();
			}
			
			$slot = $slots->next();
			if (!$slot->isAlias() && $slot->siteExists()) {
				$this->slots[$slot->getShortname()] = array('slot' => $slot, 'users' => array());
				
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
	
	/**
	 * Add a slot to a users array for an agent.
	 * 
	 * @param Agent $agent
	 * @param Slot $slot
	 * @return int
	 */
	protected function addSlotForAgent (Agent $agent, Slot $slot) {
		$agentIdString = $agent->getId()->getIdString();
		$email = $this->getAgentEmail($agent);
		// Don't include people without email addresses
		if (empty($email))
			return 0;
			
		$this->slots[$slot->getShortname()]['users'][$email] = array(
			'name' => $agent->getDisplayName(),
			'email' => $email,
		);
		ksort($this->slots[$slot->getShortname()]['users']);
		 
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
	 * Email users.
	 * 
	 * @return null
	 */
	protected function printList () {
				
		print<<<END
<html>
	<head>
		<title>Segue Archives by site</title>
		<style type='text/css'>
			.email {
				margin-bottom: 2px;
			}
			.user_exists {
				background-color: LightGreen;
			}
			.user_missing {
				background-color: LightPink;
			}
			.status {
				color: White;
			}
			.status_archived, .status_migrated {
				background-color: Green;
			}
			.status_unneeded {
				background-color: Gold;
			}
			.status_incomplete {
				background-color: Red;
			}
		</style>
	</head>
	<body>
		<h1>Segue Archives by site</h1>
		<p>Each row contains a site and a list of the users that are administrators of the site. To see this list organized by user, see <a href='archives_by_user.html'>archives_by_user.html</a></p>
		<p>Use your browser's search to find users or sites</p>
		<table border='1'>
			<thead>
				<tr>
					<th>Site Name</th>
					<th>Migration Status</th>
					<th>Admins</th>
					<th>User Name</th>
					<th>Archive Link</th>
				</tr>
			</thead>
		<tbody>
END;
		ksort($this->slots);
		foreach ($this->slots as $slot_name => $slot_info) {
			
			print "\n\t\t\t\t<tr>";
			print "\n\t\t\t\t\t<td class='slot'>".$slot_name."</td>";
			
			print "\n\t\t\t\t\t<td class='migration_status_line'>";
			$status = $slot_info['slot']->getMigrationStatus();
			switch ($status['type']) {
				case 'archived':
					print '<span class="status status_archived">Archived</span>';
					break;
				case 'migrated':
					print '<span class="status status_migrated">Migrated</span>';
					if (!empty($status['url'])) {
						print ' to <a href="'.htmlentities($status['url']).'">'.htmlentities($status['url']).'</a>';
					}
					break;
				case 'unneeded':
					if ($slot_info['slot']->siteExists())
						print '<span class="status status_unneeded">No Longer Needed</span>';
					else
						print '<span class="status status_unneeded_empty">No Longer Needed</span>';
					break;
				default:
					print '<span class="status status_incomplete">Incomplete</span>';
			}
			print "</td>";
			
			print "\n\t\t\t\t\t<td class='users'>";
			foreach ($slot_info['users'] as $user) {
				if (defined('USER_EXISTS_CALLBACK')) {
					if (call_user_func(USER_EXISTS_CALLBACK, $user['email'])) {
						$userExists = 'user_exists';
					} else {
						$userExists = 'user_missing';
					}
				} else {
					$userExists = '';
				}
				
				print "\n\t\t\t\t\t<div class='email $userExists'>".$user['email']." ".$user['name']."</div>";
			}
			print "</td>";
			
			$url = call_user_func(ARCHIVE_URL_CALLBACK, $slot_name);
			print "\n\t\t\t\t\t<td class='archive'><a href='".$url."'>".$url."</a></td>";
			
			print "\n\t\t\t\t</tr>";
		}
		
		print <<<END
			
			</tbody>
		</table>
	</body>
</html>
END;
	}
}

?>