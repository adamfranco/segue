<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(dirname(__FILE__).'/send_migration_reminders.act.php');

/**
 * Send emails to users reminding them to deal with their sites.
 * 
 * 
 * 
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 */
class list_incomplete_migrationsAction
	extends send_migration_remindersAction
{
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
	".$_SERVER['argv'][0]." [-h|--help]
	
	-h,--help  Print out this help text.
");
		}
		
		while(ob_get_level())
			ob_end_flush();
		flush();
		
		print "Preparing incomplete migration info...\n";
		
		$this->buildUserSiteList();
		$this->printList();
		
		exit;
	}
	
	/**
	 * Email users.
	 * 
	 * @return null
	 */
	protected function printList () {
				
		$total = count($this->users);
		print "\n".$total." users have incomplete migration status.\n";
		print "EMail	Name	Num Incomplete	1st Incomplete	2nd Incomplete	3rd Incomplete	Additional Incomplete\n";
		foreach ($this->users as $user) {
			print $user['email'];
			print "\t".$user['name'];
			print "\t".count($user['slots']);
			print "\t";
			if (!empty($user['slots'][0]))
				print $user['slots'][0]->getShortname();
			print "\t";
			if (!empty($user['slots'][1]))
				print $user['slots'][1]->getShortname();
			print "\t";
			if (!empty($user['slots'][2]))
				print $user['slots'][2]->getShortname();
			print "\t";
			if (!empty($user['slots'][3]))
				print (count($user['slots']) - 3).' more...';
			print "\n";
		}
	}
}

?>