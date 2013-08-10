<?php
//*****************************************************************************
/**
 * Plugin for performing XML Transformations using XSL Stylesheets
 *
 * @package		phpOpenFW
 * @subpackage	Plugin
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		http://www.gnu.org/licenses/gpl-2.0.txt
 * @version 	Started: 3-15-2006, Last updated: 1-29-2013
 **/
//*****************************************************************************

//*****************************************************************************
/**
 * XML Transformation function
 *
 * Transform an XML string given an XSL template
 * @param string XML data string
 * @param string File path to an XSL template
 */
//*****************************************************************************
function xml_transform($xml_data, $xsl_template, $show_xml_on_error=false, $use_cache=true)
{
	// Load status variables
	$xml_load_status = '';
	$xsl_load_status = '';

	// Transform XML
	if (isset($xml_data) && isset($xsl_template) && !empty($xml_data) && !empty($xsl_template)) {
		
		// Load the XML source
		$xml = new DOMDocument();
		$xml_load_status = $xml->loadXML($xml_data);
		if (!$xml_load_status) {
			trigger_error('Error: Malformed XML data given!!'); 
			if ($show_xml_on_error) { echo $xml_data; }
		}

		// Load the XSL Source
		if (file_exists($xsl_template)) {
			set_error_handler('HandleXSLError');
			$xsl = new DOMDocument;
			$xsl_load_status = $xsl->load($xsl_template, LIBXML_NOCDATA);
			restore_error_handler();
		}
		else {
			trigger_error("Error: xml_transform(): XSL Stylesheet '{$xsl_template}' does not exist!");
			return false;
		}

		// XSLT Processor (or XSLTCache) Object
		if (extension_loaded('xslcache') && $use_cache) {
			if ($xsl_load_status && $xml_load_status) { $proc = new xsltCache; }
			else { $proc = new XSLTProcessor; }
		}
		else { $proc = new XSLTProcessor; }
		$proc->registerPHPFunctions();

		// Set the XSL Stylesheet and configure processor parameters
		if ($xsl_load_status) {
			if (extension_loaded('xslcache') && $use_cache) { $proc->importStyleSheet($xsl_template); }
			else { $proc->importStyleSheet($xsl); }
		}
		else {
			trigger_error('Error: xml_transform(): XSL Stylesheet syntax errors occurred on load!!');
			return false;
		}
		
		// Transform XML
		if ($xml_load_status && $xsl_load_status) {
			$output = $proc->transformToXML($xml);

			// Check for successful output
			if ($output) { echo $output; }
			else {
				trigger_error('Error: xml_transform(): XML Transformation Error!!');
				echo "<br/>\n{$xml_data}";
				return false;
			}
		}
	}
	// Return Data Only
	else if (isset($xml_data) && !empty($xml_data)) { echo $xml_data; }
	
	return true;
}

//*****************************************************************************
/**
 * XSL Load Error Handler
 */
//*****************************************************************************
function HandleXSLError($errno, $errstr, $errfile, $errline)
{
    if ($errno==E_WARNING && (substr_count($errstr,'DOMDocument::load()') > 0))
    {
        // Throw new DOMException($errstr);
        print "<p>{$errstr}</p>\n";
        return true;
    }
    else { return false; }
}

//*****************************************************************************
/**
 * Generate XML Data from an array
 *
 * @param string Top level XML data element name
 * @param array Array to generate XML from
 * @param string Prefix to use for numeric element names
 */
//*****************************************************************************
function array2xml($element, $data, $num_prefix='data_', $depth=0, $count=0)
{
	// Prefix numeric elements
	if (is_numeric($element)) { $element = $num_prefix . $depth . '_' . $count; }
	
	// Create Indent Tabs
	$indent = '';
	for ($x = 0; $x < $depth; $x++) {
		$indent .= "\t";
	}

	if ($element === '') {
		trigger_error("Error: generate_xml(): XML Element name not supplied! (depth: {$depth})");
		return false;
	}
	
	if (is_array($data)) {
		$xml = "{$indent}<{$element}>\n";
		$count = 0;
		foreach ($data as $key => $row_data) {
			$xml .= array2xml($key, $row_data, $num_prefix, $depth+1, $count);
			$count++;	
		}
		$xml .= "{$indent}</{$element}>\n";	
	}
	else {
		$xml = "{$indent}<{$element}>{$data}</{$element}>\n";
	}

	return $xml;
}

//*****************************************************************************
/**
 * Return a string as valid XML escaped value
 *
 * @param string Data to escape
 * @return string Escaped data
 */
//*****************************************************************************
function xml_escape($str_data)
{
	if ($str_data !== '') {
		return '<![CDATA[' . strip_cdata_tags($str_data) . ']]>';
	}
	else { return false; }
}

//*****************************************************************************
/**
 * Return an array with valid XML escaped values
 *
 * @param array Data to escape
 * @return array Escaped data
 */
//*****************************************************************************
function xml_escape_array($in_data)
{
	if (is_array($in_data)) {
		foreach ($in_data as $key => $item) {
			$in_data[$key] = xml_escape_array($item);
		}
		return $in_data;
	}
	else if ($in_data !== '') {
		return '<![CDATA[' . strip_cdata_tags($in_data) . ']]>';
	}
	else { return false; }
}

//*****************************************************************************
/**
 * Strip all CDATA begin and end tags from the string passed.
 *
 * @param string String to strip CDATA tags from
 * @return string Cleaned string
 */
//*****************************************************************************
function strip_cdata_tags($str_data)
{
	settype($str_data, 'string');
	$str_data = str_replace('<![CDATA[', '', $str_data);
	$str_data = str_replace(']]>', '', $str_data);
	return $str_data;
}

?>
