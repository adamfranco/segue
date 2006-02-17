<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: nav_settings.act.php,v 1.5 2006/02/17 22:25:37 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: nav_settings.act.php,v 1.5 2006/02/17 22:25:37 adamfranco Exp $
 */
class nav_settingsAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can create an asset here.
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		 
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"),
			$idManager->getId(RequestContext::value('node')));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create an <em>Asset</em> here.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("return_node");
		$idManager =& Services::getService("Id");
		
		$id =& $idManager->getId(RequestContext::value('node'));
		
		$this->_cacheName = 'nav_settings_'.$id->getIdString();
		
		$this->runWizard ( $this->_cacheName, $this->getActionRows() );
	}
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		$asset =& $repository->getAsset($idManager->getId(RequestContext::value('node')));
	
		return _("Settings the ")."<em>".$asset->getDisplayName()."</em> "._("Node");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function &createWizard () {
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		$asset =& $repository->getAsset($idManager->getId(RequestContext::value('node')));
		$null = null;
		$renderer =& NodeRenderer::forAsset($asset, $null);
		
		
		// Instantiate the wizard, then add our steps.
		$wizard =& SimpleStepWizard::withDefaultLayout();
		
	// :: Name and Description ::
		$step =& $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$property =& $step->addComponent("display_name", new WTextField());
		$property->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$property->setValue($asset->getDisplayName());
		
		$property =& $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
		$property->setValue($asset->getDescription());
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Asset</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Asset</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_clean());
		
		
	
	// :: Layout Step ::
		$step =& $wizard->addStep("layoutstep", new WizardStep());
		$step->setDisplayName(_("Layout"));
		
		$numCells = $renderer->getNumCells();
		$arrangement = $renderer->getLayoutArrangement();
		$targetOverride = $renderer->getTargetOverride();
		
		// Create the properties.
		$property =& $step->addComponent("cells", new WSelectList());
		for ($i = 1; $i <= 4; $i++) {
			$property->addOption($i, $i);
		}
		$property->setValue($numCells);
		$property->setOnChange("updateLayoutDisplay(this.form);");
		
		
		$property =& $step->addComponent("arrangement", new WSelectList());
		$property->addOption('columns', 'Columns');
		$property->addOption('rows', 'Rows');
		$property->addOption('nested', 'Nested');
		$property->setValue($arrangement);
		$property->setOnChange("updateLayoutDisplay(this.form);");
		
		$property =& $step->addComponent("targetoverride", new WSelectList());
		for ($i = 1; $i <= 4; $i++) {
			$property->addOption($i, $i);
		}
		$property->setValue($targetOverride);
		$property->setOnChange("updateLayoutDisplay(this.form);");
		
		// create the text
		ob_start();
		print "\n<table><tr><td valign='top'>";
		print "\n\t<table>";
		print "\n\t\t<tr><th valign='top'>"._("Arrangement")."</th></tr>";
		print "\n\t\t<tr><td valign='top' style='padding-bottom: 15px'>[[arrangement]]</td></tr>";
		
		print "\n\t\t<tr><th valign='top' style='white-space: nowrap'>"._("Number of Cells")."</th></tr>";
		print "\n\t\t<tr><td valign='top' style='padding-bottom: 15px'>[[cells]]</td></tr>";
		
		print "\n\t\t<tr><th valign='top'>"._("Target")."</th></tr>";
		print "\n\t\t<tr><td valign='top' style='padding-bottom: 15px'>[[targetoverride]]</td></tr>";
		
		print "\n\t</table>";
		print "\n</td><td valign='top'>";
		
		$sampleText = _('This is some sample text. ');
		$linkText = -('link');
		$targetText = _('Target:<br/>Where links will be displayed.');
		$formName = $this->_cacheName."_form";
		$errorText = _('Error: Javascript is required to display this interface. Please enable javascript in your browser.');
		print<<<END

