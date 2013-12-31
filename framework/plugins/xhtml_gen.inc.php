<?php
/**
* XHTML Generator Plugin
*
* @package		phpOpenFW
* @subpackage	Plugin
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 3/9/2012, Last updated: 2/15/2013
**/

//*****************************************************************************
// Check that the Content_Gen Plugin is Loaded
//*****************************************************************************
if (!function_exists('xhe')) {
	trigger_error('Function "xhe()" is unavailable. Unable to load the "xhtml_gen" plugin without "xhe()".');
	return false;
}

//*****************************************************************************
//*****************************************************************************
// Common XHTML Elements
//*****************************************************************************
//*****************************************************************************

//*****************************************************************************
/**
* XHTML element functions
*/
//*****************************************************************************
if (!function_exists('p')) { function p($c, $a=false) { return xhe('p', $c, $a); } }
if (!function_exists('span')) { function span($c, $a=false) { return xhe('span', $c, $a); } }
if (!function_exists('pre')) { function pre($c, $a=false) { return xhe('pre', $c, $a); } }
if (!function_exists('strong')) { function strong($c, $a=false) { return xhe('strong', $c, $a); } }
if (!function_exists('em')) { function em($c, $a=false) { return xhe('em', $c, $a); } }
if (!function_exists('u')) { function u($c, $a=false) { return xhe('u', $c, $a); } }
if (!function_exists('small')) { function small($c, $a=false) { return xhe('small', $c, $a); } }

if (!function_exists('li')) { function li($c, $a=false) { return xhe('li', $c, $a); } }
if (!function_exists('dt')) { function dt($c, $a=false) { return xhe('dt', $c, $a); } }
if (!function_exists('dd')) { function dd($c, $a=false) { return xhe('dd', $c, $a); } }

if (!function_exists('form')) { function form($c, $a=false) { return xhe('form', $c, $a); } }
if (!function_exists('label')) { function label($c, $a=false) { return xhe('label', $c, $a); } }
if (!function_exists('option')) { function option($c, $a=false) { return xhe('option', $c, $a); } }
if (!function_exists('button')) { function button($c, $a=false) { return xhe('button', $c, $a); } }

if (!function_exists('fieldset')) { function fieldset($c, $a=false) { return xhe('fieldset', $c, $a); } }
if (!function_exists('legend')) { function legend($c, $a=false) { return xhe('legend', $c, $a); } }

if (!function_exists('h1')) { function h1($c, $a=false) { return xhe('h1', $c, $a); } }
if (!function_exists('h2')) { function h2($c, $a=false) { return xhe('h2', $c, $a); } }
if (!function_exists('h3')) { function h3($c, $a=false) { return xhe('h3', $c, $a); } }
if (!function_exists('h4')) { function h4($c, $a=false) { return xhe('h4', $c, $a); } }
if (!function_exists('h5')) { function h5($c, $a=false) { return xhe('h5', $c, $a); } }
if (!function_exists('h6')) { function h6($c, $a=false) { return xhe('h6', $c, $a); } }

if (!function_exists('input')) { function input($attrs) { return xhe('input', false, $attrs); } }
if (!function_exists('img')) { function img($attrs) { return xhe('img', false, $attrs); } }
if (!function_exists('br')) { function br($attrs=false) { return xhe('br', false, $attrs); } }
if (!function_exists('hr')) { function hr($attrs=false) { return xhe('hr', false, $attrs); } }

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML "ul" element
*/
//*****************************************************************************
//*****************************************************************************
if (!function_exists('ul')) {
	function ul($lis, $attrs=false)
	{
		if ($attrs !== false && !is_array($attrs)) {
			trigger_error('Attributes must be an array.');
			return false;
		}

		$inset = '';
		if (!is_array($lis)) { $inset = $lis; }
		else { foreach ($lis as $val) { $inset .= $val; } }

		return xhe('ul', $inset, $attrs);
	}
}

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML "ol" element
*/
//*****************************************************************************
//*****************************************************************************
if (!function_exists('ol')) {
	function ol($lis, $attrs=false)
	{
		if ($attrs !== false && !is_array($attrs)) {
			trigger_error('Attributes must be an array.');
			return false;
		}

		$inset = '';
		if (!is_array($lis)) { $inset = $lis; }
		else { foreach ($lis as $val) { $inset .= $val; } }

		return xhe('ol', $inset, $attrs);
	}
}

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML "select" element
*/
//*****************************************************************************
//*****************************************************************************
if (!function_exists('select')) {
	function select($opts, $attrs=false)
	{
		if ($attrs !== false && !is_array($attrs)) {
			trigger_error('Attributes must be an array.');
			return false;
		}

		$inset = '';
		if (!is_array($opts)) { $inset = $opts; }
		else { foreach ($opts as $val) { $inset .= $val; } }

		return xhe('select', $inset, $attrs);
	}
}

?>