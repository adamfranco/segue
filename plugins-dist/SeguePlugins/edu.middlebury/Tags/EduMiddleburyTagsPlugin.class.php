<?php
/**
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.9 2008/04/11 21:51:37 achapin Exp $
 */ 

require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/BreadCrumbsVisitor.class.php");
require_once(dirname(__FILE__)."/TaggableItemVisitor.class.php");
require_once(dirname(__FILE__)."/TagCloudNavParentVisitor.class.php");
require_once(dirname(__FILE__)."/ContainerInfoVisitor.class.php");
require_once(dirname(__FILE__)."/UmbrellaVisitor.class.php");

/**
 * A simple plugin for including links in a site
 * 
 * @since 3/12/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EduMiddleburyTagsPlugin.class.php,v 1.9 2008/04/11 21:51:37 achapin Exp $
 */
class EduMiddleburyTagsPlugin 
	extends SegueAjaxPlugin
//	extends SeguePlugin

{
			

	/**
 	 * Answer a description of the plugin (not the instance) to provide to 
 	 * users when choosing between what plugin to create.
 	 * 
 	 * @return string
 	 * @access public
 	 * @since 6/1/07
 	 * @static
 	 */
 	public static function getPluginDescription () {
 		 return _("The Tags plugin allows users to add a block that displays all the tags for a given node and its children"); 	
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
 		return _("Tags");
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
 		return array("Alex Chapin", "Adam Franco", "David Fouhey");
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
 		return '1.0';
 	}

	/**
	 * Update plugin data from requests
 	 * In our situation, update the options used
	 * by this particular instance of the plugin
	 * 
	 * @param array $request
	 * @return void
	 * @access public
	 * @since 6/17/08
	 */

	public function update( $request ) {
		if ($this->getFieldValue('tagNode'))
			$this->writeOption('targetNodeId', $this->getFieldValue('tagNode'));
		
		if ($this->getFieldValue('defaultSortMethod')) {
			$this->writeOption('defaultSortMethod', $this->getFieldValue('defaultSortMethod'));	
			$this->writeOption('defaultDisplayType', $this->getFieldValue('defaultDisplayType'));	
			$this->writeOption('defaultListLimit', $this->getFieldValue('defaultListLimit'));	
		}
	}
	
	/**
	 * @var array $_defaults;  
	 * @access private
	 * @since 2/4/09
	 */
	private $_defaults;
	
	/**
	 * @var array $_allowedOptions;  
	 * @access private
	 * @since 2/4/09
	 */
	private $_allowedOptions;
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @since 2/4/09
	 */
	public function initialize () {
		$this->_defaults = array(
			'defaultSortMethod' => 'alpha',
			'defaultDisplayType' => 'cloud',
			'defaultListLimit' => '15'
		);
		$this->_allowedOptions = array(
			'targetNodeId',
			'defaultSortMethod',
			'defaultDisplayType',
			'defaultListLimit'
		);
	}
	
	/**
	 * Read an option
	 * 
	 * @param string $key
	 * @return string
	 * @access protected
	 * @since 2/4/09
	 */
	protected function readOption ($key) {
		$doc = new Harmoni_DOMDocument();
		try {
			$doc->loadXML($this->getContent());
			$xpath = new DOMXPath($doc);
			$elements = $xpath->query('/options/'.$key);
			
			if ($elements->length && strlen($elements->item(0)->nodeValue))
				return $elements->item(0)->nodeValue;
			
		} catch (DOMException $e) {
		}
		
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
<options>
	<targetNodeId>12345</targetNodeId>
	<defaultSortMethod>alpha</defaultSortMethod>
	<defaultDisplayType>cloud</defaultDisplayType>
</options>
		*/
		
		if (!in_array($key, $this->_allowedOptions))
			throw new InvalidArgumentException("Unknown option, $key");
		
		
		$doc = new Harmoni_DOMDocument();
		$doc->preserveWhiteSpace = false;
		try {
			$doc->loadXML($this->getContent());
		} catch (DOMException $e) {
			$doc->appendChild($doc->createElement('options'));
		}
		
		if (!$doc->documentElement->nodeName == 'options')
			throw new OperationFailedException('Expection root-node "options", found "'.$doc->documentElement->nodeName.'".');
		
		// Fetch the existing element or create a new one for this key
		$xpath = new DOMXPath($doc);
		$elements = $xpath->query('/options/'.$key);
		if ($elements->length)
			$element = $elements->item(0);
		else
			$element = $doc->documentElement->appendChild($doc->createElement($key));
		
		
		// Set the value and save
		$element->nodeValue = $val;
		$this->setContent($doc->saveXMLWithWhitespace());
	}
	
	/**
	 * Answer the target node id
	 * 
	 * @return string
	 * @access protected
	 * @since 2/4/09
	 */
	protected function getTargetNodeId () {
		try {
			return $this->readOption('targetNodeId');
		} catch (OperationFailedException $e) {
			$visitor = new TagCloudNavParentVisitor;
			$director = SiteDispatcher::getSiteDirector();
			$node = $director->getSiteComponentById($this->getId());
			$parentNavNode = $node->acceptVisitor($visitor);
			return $parentNavNode->getId();
		}
	}

	/**
	 * Helper methods, writes out options for a selection box given 
	 * the variety of tree that the UmbrellaVisitor generates
	 *
	 * @return none
	 * @access public
	 * @since 9/23/08
	 */	
	public function writeUmbrellaSelect($node, $target, $depth = 0){
		$toWrite = $node[0];
		echo "<!-- ${target} -->\n";
		echo "<option value='".$toWrite[1]."'";
		if($target == $toWrite[1]){
			echo " selected";
		}
		echo ">";
		if($depth > 0){
			echo str_repeat("&nbsp;",($depth*5));	
		}
		echo $toWrite[0]."</option>\n";
		for($i = 1; $i < sizeof($node); $i++){
			$this->writeUmbrellaSelect($node[$i], $target, $depth+1);	
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
		
		if($this->getFieldValue('edit') && $this->canModify()){
			$director = SiteDispatcher::getSiteDirector();
			$node = $director->getSiteComponentById($this->getId());	

			$currentTarget = $this->getTargetNodeId();
 			$node = $director->getSiteComponentById($this->getId());
			print "\n".$this->formStartTagWithAction();
			$visitor = new UmbrellaVisitor;
			$node->acceptVisitor($visitor);
			print "<div>";
			
			print _('Chose a section or page:');
			print "<select name='".$this->getFieldName('tagNode')."'>";
			$this->writeUmbrellaSelect($visitor->getNodeData(),$currentTarget);
			print "</select>\n";
			print "<div class='tags_display_options'>"._('Only tags from your selected section or page will be displayed')."</div><br/>\n";
			
			print "<select name='".$this->getFieldName('defaultSortMethod')."'>";
			print "\n\t<option value='alpha' ";
			if ($this->readOption('defaultSortMethod') == 'alpha')
				print "selected='selected'";
			print ">"._('sort tags alphabetically')."</option>";
			print "\n\t<option value='freq' ";
			if ($this->readOption('defaultSortMethod') == 'freq')
				print "selected='selected'";
			print ">"._('sort tags by frequency')."</option>";
			print "</select>\n";
			
			print "<br/>";
			print "<select name='".$this->getFieldName('defaultDisplayType')."'>";
			print "\n\t<option value='cloud' ";
			if ($this->readOption('defaultDisplayType') == 'cloud')
				print "selected='selected'";
			print ">"._('display as cloud')."</option>";
			print "\n\t<option value='list' ";
			if ($this->readOption('defaultDisplayType') == 'list')
				print "selected='selected'";
			print ">"._('display as list')."</option>";
			print "</select>\n";
			
			print "<br/>";
			print "<select name='".$this->getFieldName('defaultListLimit')."'>";
			print "\n\t<option value='0' ";
			if ($this->readOption('defaultListLimit') == '0')
				print "selected='selected'";
			print ">"._('in list show: all')."</option>";
			for ($i = 5; $i < 25; $i = $i + 5) {
				print "\n\t<option value='$i' ";
				if (intval($this->readOption('defaultListLimit')) == $i)
					print "selected='selected'";
				print ">".str_replace('%1', $i, _('in list show: %1'))."</option>";
			}
			for ($i = 25; $i <= 300; $i = $i + 25) {
				print "\n\t<option value='$i' ";
				if (intval($this->readOption('defaultListLimit')) == $i)
					print "selected='selected'";
				print ">".str_replace('%1', $i, _('in list show: %1'))."</option>";
			}
			print "</select>\n";
			
			print "<br/>";
			print "<br/>";
			print "<input type='submit' value='Update' name='".$this->getFieldName('submit')."'>\n";
			print "\n\t<input type='button' value='"._('Cancel')."' onclick=".$this->locationSendString()."/>";
			print "</div>";
			print "</form>";
		} else if ($this->canView()) {
			$items = array();
	 		$director = SiteDispatcher::getSiteDirector();
	 		$node = $director->getSiteComponentById($this->getTargetNodeId());
 			 			
 			$visitor = new TaggableItemVisitor;
	 		$items = $node->acceptVisitor($visitor);
 		
 	

			print "\n<div class='breadcrumbs' style='height: auto; margin-top: 1px; margin-bottom: 5px; border-bottom: 1px dotted; padding-bottom: 2px;'>";
 			print str_replace('%1', $node->acceptVisitor(new BreadCrumbsVisitor($node)),
 			_("Tags within: %1"));

 			print "</div>";
			print "\n<div style='text-align: justify;' id='tag_cloud_container-".$this->getId()."'>";
			$tags = TagAction::getTagsFromItems($items);
 			//SiteDispatcher::passthroughContext();
			print TagAction::getTagCloudDiv($tags, 'sitetag', TagAction::getDefaultStyles(), array(), array(null => array('node' => $this->getTargetNodeId())));
			
 			//SiteDispatcher::forgetContext();
 			?>
 	
 	<script type="text/javascript">
 	// <![CDATA[
 	
 	var cloudParent = document.get_element_by_id('tag_cloud_container-<?php print $this->getId(); ?>');
 	var clouds = document.get_elements_by_class('tag_cloud', cloudParent);
 	var cloud = TagCloud.forContainer(clouds[0]);
 	
 			<?php
 			if ($this->readOption('defaultListLimit') != 15)
 				print "\n\tcloud.tagList.setLimit(".$this->readOption('defaultListLimit').");";
 			if ($this->readOption('defaultSortMethod') == 'freq')
 				print "\n\tcloud.orderFreq();";
 			if ($this->readOption('defaultDisplayType') == 'list')
 				print "\n\tcloud.showList();";
 	
 			?>

 	// ]]>
 	</script>
 	
 			
 			<?php
			print "</div>";
			if($this->shouldShowControls()){
				print "\n<div style='text-align: right; white-space: nowrap;'>";
				print "\n\t<a ".$this->href(array('edit' => 'true')).">"._('edit')."</a>";
				print "\n</div>";
			}
		
			/* 	
			$visitor = new UmbrellaVisitor;
			$node->acceptVisitor($visitor);
			print "<br/><pre>";
			print_r($visitor->getNodeData());
			print "</pre><br/>\n";
			*/



 		}
 						
		return ob_get_clean();
 	} 

}

?>
