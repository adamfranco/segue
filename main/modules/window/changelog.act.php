<?php
/**
 * @since 12/13/06
 * @package segue.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: changelog.act.php,v 1.1 2007/09/25 14:49:16 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * <##>
 * 
 * @since 12/13/06
 * @package concerto.modules.window
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: changelog.act.php,v 1.1 2007/09/25 14:49:16 adamfranco Exp $
 */
class changelogAction
	extends Action
{
		
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 12/13/06
	 */
	function execute () {
		
		switch (RequestContext::value('package')) {
			case 'harmoni':
				$currentPackage = 'harmoni';
				$file = HARMONI_BASE."docs/changelog.html";
				$source = file_get_contents($file);
				break;
			case 'polyphony':
				$currentPackage = 'polyphony';
				$file = POLYPHONY."/docs/changelog.html";
				$source = file_get_contents($file);
				break;
			case 'viewer':
				$currentPackage = 'viewer';
				$file = VIEWER_URL."/README.txt";
				$source = 
"<html>
	<head>
		<title>Viewer Changelog</title>
	</head>
	<body>
		<pre>".htmlentities(file_get_contents($file))."</pre>
	</body>
</html>
";
				break;
			default:
				$currentPackage = 'concerto';
				$file = MYDIR."/doc/changelog.html";
				$source = file_get_contents($file);
				break;
		}
		
		$menu = $this->generateMenu($currentPackage);
		
		// insert the menu into the file
		print str_replace('<body>', '<body>'.$menu, $source);
		exit;
	}
	
	/**
	 * Answer the menu of changelogs
	 * 
	 * @param string $currentPackage
	 * @return string
	 * @access public
	 * @since 12/13/06
	 */
	function generateMenu ($currentPackage) {
		$harmoni = Harmoni::instance();
		
		$packages = array(
			'segue' 	=> 'Segue Changelog',
			'harmoni' 	=> 'Harmoni Changelog',
			'polyphony'	=> 'Polyphony Changelog',
// 			'viewer'	=> 'Concerto Viewer Changelog',
		);
		
		$menuItems = array();		
		foreach ($packages as $key => $name) {
			ob_start();
			
			if ($currentPackage == $key)
				print $name;
			else {
				print "<a href='";
				print $harmoni->request->quickURL('window', 'changelog',
						array('package' => $key));
				print "'>".$name."</a>";
			}
			
			$menuItems[] = ob_get_clean();
		}
		
		return "<div>".implode(' | ', $menuItems)."</div>";
	}
	
}

?>