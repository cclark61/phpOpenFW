<?php
/**
* Content Generator Plugin
*
* @package		phpOpenFW
* @subpackage	Plugin
* @author 		Christian J. Clark
* @copyright	Copyright (c) Christian J. Clark
* @license		http://www.gnu.org/licenses/gpl-2.0.txt
* @version 		Started: 7-7-2006, Last updated: 3-6-2012
**/

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML <div> element
* @param string Content inside of div
* @param array An array, in the form of [key] => [value], of attributes
*/
//*****************************************************************************
//*****************************************************************************
function div($content, $attrs=false)
{
	$element = new gen_element('div', $content, $attrs);
	
	ob_start();
	$element->render();
	return ob_get_clean();
}

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML <a> element
* @param string Anchor "href" attribute
* @param string Content inside of anchor
* @param array An array, in the form of [key] => [value], of attributes
*/
//*****************************************************************************
//*****************************************************************************
function anchor($href, $content, $attrs=false)
{
	if (!is_array($attrs)) { $attrs = array(); }
	$attrs['href'] = $href;
	$element = new gen_element('a', $content, $attrs);
	
	ob_start();
	$element->render();
	return ob_get_clean();
}

//*****************************************************************************
//*****************************************************************************
/**
* Creates an XHTML <img> element
* @param string Anchor "href" attribute
* @param string Content inside of anchor
* @param array An array, in the form of [key] => [value], of attributes
*/
//*****************************************************************************
//*****************************************************************************
function image($src, $alt=false, $attrs=false)
{
	if (!is_array($attrs)) { $attrs = array(); }
	$attrs['src'] = $src;
	if ($alt) { $attrs['alt'] = $alt; }
	$element = new gen_element('img', false, $attrs);
	
	ob_start();
	$element->render();
	return ob_get_clean();
}

//*****************************************************************************
//*****************************************************************************
/**
* Action Message Generator
* @param string Message
*/
//*****************************************************************************
//*****************************************************************************
function action_message($message)
{	
	print div(div($message, array('class' => 'text_block')), array('class' => 'action_message'));
}

//*****************************************************************************
//*****************************************************************************
/**
* Generic Link Generator Function
* @param array (URL, Display Caption, Link Class (not required))
*/
//*****************************************************************************
//*****************************************************************************
function gen_links($links, $extra_class='')
{
	if (is_array($links)) {
		$ul_content = '';
		foreach ($links as $link) {
			$link_attrs = array();
			if (isset($link[2])) { $link_attrs['class'] = $link[2]; }
			else { $link_attrs['class'] = 'gen_link'; }
			$ul_content .= '<li>' . anchor($link[0], $link[1], $link_attrs) . '</li>';
		}
		
		$ul = new gen_element('ul', $ul_content);
		ob_start();
		$ul->render();
		$ul_str = ob_get_clean();
		$ob_class = ($extra_class != '') ? ('outer_box $extra_class') : ('outer_box');
		print div($ul_str, array('class'=>$ob_class));
	}
	else {
		trigger_error('[!] Error: Expecting array input for function: [gen_links]!');
	}
}

//*****************************************************************************
//*****************************************************************************
/**
* Print a preformatted array or Simple XML Element Object (nicely viewable in HTML or CLI)
* @param array Array to Print. Multiple Arrays can be passed.
*/
//*****************************************************************************
//*****************************************************************************
function print_array($in_array)
{
	$sapi = strtoupper(php_sapi_name());
	$arg_list = func_get_args();
	foreach ($arg_list as $in_array) {
		if (
			is_array($in_array) 
			|| (gettype($in_array) == 'object' 
			&& (get_class($in_array) == 'SimpleXMLElement' || get_class($in_array) == 'stdClass'))
		) {
			if ($sapi != 'CLI') { print "<pre>\n"; }
			print_r($in_array);
			if ($sapi != 'CLI') { print "</pre>\n"; }
		}
	}
}

//*****************************************************************************
//*****************************************************************************
// Add URL Parameters Function
/**
* Given a URL, add another paramter to it and return it.
* @param string A URL
* @param array An array in the form of [Key] => [Value] to be used for paramters.
* @param bool Separator: [False] = '&' (default), [True] = '&amp;'
* @param bool [True] = URL Encode Data (default), [False] = Do Not URL Encode Data
* @return string New URL with update arguments/parameters
*/
//*****************************************************************************
//*****************************************************************************
function add_url_params($in_url, $params, $xml_escape=false, $url_encode=true)
{
	$out_url = $in_url;
	if (!is_array($params)) {
		trigger_error('Error: [add_url_params] :: Second argument must be an array.');
	}
	else if (count($params) <= 0) {
		//trigger_error('Error: [add_url_params] :: No parameters given.');
		return $out_url;
	}
	else {
		$args_started = false;
		foreach ($params as $arg => $val) {
			if (!$args_started && stristr($out_url, '?') === false) {
				$out_url .= '?';
				$args_started = true;
			}
			else {
				if ($xml_escape) { $out_url .= '&amp;'; }
				else { $out_url .= '&'; }
			}
			if (!$url_encode) { $out_url .= $arg . '=' . $val; }
			else { $out_url .= $arg . '=' . urlencode($val); }
		}
	}
	return $out_url;
}

//***************************************************************************
//***************************************************************************
// Generate Content Element
// DEPRECATED, Use xhe() function instead. Usage is exactly the same.
//***************************************************************************
//***************************************************************************
function gen_content($elm=false, $content='', $attrs=array(), $escape=false) {
	return xhe($elm, $content, $attrs, $escape);
}

?>
