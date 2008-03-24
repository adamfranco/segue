<?php
/**
 * @since 1/7/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginVersion.class.php,v 1.5 2008/03/24 16:29:30 adamfranco Exp $
 */ 

/**
 * The SeguePluginVersion is a data container for accessing information about and data
 * for a version of a plugin. This class is not part of the Plugin API and is used only
 * by the Plugin driver.
 * 
 * @since 1/7/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginVersion.class.php,v 1.5 2008/03/24 16:29:30 adamfranco Exp $
 */
class SeguePluginVersion {
	
	/**
	 * @var object SeguePlugin $pluginInstance
	 * @access private
	 * @since 1/7/08
	 */
	private $pluginInstance;
	
	/**
	 * @var string $versionId;  
	 * @access private
	 * @since 1/7/08
	 */
	private $versionId;
	
	/**
	 * @var object DateAndTime $timestamp
	 * @access private
	 * @since 1/7/08
	 */
	private $timestamp;
	
	/**
	 * @var object Id $agentId
	 * @access private
	 * @since 1/7/08
	 */
	private $agentId;
	
	/**
	 * @var int $number;  
	 * @access private
	 * @since 1/7/08
	 */
	private $number;
	
	/**
	 * @var string $comment;  
	 * @access private
	 * @since 1/7/08
	 */
	private $comment;
	
	/**
	 * @var object DOMDocument $versionXml;  
	 * @access private
	 * @since 1/7/08
	 */
	private $versionXml;
		
	/**
	 * Constructor
	 * 
	 * @param object SeguePluginsAPI $pluginInstance
	 * @param string $versionId
	 * @param object DateAndTime $timestamp
	 * @param object Id $agentId
	 * @param int $number
	 * @param string $comment
	 * @return void
	 * @access public
	 * @since 1/7/08
	 */
	public function __construct (SeguePluginsAPI $pluginInstance, $versionId, 
		DateAndTime $timestamp, Id $agentId, $number, $comment) 
	{
		ArgumentValidator::validate($versionId, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($number, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($comment, StringValidatorRule::getRule());
		
		$this->pluginInstance = $pluginInstance;
		$this->versionId = $versionId;
		$this->timestamp = $timestamp;
		$this->agentId = $agentId;
		$this->number = $number;
		$this->comment = $comment;
	}
	
	/**
	 * Answer the version id
	 * 
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersionId () {
		return $this->versionId;
	}
	
	/**
	 * Answer the timestamp at which this version was created.
	 * 
	 * @return DateAndTime
	 * @access public
	 * @since 1/7/08
	 */
	public function getTimestamp () {
		return $this->timestamp;
	}
	
	/**
	 * Answer the agent Id responsible for this version.
	 * 
	 * @return Id
	 * @access public
	 * @since 1/7/08
	 */
	public function getAgentId () {
		return $this->agentId;
	}
	
	/**
	 * Answer the agent responsible for this version
	 * 
	 * @return Agent
	 * @access public
	 * @since 1/7/08
	 */
	public function getAgent () {
		$agentMgr = Services::getService('Agent');
		return $agentMgr->getAgent($this->getAgentId());
	}
	
	/**
	 * Answer the number of the version in the history sequence
	 *
	 * @return int
	 * @access public
	 * @since 1/7/08
	 */
	public function getNumber () {
		return $this->number;
	}
	
	/**
	 * Answer the comment stored about this version
	 * 
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	public function getComment () {
		return $this->comment;
	}
	
	/**
	 * Answer the plugin instance this version is of.
	 *
	 * @return SeguePluginsAPI
	 * @access public
	 * @since 1/7/08
	 */
	public function getPluginInstance () {
		return $this->pluginInstance;
	}
	
	/**
	 * Answer the XML document that describes this version.
	 * 
	 * @return DOMDocument
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersionXml () {
		if (!isset($this->versionXml)) {
			$query = new SelectQuery;
			$query->addTable('segue_plugin_version');
			$query->addWhereEqual('version_id', $this->getVersionId());
			$query->addColumn('version_xml');
			
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			
			$this->versionXml = new Harmoni_DOMDocument;
			$this->versionXml->loadXML($result->field('version_xml'));
		}
		
		return $this->versionXml;
	}
	
	/**
 	 * Answer a string of XHTML markup that displays the plugin state representation
 	 * in this version.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function getMarkup () {
 		return $this->pluginInstance->executeAndGetVersionMarkup($this->getVersionXml());
 	}
 	
 	/**
 	 * Update the plugin state to match the representation passed in this version. This
 	 * method will also mark a new version with the comment provided.
 	 * 
 	 * @param optional string $comment
 	 * @return void
 	 * @access public
 	 * @since 1/4/08
 	 */
 	public function apply ($comment = "") {
 		$this->pluginInstance->applyVersion($this->getVersionXml());
 		$this->pluginInstance->markVersion($comment);
 	}
 	
 	/**
 	 * Answer true if this version is the current version.
 	 *
 	 * @return boolean
 	 * @access public
 	 * @since 1/8/08
 	 */
 	public function isCurrent () {
 		$query = new SelectQuery;
		$query->addTable('segue_plugin_version');
		$query->addColumn('version_id');
		$query->addWhereEqual('node_id', $this->pluginInstance->getId());
		$query->addOrderBy('tstamp', SORT_DESC);
		$query->limitNumberOfRows(1);
		
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		if ($result->field('version_id') == $this->getVersionId())
			return true;
		else
			return false;
 	}
 	
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
 		$doc = $this->getVersionXml();
 		$this->pluginInstance->replaceIdsInVersion($idMap, $doc);
 		
 		$query = new UpdateQuery;
		$query->setTable('segue_plugin_version');
		$query->addWhereEqual('version_id', $this->getVersionId());
		$query->addValue('version_xml', $doc->saveXML());
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
 	}
}

?>