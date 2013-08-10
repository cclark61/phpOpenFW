<?php
//*******************************************************************
/**
 * Calendar plugin
 * @package		phpOpenFW
 * @subpackage	Plugins
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 * @version 	Started: 4-22-2008 Updated: 3-20-2010
 **/
//*******************************************************************

//*******************************************************************
/**
 * Calendar Class
 * @package		phpOpenFW
 * @subpackage	Plugin
 */
//*******************************************************************
abstract class calendar
{
	
	//*******************************************************************
	// Member Variables
	//*******************************************************************
	protected $in_date;
	protected $year;
	protected $month;
	protected $day;
	protected $link_start;
	protected $show_header;
	protected $caption;
	protected $first_timestamp;
	protected $curr_timestamp;
	protected $first_day_num;
	protected $last_month_day;
	protected $empty_cells_before;
	
	//*******************************************************************
	// Constructor Function
	//*******************************************************************
	public function __construct($date=false, $link_start=false)
	{
		// Class Defaults
		if (!$date || is_null($date) || $date == '') { $date = date('Y-m-d'); }
		list($this->year, $this->month, $this->day) = explode('-', $date);
		$this->in_date = (checkdate($this->month, $this->day, $this->year)) ? ($date) : (date('Y-m-d'));
		
		$this->show_header = true;
		$this->link_start = $link_start;
			
		list($this->year, $this->month, $this->day) = explode('-', $this->in_date);
		$this->first_timestamp = mktime(0, 0, 0, $this->month, 1, $this->year);
		$this->curr_timestamp = mktime(0, 0, 0, $this->month, $this->day, $this->year);
		
		// First Day Number
		$this->first_day_num = date('N', $this->first_timestamp);
		
		// Calendar Caption
		$this->caption = date('F Y', $this->first_timestamp);
		
		// Last Day of the Month
		$this->last_month_day = date('t', $this->first_timestamp);
		
		// Calculate Empty Cells
		$this->empty_cells_before = $this->first_day_num - 1;
	}

	//*******************************************************************
	// Disable Calendar Header
	//*******************************************************************
	public function disable_header() { $this->show_header = false; }
	
	//*******************************************************************
	// Set Caption
	//*******************************************************************
	public function set_caption($caption=false) { $this->caption = $caption; }
	
}

//*******************************************************************
/**
 * Month Class
 * @package		phpOpenFW
 * @subpackage	Plugin
 */
//*******************************************************************
class month_calendar extends calendar
{
	//*******************************************************************
	// Render Function
	//*******************************************************************
	public function render()
	{
		// Start Calendar Table
		$tbl = new table();
		$tbl->set_attribute('class', 'gen_calendar');
		$tbl->set_columns(7);
		if ($this->caption) { $tbl->caption($this->caption); }
		$curr_cell = 0;
		$curr_row = 0;
		
		// Header
		if ($this->show_header) {
			$header_attrs = array('class' => 'calendar_header');
			$tbl->th('Mo.', 1, $header_attrs);
			$tbl->th('Tu.', 1, $header_attrs);
			$tbl->th('We.', 1, $header_attrs);
			$tbl->th('Th.', 1, $header_attrs);
			$tbl->th('Fr.', 1, $header_attrs);
			$tbl->th('Sa.', 1, $header_attrs);
			$tbl->th('Su.', 1, $header_attrs);
		}
		
		// Empty Cells Before
		for ($bx = 1; $bx <= $this->empty_cells_before; $bx++) {
			$tbl->td('&nbsp;');
			$curr_cell++;
		}
		
		// Actual Date Cells
		for ($x = 1; $x <= $this->last_month_day; $x++) {
			$link_x = ($x < 10) ? ('0' . $x) : ($x);
			if ($x == $this->day) { $tbl->td($x, 1, array('class' => 'selected')); }
			else {
				if ($this->link_start) {
					$full_link = $this->link_start . urlencode("$this->month/$link_x/$this->year");
					$val = new gen_element('a', $x, array('href' => $full_link));
					$tbl->td($val);
				}
				else { $tbl->td($x); }
			}
			
			// Increment Cell and/or Row
			$curr_cell++;
			if ($curr_cell == 7) {
				$curr_cell = 0;
				$curr_row++;
			}
		}
		
		// Empty Cells after
		while ($curr_cell < 7) {
			$tbl->td('&nbsp;');
			$curr_cell++;
		}
	
		// Render Calendar Table
		//$tbl->set_alt_rows();
		//$tbl->no_xsl();
		$tbl->render();
	}
}


?>
