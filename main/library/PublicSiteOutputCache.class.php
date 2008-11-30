<?

require_once(HARMONI."/utilities/PersistentCache.class.php");
require_once(HARMONI."/architecture/harmoni/OutputCache.interface.php");

define("SITE_CACHE_EXPIRY_TIME", 6000); // in seconds

class PublicSiteOutputCache implements OutputCache {

    function shouldCache(Harmoni $harmoni) {
        // no point in caching when we don't have a cache
        if (!HAVE_MEMCACHE) return false;

        return PublicSiteOutputCache::_getKey($harmoni)?true:false;
    }

    function fetch(Harmoni $harmoni) {
        return PersistentCache::get(PublicSiteOutputCache::_getKey($harmoni));
    }

    function store(Harmoni $harmoni, $content) {
        return PersistentCache::store(PublicSiteOutputCache::_getKey($harmoni), $content, SITE_CACHE_EXPIRY_TIME);
    }

    function invalidate() {
        // although this is non-ideal, we only have to do it
        // when something on a site changes.
        //
        // 1) traverse the site hierarchy for the root node of the site, and
        // 2) remove from the cache any entries pertaining to said node
		$idManager = Services::getService("Id");
		$hierarchyManager = Services::getService('Hierarchy');
        $harmoni = Harmoni::instance();

        $harmoni->request->startNamespace(null);
        $site = $harmoni->request->get("site");
        $node = $harmoni->request->get("node");
        $harmoni->request->endNamespace();

        if (!$site && !$node) {
            $harmoni->request->startNamespace("plugin_manager");
            $pluginId = $harmoni->request->get("plugin_id");
            $harmoni->request->endNamespace();
            if ($pluginId) {
                // trick the site dispatcher into thinking this is the
                // current node (which it is...)
                $harmoni->request->startNamespace(null);
                $harmoni->request->set("node", $pluginId);
                $harmoni->request->endNamespace();
            } else {
                return;
            }
        }

		$siteNode = SiteDispatcher::getCurrentRootNode();

		$hierarchy = $hierarchyManager->getHierarchy(
			$idManager->getId("edu.middlebury.authorization.hierarchy"));
		$infoList = $hierarchy->traverse(
			$idManager->getId($siteNode->getId()),
			Hierarchy::TRAVERSE_MODE_DEPTH_FIRST,
			Hierarchy::TRAVERSE_DIRECTION_DOWN,
			Hierarchy::TRAVERSE_LEVELS_ALL);
		
		while ($infoList->hasNext()) {
			$info = $infoList->next();
            PersistentCache::remove(PublicSiteOutputCache::_getKeyFromParts("anonymous", $info->getNodeId()));
            PersistentCache::remove(PublicSiteOutputCache::_getKeyFromParts("institute", $info->getNodeId()));
		}
    }

    function _getKeyFromParts($user, $node) {
        return "siteCache:$user:$node";
    }

    function _getKey(Harmoni $harmoni) {
        // check authn, and see if we are logged in as anybody
        // specific. if so, we shouldn't cache it.
        $valid = array(
            "edu.middlebury.agents.anonymous" => false,
            "edu.middlebury.institude" => false
        );

        $authN = Services::getService("AuthN");
        $authTypes = $authN->getAuthenticationTypes();
        while ($authTypes->hasNext()) {
            $id = $authN->getUserId($authTypes->next());
            if (!in_array($id->getIdString(), array_keys($valid))) {
                return null;
            }

            $valid[$id->getIdString()] = true;
        }

        $user = "anonymous";
        if ($valid["edu.middlebury.institute"]) {
            $user = "institute";
        }

        if ($harmoni->getCurrentAction() == "view.html") {
            $harmoni->request->startNamespace(null);
            $site = $harmoni->request->get("site");
            $node = $harmoni->request->get("node");
            $harmoni->request->endNamespace();

            if ($site) {
                if (!$node) {
                    $harmoni->request->startNamespace(null);
                    $node = SiteDispatcher::getCurrentRootNode()->getNodeId();
                    $harmoni->request->endNamespace();
                }
                return PublicSiteOutputCache::_getKeyFromParts($user, $node);
            }
        }

        return null;
    }

}
