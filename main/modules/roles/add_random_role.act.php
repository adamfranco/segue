<?php
/**
 * @since 11/14/07
 * @package segue.roles
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.7 2007/11/29 20:25:34 adamfranco Exp $
 */

require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsAuthorizableVisitor.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__)."/Visitors/PopulateRolesVisitor.class.php");


/**
 * An action for adding a random role to a random # of users for the passed-in site.
 *
 * This is used exclusively for seeding a database for performance testing.
 *
 * @since 11/14/07
 * @package segue.roles
 *
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.7 2007/11/29 20:25:34 adamfranco Exp $
 */
class add_random_roleAction
	extends MainWindowAction
{

	/**
	 * Check Authorizations
	 *
	 * @return boolean
	 * @access public
	 * @since 11/14/07
	 */
	public function isAuthorizedToExecute () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"),
				$this->getSiteId());

	}

	/**
	 * Answer the site id.
	 *
	 * @return object Id
	 * @access protected
	 * @since 11/14/07
	 */
	protected function getSiteId () {
		return SiteDispatcher::getCurrentRootNode()->getQualifierId();
	}

	protected function getSite () {
		return SiteDispatcher::getCurrentRootNode();
	}


	/**
	 * Build the content for this action
	 *
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$idMgr = Services::getService("Id");
		$agentMgr = Services::getService("Agent");
		$roleMgr = SegueRoleManager::instance();

		$actionRows = $this->getActionRows();
        $siteId = $this->getSiteId();

        $roles = $roleMgr->getRoles();
        $num = count($roles);

        // grab a random role
        $randomRole = $roles[rand(0, $num - 1)];

        // grab a random user
        $agentsIter = $agentMgr->getAgents();
        $agentsArray = array();
        while($agentsIter->hasNext()) {
            $agentsArray[] = $agentsIter->next();
        }
        $randomAgent = $agentsArray[rand(0, count($agentsArray))];

        $randomRole->apply($randomAgent->getId(), $this->getSiteId());

		$actionRows->add(new Heading(_("Random Role Assignment Booyah"), 2));

		ob_start();
		print "\n<ul>";
        print "\n\t<li>Site ID: " . $this->getSiteId() . "</li>\n";
        print "\n\t<li>Role: " . $randomRole->getIdString() . "/" . $randomRole->getDisplayName() . "</li>\n";
        print "\n\t<li>Agent: " . $randomAgent->getId() . "/" . $randomAgent->getDisplayName() . "</li>\n";
		print "\n</ul>";

		$introText = new Block(ob_get_contents(),2);
		$actionRows->add($introText, "100%", null, CENTER, CENTER);
		ob_end_clean();
	}

	/**
	 * Return the URL that this action should return to when completed.
	 *
	 * @return string
	 * @access public
	 * @since 11/14/07
	 */
	function getReturnUrl () {
		$wizard = $this->getWizard($this->cacheName);

		$harmoni = Harmoni::instance();

		$chooseUserListener = $wizard->getChild('choose_user');
		if ($chooseUserListener->wasPressed())
			return $harmoni->request->quickURL('roles', 'choose_agent');
		else {
			if (RequestContext::value('returnModule'))
				$module = RequestContext::value('returnModule');
			else
				$module = 'ui1';

			if (RequestContext::value('returnAction'))
				$action = RequestContext::value('returnAction');
			else
				$action = 'editview';
			return $harmoni->request->quickURL($module, $action);
		}
	}
}

?>
