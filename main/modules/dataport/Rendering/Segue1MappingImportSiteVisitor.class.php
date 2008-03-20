<?php
/**
 * @since 3/20/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1MappingImportSiteVisitor.class.php,v 1.1 2008/03/20 15:45:52 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/DomImportSiteVisitor.class.php");

/**
 * This class imports sites as well as stores a mapping between old (Segue1) Ids 
 * and new Ids. The most recent import of a segue1 site will take over any mappings 
 * from previous imports of that site.
 * 
 * @since 3/20/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1MappingImportSiteVisitor.class.php,v 1.1 2008/03/20 15:45:52 adamfranco Exp $
 */
class Segue1MappingImportSiteVisitor
	extends DomImportSiteVisitor
{
		
	/**
	 * Create a new Site and import the source data into it.
	 * 
	 * @return object SiteNavBlockSiteComponent
	 * @access public
	 * @since 1/22/08
	 */
	public function importSite () {
		$site = parent::importSite();
		
		$this->storeSegue1IdMapping();
		
		return $site;
	}
	
	/**
	 * @var string $origenSlotname;  
	 * @access private
	 * @since 3/20/08
	 */
	private $origenSlotname;
	
	/**
	 * @var string $destSlotname;  
	 * @access private
	 * @since 3/20/08
	 */
	private $destSlotname;
	
	/**
	 * Set the origen slot name
	 * 
	 * @param string $slotname
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function setOrigenSlotname ($slotname) {
		ArgumentValidator::validate($slotname, NonzeroLengthStringValidatorRule::getRule());
		$this->origenSlotname = $slotname;
	}
	
	/**
	 * Set the destination slot name
	 * 
	 * @param string $slotname
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function setDestinationSlotname ($slotname) {
		ArgumentValidator::validate($slotname, NonzeroLengthStringValidatorRule::getRule());
		$this->destSlotname = $slotname;
	}
	
	/**
	 * Store a mapping between Segue1 ids and Segue2 ids
	 * 
	 * @return void
	 * @access protected
	 * @since 3/20/08
	 */
	protected function storeSegue1IdMapping () {
		if (!isset($this->origenSlotname))
			throw new OperationFailedException("Origen slot not set. Call ".get_class($this)."->setOrigenSlotname('xxxxx').");
		if (!isset($this->destSlotname))
			throw new OperationFailedException("Destination slot not set. Call ".get_class($this)."->setDestinationSlotname('xxxxx').");
				
		$dbc = Services::getService('DatabaseManager');
		$map = $this->filterNonAccessible($this->getIdMap());
		
		
// 		printpre(htmlentities($this->doc->saveXMLWithWhitespace()));
// 		printpre($map);
// 		throw new Exception('test');
		
		// Delete any old mappings
		$query = new DeleteQuery;
		$query->setTable('segue1_id_map');
		$query->addWhereIn('segue1_id', array_keys($map));
		$dbc->query($query, IMPORTER_CONNECTION);
		
		// Add new mappings
		$query = new InsertQuery;
		$query->setTable('segue1_id_map');
		foreach ($map as $segue1Id => $segue2Id) {
			$query->createRow();
			$query->addValue('segue1_slot_name', $this->origenSlotname);
			$query->addValue('segue1_id', $segue1Id);
			$query->addValue('segue2_slot_name', $this->destSlotname);
			$query->addValue('segue2_id', $segue2Id);
		}
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Filter ids that don't correspond to accessible segue 1 site components.
	 * 
	 * @param array $map
	 * @return array
	 * @access private
	 * @since 3/20/08
	 */
	private function filterNonAccessible ($map) {
		$newMap = array();
		foreach ($map as $segue1Id => $segue2Id) {
			if (preg_match('/^site_|section_|page_|story_|comment_/', $segue1Id))
				$newMap[$segue1Id] = $segue2Id;
		}
		return $newMap;
	}
}

?>