<script type='text/javascript'>
/* <![CDATA[ */

	/**
	 * Inititialize the display based on the form name
	 * 
	 * @param string formName
	 * @return void
	 * @access public
	 * @since 2/17/06
	 */
	function initializeLayoutDisplay (formName) {
		for (var i = 0; i < document.forms.length; i++) {
			if (document.forms[i].name == formName) {
				var form = document.forms[i];
				break;
			}
		}		
		updateLayoutDisplay(form);
	}
	
	/**
	 * Render the layout display.
	 * 
	 * @param string elementName
	 * @param string elementKey
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function updateLayoutDisplay ( form ) {		
		var inputs = form.elements;
		for (var i = 0; i < inputs.length; i++) {
			if (inputs[i].name.match(/^.*cells$/))
				var numCellsInput = inputs[i];
			else if (inputs[i].name.match(/^.*arrangement$/))
				var arrangementInput = inputs[i];
			else if (inputs[i].name.match(/^.*targetoverride$/))
				var targetOverrideInput = inputs[i];
		}
		
	// Error Checking
		// Nested must have at least two cells
		if (arrangementInput.value == 'nested' && numCellsInput.value < 2)
			numCellsInput.value = 2;
		
		// Nested Override cannot be 1.
		if (arrangementInput.value == 'nested' && targetOverrideInput.value < 2)
			targetOverrideInput.value = 2;
		
		// Override must be in bounds.
		if (targetOverrideInput.value > numCellsInput.value && numCellsInput.value > 1)
			targetOverrideInput.value = numCellsInput.value;
		
		renderLayoutDisplay(numCellsInput.value, arrangementInput.value, targetOverrideInput.value);
	}
	
	/**
	 * Render the layout display.
	 * 
	 * @param integer numCells
	 * @param string arrangment
	 $ @param integer targetOverride
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderLayoutDisplay (numCells, arrangement, targetOverride) {
		var destination = getElementFromDocument('layout_display');
		
		destination.innerHTML = '';
		var table = document.createElement('table');
		destination.appendChild(table);
		table.border = 1;		
		
		switch (arrangement) {
			case 'columns':
				if (numCells == 1)
					renderColumn(table, 5);
				else
					renderColumnDisplay(table, numCells, targetOverride);
				break;
			case 'rows':
				if (numCells == 1)
					renderColumn(table, 5);
				else
					renderRowDisplay(table, numCells, targetOverride);
				break;
			case 'nested':
				renderNestedDisplay(table, numCells, targetOverride);
				break;
			default:
				alert("Unknown arrangement, '" + arrangement +"'");
		}
	}
	
	/**
	 * Render the the rows and columns of a 'columns' display
	 * 
	 * @param node table
	 * @param integer numCells
	 * @param integer targetOverride
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderColumnDisplay ( table, numCells, targetOverride ) {
		var row = document.createElement('tr');
		table.appendChild(row);
		
		var selectedRendered = false;
		for (var i = 1; i <= numCells; i++) {
			var column = document.createElement('td');
			row.appendChild(column);
			
			column.style.verticalAlign = 'top';
			
			if (i == targetOverride) {
				column.style.backgroundColor = '#afa';
				column.style.width = '400px';
				column.innerHTML = '$targetText';
				column.style.verticalAlign = 'top';
				column.style.padding = '10px';
			} else {
				if (!selectedRendered) {
					renderSelectedColumn(column);
					selectedRendered = true;
				} else
					renderColumn(column);
				column.style.backgroundColor = '#aaf';
				column.style.width = '200px';
			}
		}
	}
	
	/**
	 * Render the the rows and columns of a 'row' display
	 * 
	 * @param node table
	 * @param integer numCells
	 * @param integer targetOverride
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderRowDisplay ( table, numCells, targetOverride ) {
		
		var selectedRendered = false;
		for (var i = 1; i <= numCells; i++) {
			var row = document.createElement('tr');
			table.appendChild(row);
			var column = document.createElement('td');
			row.appendChild(column);
			
			column.style.verticalAlign = 'top';
			
			if (i == targetOverride) {
				column.style.backgroundColor = '#afa';
				column.style.height = '400px';
			} else {
				if (!selectedRendered) {
					renderSelectedRow(column);
					selectedRendered = true;
				} else
					renderRow(column);
				column.style.backgroundColor = '#aaf';
// 				column.style.width = '200px';
			}
		}
	}
	
	/**
	 * Render a column with link and text blocks
	 * 
	 * @param node column
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderSelectedColumn ( column, num ) {
		if (!num)
			var num = 3;
		
		var table = document.createElement('table');
		column.appendChild(table);
		
		for (var i = 0; i < num; i++) {
			var row = document.createElement('tr');
			table.appendChild(row);
			var col = document.createElement('td');
			row.appendChild(col);
			col.innerHTML = '&lt; link &gt;';
			col.style.border = '1px dotted';
			col.style.margin = '5px';
			col.style.padding = '3px';
			if (i == 0)
				col.style.backgroundColor = '#77f';
			else
				col.style.backgroundColor = '#99f';
		}
		
		var row = document.createElement('tr');
		table.appendChild(row);
		var col = document.createElement('td');
		row.appendChild(col);
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.style.border = '1px dotted';
		col.style.margin = '3px';
		col.style.backgroundColor = '#99f';
	}
	
	/**
	 * Render a column with link and text blocks
	 * 
	 * @param node column
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderColumn ( column, num ) {
		if (!num)
			var num = 3;
			
		var table = document.createElement('table');
		column.appendChild(table);
		
		for (var i = 0; i < num; i++) {
			var row = document.createElement('tr');
			table.appendChild(row);
			var col = document.createElement('td');
			row.appendChild(col);
			text = "$sampleText";
			for (j = 0; j < 7; j++)
				col.innerHTML += text;
			col.style.border = '1px dotted';
			col.style.margin = '5px';
			col.style.padding = '3px';
			col.style.backgroundColor = '#99f';
		}
	}
	
	/**
	 * Render a column with link and text blocks
	 * 
	 * @param node column
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderSelectedRow ( column ) {
		var table = document.createElement('table');
		column.appendChild(table);
		var row = document.createElement('tr');
		table.appendChild(row);
		
		for (var i = 0; i < 5; i++) {
			var col = document.createElement('td');
			row.appendChild(col);
			col.innerHTML = '&lt; link &gt;';
			col.style.border = '1px dotted';
			col.style.margin = '5px';
			col.style.padding = '3px';
			col.style.whiteSpace = 'nowrap';
			if (i == 0)
				col.style.backgroundColor = '#77f';
			else
				col.style.backgroundColor = '#99f';
		}
		
		var col = document.createElement('td');
		row.appendChild(col);
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.style.border = '1px dotted';
		col.style.margin = '3px';
		col.style.backgroundColor = '#99f';
	}
	
	/**
	 * Render a column with link and text blocks
	 * 
	 * @param node column
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderRow ( column ) {
		var table = document.createElement('table');
		column.appendChild(table);
		var row = document.createElement('tr');
		table.appendChild(row);
		
		for (var i = 0; i < 3; i++) {
			var col = document.createElement('td');
			row.appendChild(col);
			text = "$sampleText";
			for (j = 0; j < 7; j++)
				col.innerHTML += text;
			col.style.border = '1px dotted';
			col.style.margin = '5px';
			col.style.padding = '3px';
			col.style.backgroundColor = '#99f';
		}
	}
	
	/**
	 * Render the the rows and columns of a 'columns' display
	 * 
	 * @param node table
	 * @param integer numCells
	 * @param integer targetOverride
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderNestedDisplay ( table, numCells, targetOverride ) {
		var row = document.createElement('tr');
		table.appendChild(row);
		
		var selectedRendered = false;
		for (var i = 1; i <= numCells; i++) {
			var column = document.createElement('td');
			row.appendChild(column);
			
			column.style.verticalAlign = 'top';
			
			if (i == 1) {
				column.style.backgroundColor = '#faa';
				column.style.width = '200px';
				renderNestedColumn(column);			
			} else if (i == targetOverride) {
				column.style.backgroundColor = '#afa';
				column.style.width = '400px';
				column.innerHTML = '$targetText';
				column.style.verticalAlign = 'top';
				column.style.padding = '10px';
			} else {
				renderColumn(column);
				column.style.backgroundColor = '#aaf';
				column.style.width = '200px';
			}
		}
	}
	
	/**
	 * Render a column with link and text blocks
	 * 
	 * @param node column
	 * @return void
	 * @access public
	 * @since 2/16/06
	 */
	function renderNestedColumn ( column, num ) {
		if (!num)
			var num = 3;
		
		var table = document.createElement('table');
		column.appendChild(table);
		
		for (var i = 0; i < num; i++) {
			var row = document.createElement('tr');
			table.appendChild(row);
			var col = document.createElement('td');
			row.appendChild(col);
			col.innerHTML = '&lt; link &gt;';
			col.style.border = '1px dotted';
			col.style.margin = '5px';
			col.style.padding = '3px';
			if (i == 1) {
				col.style.backgroundColor = '#f77';
				
				var row = document.createElement('tr');
				table.appendChild(row);
				var col = document.createElement('td');
				row.appendChild(col);
// 				col.style.border = '1px dotted';
				col.style.margin = '5px';
				col.style.padding = '3px';
				col.style.paddingLeft = '15px';
				col.style.backgroundColor = '#faa';
				
				// box for the children to sit in.
				var box = document.createElement('div');
				col.appendChild(box);
				box.style.border = '1px dotted';
				box.style.backgroundColor = '#aaf';
				
				renderSelectedColumn(box);
			} else
				col.style.backgroundColor = '#f99';
		}
		
		var row = document.createElement('tr');
		table.appendChild(row);
		var col = document.createElement('td');
		row.appendChild(col);
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.innerHTML += "$sampleText";
		col.style.border = '1px dotted';
		col.style.margin = '3px';
		col.style.backgroundColor = '#f99';
	}
	
	/**
	 * Answer the element of the document by id.
	 * 
	 * @param string id
	 * @return object The html element
	 * @access public
	 * @since 8/25/05
	 */
	function getElementFromDocument(id) {
		// Gecko, KHTML, Opera, IE6+
		if (document.getElementById) {
			return document.getElementById(id);
		}
		// IE 4-5
		if (document.all) {
			return document.all[id];
		}			
	}

