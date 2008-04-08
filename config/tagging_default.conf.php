<?php

/**
 * Set up the IdManager as this is required for the ID service
 *
 * USAGE: Copy this file to id.conf.php to set custom values.
 *
 * @package concerto.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: tagging_default.conf.php,v 1.2 2008/04/08 20:57:37 achapin Exp $
 */
 
 	$configuration = new ConfigurationProperties;
	$configuration->addProperty('DatabaseIndex', $dbID);
	$systems = array(
		'concerto' => array(
			'ItemClass' 	=> 'HarmoniNodeTaggedItem',
			'HierarchyId'	=> 'edu.middlebury.authorization.hierarchy',
			'UrlCallback'	=> 'getConcertoNodeUrl',
			'ThumbUrlCallback'	=> 'getConcertoNodeThumbnailUrl',
		),
		'segue' => array(
			'ItemClass' 	=> 'HarmoniNodeTaggedItem',
			'HierarchyId'	=> 'edu.middlebury.authorization.hierarchy',
			'UrlCallback'	=> 'getSegueNodeUrl',
// 			'ThumbUrlCallback'	=> 'getSegueNodeThumbnailUrl',
		),
	);
	$configuration->addProperty('Systems', $systems);
	Services::startManagerAsService("TagManager", $context, $configuration);

	define('POLYPHONY_TAGGEDITEM_PRINTING_CALLBACK', 'concertoPrintTaggedItem');


/**
 * Print out an Item
 * 
 * @param object $item
 * @return object GuiComponent
 * @access public
 * @since 11/8/06
 */
function concertoPrintTaggedItem ( $item, $viewAction) {
	printTaggedItem($item, $viewAction);
	
	if ($item->getSystem() != ARBITRARY_URL) {
		print "<p style='font-size: small;'>";
		print "<a ";
		print " style='cursor: pointer;'";
		print " onclick='Basket.addAssets(new Array(\"".$item->getIdString()."\"));'";
		print ">"._('+ Selection');
		print "</a>";
		print "</p>";
	}
}

/**
 * Answer the url for a node of a give Id.
 * 
 * @param object HarmoniNodeTaggedItem $item
 * @return string
 * @access public
 * @since 11/8/06
 */
function getConcertoNodeUrl ( $item ) {
	$concertoBaseUrl = MYURL.'?';
//	$concertoBaseUrl = 'http://concerto.middlebury.edu/index.php?';
	
	
	$node =$item->getNode();
	$nodeType =$node->getType();
	
	$domainsToIgnore = array('authorization', 'Authorization', 'System', 'Agents');
	
	// Repositories
	if ($nodeType->isEqual(
		new Type('Repository', 'edu.middlebury.harmoni', 'Repository'))) 
	{
		return $concertoBaseUrl.'&module=collection&action=browse&collection_id='.$item->getIdString();
	} 
	// Assets
	else if (!in_array($nodeType->getDomain(), $domainsToIgnore)) {
		return $concertoBaseUrl.'&module=asset&action=view&asset_id='.$item->getIdString();
	} else {
		return '';
	}
}

/**
 * Answer the url for a node of a give Id.
 * 
 * @param object HarmoniNodeTaggedItem $item
 * @return string
 * @access public
 * @since 11/8/06
 */
function getConcertoNodeThumbnailUrl ( $item ) {
	$concertoBaseUrl = MYURL.'?';
//	$concertoBaseUrl = 'http://concerto.middlebury.edu/index.php?';
	
	
	$node =$item->getNode();
	$nodeType =$node->getType();
	
	$domainsToIgnore = array('authorization', 'Authorization', 'System', 'Agents');
	
	// Repositories
	if ($nodeType->isEqual(
		new Type('Repository', 'edu.middlebury.harmoni', 'Repository'))) 
	{
		return '';
	} 
	// Assets
	else if (!in_array($nodeType->getDomain(), $domainsToIgnore)) {
		return $concertoBaseUrl.'&module=repository&action=viewthumbnail&polyphony-repository___asset_id='.$item->getIdString();
	} else {
		return '';
	}
}

/**
 * Answer the url for a node of a give Id.
 * 
 * @param object HarmoniNodeTaggedItem $item
 * @return string
 * @access public
 * @since 11/8/06
 */
function getSegueNodeUrl ( $item ) {
	$harmoni  = Harmoni::instance();
	$harmoni->request->startNamespace(null);
	$url = $harmoni->request->quickURL('view', 'html', array('node' => $item->getIdString()));
	$harmoni->request->endNamespace();
	return $url;
}