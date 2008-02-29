<?php
/**
 * @since 2/28/08
 * @package segue.logs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: usage_graph.act.php,v 1.1 2008/02/29 20:04:07 adamfranco Exp $
 */ 

if (!defined('JPGRAPH_DIR'))
	throw new Exception("Configuration Error, JPGRAPH_DIR is not defined.");


require_once(JPGRAPH_DIR."/src/jpgraph.php");
require_once(JPGRAPH_DIR."/src/jpgraph_bar.php");
require_once(JPGRAPH_DIR."/src/jpgraph_line.php");

/**
 * Generate a graph of usage statistics
 * 
 * @since 2/28/08
 * @package segue.logs
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: usage_graph.act.php,v 1.1 2008/02/29 20:04:07 adamfranco Exp $
 */
class usage_graphAction
	extends Action
{

	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/28/08
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
		
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 2/28/08
	 */
	public function execute () {		
		// New graph with a drop shadow
		$graph = new Graph(800,600,'auto');
		$graph->SetShadow();
		
		// Use a "text" X-scale
		$graph->SetScale("textlin");
		
		// Specify X-labels
		//$databarx = array('tXi','','','xxx','','','iXii','','','OOO','','','tOO');
		$graph->xaxis->SetFont(FF_VERA,FS_NORMAL);
		$graph->xaxis->SetTickLabels($this->getLabels());
		$graph->xaxis->SetLabelAngle(45);

		$days = $this->getNumDays();
		// Between 3 and 20 weeks, use weekly intervals
		if ($days > 21 && $days < 140)
			$graph->xaxis->SetTextLabelInterval(7);
		// above 20 weeks, just fit in twenty labels
		else if ($days >= 140)
			$graph->xaxis->SetTextLabelInterval(round($days/20));
		
		$graph->SetMargin(40,20,40,80);
		
		// Set title and subtitle
		$title = _("Segue Usage for the past %1 %2");
		$title = str_replace('%1', $this->getIntervalSize(), $title);
		if ($this->getIntervalSize() == 1)
			$unit = $this->getIntervalUnit();
		else
			$unit = $this->getIntervalUnit().'s';
		$title = str_replace('%2', ucfirst(strtolower($unit)), $title);
		$graph->title->Set($title);
		
		// Use built in font
		$graph->title->SetFont(FF_VERA,FS_BOLD);
		$graph->legend->SetFont(FF_VERA,FS_NORMAL);
		
		// Create the bar plot
		$b1 = new BarPlot($this->getEdits());
		$b1->SetLegend(_("Edits"));
		
		$b1->SetFillColor('#7778F3');
		
		$b2 = new BarPlot($this->getFiles());
		$b2->SetLegend(_("Files"));
		$b2->SetFillColor('#FFCB3F');
		
		$b3 = new BarPlot($this->getComments());
		$b3->SetLegend(_("Comments"));
		$b3->SetFillColor('#89DF6D');
		
		$accbar = new AccBarPlot(array($b1, $b2, $b3));
		$accbar->SetWidth(1);
		//$b1->SetShadow();
		$graph->Add($accbar);
		
		
		
		$l2 = new LinePlot($this->getErrors());
		$l2->SetLegend(_("Errors"));
		$l2->SetColor('#FF0000');
		$l2->SetWeight(1);
		$l2->SetBarCenter(true);
		$graph->Add($l2);
		
		$l1 = new LinePlot($this->getLogins());
		$l1->SetLegend(_("Logins"));
		$l1->SetColor('#0F3F00');
		$l1->SetWeight(3);
		$l1->SetBarCenter(true);
		$graph->Add($l1);
		
		
		// Finally output the  image
		$graph->Stroke();
		
		
		exit;
	}
	
	/**
	 * Answer the data labels
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getLabels () {
		$data = $this->getData();
		return $data['labels'];
	}
	
	/**
	 * Answer the comments numbers
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getComments () {
		$data = $this->getData();
		return $data['comments'];
	}
	
	/**
	 * Answer the edits numbers
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getEdits () {
		$data = $this->getData();
		return $data['edits'];
	}
	
	/**
	 * Answer the edits numbers
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getFiles () {
		$data = $this->getData();
		return $data['files'];
	}
	
	/**
	 * Answer the number of logins
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getLogins () {
		$data = $this->getData();
		return $data['logins'];
	}
	
	/**
	 * Answer the number of errors
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getErrors () {
		$data = $this->getData();
		return $data['errors'];
	}
	
	/**
	 * Add in empty data arrays for dates with no values
	 * 
	 * @param string $upcoming
	 * @return void
	 * @access private
	 * @since 2/29/08
	 */
	private function addEmptyDates ($upcoming) {
		if (!count($this->data['labels']))
			return;
		
		$i = Date::fromString(end($this->data['labels']))->plus(Duration::withDays(1));
		$upcoming = Date::fromString($upcoming);
		
		while ($i->isLessThan($upcoming)) {
			$this->data['labels'][] = $i->yyyymmddString();
			$this->data['comments'][] = 0;
			$this->data['edits'][] = 0;
			$this->data['files'][] = 0;
			$this->data['logins'][] = 0;
			$this->data['errors'][] = 0;
			
			$i = $i->plus(Duration::withDays(1));
		}
	}
	
	/**
	 * Answer the data array
	 * 
	 * @return array
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getData () {
		if (!isset($this->data)) {
			$dbc = Services::getService("DatabaseManager");
			$result = $dbc->query($this->getQuery())->returnAsSelectQueryResult();
			
			$this->data = array();
			$this->data['labels'] = array();
			$this->data['comments'] = array();
			$this->data['edits'] = array();
			$this->data['files'] = array();
			$this->data['logins'] = array();
			$this->data['errors'] = array();
			
			while ($result->hasNext()) {
				$row = $result->next();
				$this->addEmptyDates($row['log_date']);
				$this->data['labels'][] = $row['log_date'];
				$this->data['comments'][] = $row['num_comments'];
				$this->data['edits'][] = $row['num_modifications'];
				$this->data['files'][] = $row['num_media'];
				$this->data['logins'][] = $row['num_logins'];
				$this->data['errors'][] = $row['num_errors'];
			}
		}
		return $this->data;
	}
	
	/**
	 * Answer the stats query
	 * 
	 * @return object SelectQuery
	 * @access protected
	 * @since 2/28/08
	 */
	protected function getQuery () {
		return new GenericSQLQuery("SELECT
	log_date, 
	IFNULL(MAX(num_mod_events), 0) AS num_modifications, 
	IFNULL(MAX(num_media_events), 0) AS num_media, 
	IFNULL(MAX(num_comment_events), 0) AS num_comments,
	IFNULL(MAX(num_login_events), 0) AS num_logins,
	IFNULL(MAX(num_error_events), 0) AS num_errors
FROM
		(SELECT 
			date(`timestamp`) AS log_date, 
			count(*) AS num_mod_events, 
			NULL AS num_media_events,
			NULL AS num_comment_events,
			NULL AS num_login_events,
			NULL AS num_error_events
		FROM `log_entry` 
		WHERE 
			log_name = 'Segue' 
			AND fk_priority_type IN (SELECT id FROM log_type WHERE keyword = 'Event_Notice')
			AND category IN (
				'Create Site', 
				'Modify Content', 
				'Component Modified', 
				'Component Moved', 
				'Create Component', 
				'Delete Component', 
				'Create Placeholder', 
				'Delete Placeholder', 
				'ModifyPlaceholder')
		GROUP BY date(timestamp)
	UNION
		SELECT 
			date(`timestamp`) AS log_date, 
			NULL AS num_mod_events, 
			count(*) AS num_media_events,
			NULL AS num_comment_events,
			NULL AS num_login_events,
			NULL AS num_error_events
		FROM `log_entry` 
		WHERE log_name = 'Segue' 
			AND fk_priority_type IN (SELECT id FROM log_type WHERE keyword = 'Event_Notice')
			AND category IN (
				'Media Library')
		GROUP BY date(timestamp)
	UNION
		SELECT 
			date(`timestamp`) AS log_date, 
			NULL AS num_mod_events, 
			NULL AS num_media_events,
			count(*) AS num_comment_events,
			NULL AS num_login_events,
			NULL AS num_error_events
		FROM `log_entry` 
		WHERE log_name = 'Segue' 
			AND fk_priority_type IN (SELECT id FROM log_type WHERE keyword = 'Event_Notice')
			AND category IN (
				'Comments')
		GROUP BY date(timestamp)
	UNION
		SELECT 
			date(`timestamp`) AS log_date, 
			NULL AS num_mod_events, 
			NULL AS num_media_events,
			NULL AS num_comment_events,
			count(*) AS num_login_events,
			NULL AS num_error_events
		FROM `log_entry` 
		WHERE log_name = 'Authentication' AND category = 'Authentication Sucess'
		GROUP BY date(timestamp)
	UNION
		SELECT 
			date(`timestamp`) AS log_date, 
			NULL AS num_mod_events, 
			NULL AS num_media_events,
			NULL AS num_comment_events,
			NULL AS num_login_events,
			count(*) AS num_error_events
		FROM `log_entry` 
		WHERE log_name = 'Harmoni' 
			AND fk_priority_type NOT IN (SELECT id FROM log_type WHERE keyword IN ('Event_Notice', 'Notice'))
		GROUP BY date(timestamp)
	) AS union_table
WHERE log_date > CURDATE() - ".$this->getInterval()."
GROUP BY log_date
ORDER BY log_date ASC");
	}
	
	/**
	 * Answer the interval length over which to show the graph
	 * 
	 * @return string
	 * @access protected
	 * @since 2/29/08
	 */
	protected function getInterval () {
		return "INTERVAL ".$this->getIntervalSize()." ".$this->getIntervalUnit();
	}
	
	/**
	 * Answer the size of the interval
	 * 
	 * @return int
	 * @access protected
	 * @since 2/29/08
	 */
	protected function getIntervalSize () {
		if (intval(RequestContext::value('interval_size')) 
				&& intval(RequestContext::value('interval_size')) > 0)
			return intval(RequestContext::value('interval_size'));
		else
			return 3;
	}
	
	/**
	 * Answer the units of the interval
	 * 
	 * @return string
	 * @access protected
	 * @since 2/29/08
	 */
	protected function getIntervalUnit () {
		$units = array('DAY', 'WEEK', 'MONTH', 'YEAR');
		if (RequestContext::value('interval_unit') 
				&& in_array(RequestContext::value('interval_unit'), $units))
			return RequestContext::value('interval_unit');
		else
			return 'MONTH';
	}
	
	/**
	 * Answer the number of days of logs.
	 * 
	 * @return int
	 * @access protected
	 * @since 2/29/08
	 */
	protected function getNumDays () {
		return count($this->getLabels());
	}
	
}

?>