/* ]]> */
</script>

<div id='layout_display'>$errorText</div>

</td></tr></table>

<script type='text/javascript'>
/* <![CDATA[ */

	// Render the initial display
	initializeLayoutDisplay('$formName');

/* ]]> */
</script>

END;
		
		$step->setContent(ob_get_clean());

// 		
// 		// :: Effective/Expiration Dates ::
// 		$step =& $wizard->addStep("datestep", new WizardStep());
// 		$step->setDisplayName(_("Effective Dates")." ("._("optional").")");
// 		
// 		// Create the properties.
// 		$property =& $step->addComponent("effective_date", new WTextField());
// 	//	$property->setDefaultValue();
// //		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
// 	
// 		$property =& $step->addComponent("expiration_date", new WTextField());
// 	//	$property->setDefaultValue();
// //		$property->setErrorString(" <span style='color: #f00'>* "._("The date must be of the form YYYYMMDD, YYYYMM, or YYYY.")."</span>");
// 		
// 		// Create the step text
// 		ob_start();
// 		print "\n<h2>"._("Effective Date")."</h2>";
// 		print "\n"._("The date that this <em>Asset</em> becomes effective: ");
// 		print "\n<br />[[effective_date]]";
// 		
// 		print "\n<h2>"._("Expiration Date")."</h2>";
// 		print "\n"._("The date that this <em>Asset</em> expires: ");
// 		print "\n<br />[[expiration_date]]";
// 		$step->setContent(ob_get_contents());
// 		ob_end_clean();

		
		return $wizard;
	}
		
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =& $this->getWizard($cacheName);
		
		if (!$wizard->validate()) return false;
		
		// Make sure we have a valid Repository
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		$repository =& $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		$asset =& $repository->getAsset($idManager->getId(RequestContext::value('node')));
		$null = null;
		$renderer =& NodeRenderer::forAsset($asset, $null);
		
		$properties =& $wizard->getAllValues();
		
		// Name and description
		$asset->updateDisplayName($properties['namedescstep']['display_name']);
		$asset->updateDescription($properties['namedescstep']['description']);
		
		$part =& $renderer->getNumCellsPart();
		$part->updateValue(Integer::withValue($properties['layoutstep']['cells']));
		
		$part =& $renderer->getLayoutArrangementPart();
		$part->updateValue(String::withValue($properties['layoutstep']['arrangement']));
		
		$part =& $renderer->getTargetOverridePart();
		$part->updateValue(Integer::withValue($properties['layoutstep']['targetoverride']));
		
		// Update the effective/expiration dates
// 		if ($properties['datestep']['effective_date'])
// 			$asset->updateEffectiveDate(
// 				DateAndTime::fromString($properties['datestep']['effective_date']));
// 		if ($properties['datestep']['expiration_date'])
// 			$asset->updateExpirationDate(
// 				DateAndTime::fromString($properties['datestep']['expiration_date']));
		
		return TRUE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("site", "view", array(
				"node" => RequestContext::value('return_node')));
	}
}

?>