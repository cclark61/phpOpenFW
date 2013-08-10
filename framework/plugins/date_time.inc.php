<?php
/**
* Date/Time functions plugin
*
* @package		phpOpenFW
* @subpackage	Plugin
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 1-4-2005 Updated: 4-2-2013
**/

//*****************************************************************************
// is_valid_date Function
// *** Returns TRUE if $date is a valid MM/DD/YYYY formatted date.
//*****************************************************************************
function valid_date($date) { return is_valid_date($date); }
function is_valid_date($date)
{
	$regex = '/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/';
	if (preg_match($regex, $date, $parts)) {
		return checkdate($parts[1], $parts[2], $parts[3]);
	}
	else { return false; }
}


//*****************************************************************************
// is_valid_sql_date Function
// *** Returns TRUE if $date is a valid SQL formatted date.
//*****************************************************************************
function is_valid_sql_date($date)
{
	$regex = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/';
	if (preg_match($regex, $date, $parts)) {
		return checkdate($parts[2], $parts[3], $parts[1]);
	}
	else { return false; }
}


//*****************************************************************************
// is_valid_time Function
// *** Returns TRUE if $time is HH:MM:SS, none of which are 00.
//*****************************************************************************
function valid_time($time) { return is_valid_time($valid_time); }
function is_valid_time($time)
{
	$regex = '/^([0-1]?\d|2[0-3]):([0-5]?\d):([0-5]?\d)$/';
	return preg_match($regex, $time);
}

//*****************************************************************************
// Transform Date Function
//*****************************************************************************
function transform_date($date, $start_format, $end_format, $clean_nums=true)
{
	if (empty($date)) { return false; }
	else {
		$date_parts = explode(' ', $date);
		$date = $date_parts[0];
	}
	
	switch ($start_format) {
		case 'sql':
			$date_parts = explode('-', $date);
			if (count($date_parts) < 3) { return false; }
			list($year, $month, $day) = $date_parts;
			
			// Clean the Numbers
			if ($clean_nums) {
				$month += 0;
				$day += 0;
				$year += 0;
			}
			
			switch ($end_format) {
				case 'm/d/y':
					return "$month/$day/$year";
					break;
					
				default:
					break;
			}
			break;
			
		case 'm/d/y':
			$date_parts = explode('/', $date);
			if (count($date_parts) < 3) { return false; }
			list($month, $day, $year) = $date_parts;
			
			switch ($end_format) {
				case 'sql':
					return "$year-$month-$day";
					break;
					
				default:
					break;
			}
			break;
			
		default:
			break;
	}
}

//*****************************************************************************
// Make a MySQL Timestamp Look Pretty
//*****************************************************************************
function mystamp_pretty($mysql_stamp, $format='n/j/Y g:i a')
{
	$unix_stamp = strtotime($mysql_stamp);
	if ($unix_stamp > 0) {
		return date($format, $unix_stamp);
	}
	else {
		return false;
	}
}

?>
