<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/list_incomplete_migrations.act.php');

/**
 * Send emails to users reminding them to deal with their sites.
 * 
 * 
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 */
class archives_by_userAction
	extends list_incomplete_migrationsAction
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
		
		// Get the users who can adminster each slot.
		while ($slots->hasNext()) {
			$i++;
			if ($i % $increment == 0) {
				print '.';
				flush();
			}
			
			$slot = $slots->next();
			if (!$slot->isAlias() && $slot->siteExists()) {					
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
	 * Email users.
	 * 
	 * @return null
	 */
	protected function printList () {
				
		print<<<END
<html>
	<head>
		<title>Segue Archives by user</title>
		<style type='text/css'>
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
		<h1>Segue Archives by user</h1>
		<p>Each row contains a user who is an administrator of the site to the right. To see this list organized by site, see <a href='archives_by_site.html'>archives_by_site.html</a></p>
		<p>Use your browser's search to find users or sites</p>
		<table border='1'>
			<thead>
				<tr>
					<th>User EMail</th>
					<th>User Name</th>
					<th>Site Name</th>
					<th>Migration Status</th>
					<th>Archive Link</th>
				</tr>
			</thead>
		<tbody>
END;
		$emails = array();
		foreach ($this->users as $user) {
			$emails[] = $user['email'];
		}
		array_multisort($emails, $this->users);
		foreach ($this->users as $user) {
			if (defined('USER_EXISTS_CALLBACK')) {
				if (call_user_func(USER_EXISTS_CALLBACK, $user['email'])) {
					$userExists = 'user_exists';
				} else {
					$userExists = 'user_missing';
				}
			} else {
				$userExists = '';
			}
			foreach ($user['slots'] as $slot) {
				print "\n\t\t\t\t<tr>";
				print "\n\t\t\t\t\t<td class='email $userExists'>".$user['email']."</td>";
				print "\n\t\t\t\t\t<td class='name $userExists'>".$user['name']."</td>";
				
				print "\n\t\t\t\t\t<td class='slot'>".$slot->getShortname()."</td>";
				
				print "\n\t\t\t\t\t<td class='migration_status_line'>";
				$status = $slot->getMigrationStatus();
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
						if ($slot->siteExists())
							print '<span class="status status_unneeded">No Longer Needed</span>';
						else
							print '<span class="status status_unneeded_empty">No Longer Needed</span>';
						break;
					default:
						print '<span class="status status_incomplete">Incomplete</span>';
				}
				print "</td>";
				
				$url = call_user_func(ARCHIVE_URL_CALLBACK, $slot->getShortname());
				print "\n\t\t\t\t\t<td class='archive'><a href='".$url."'>".$url."</a></td>";
				
				print "\n\t\t\t\t</tr>";
			}
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