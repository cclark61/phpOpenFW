#!/usr/bin/env php
<?php
//****************************************************************************************
//****************************************************************************************
// Quarry PHAR Builder
//****************************************************************************************
//****************************************************************************************

//================================================================================
// Get Version
//================================================================================
$version = file_get_contents(__DIR__ . '/VERSION');
if (!$version) {
	die("Could not get version.\n");
}
if (!file_put_contents(__DIR__ . '/framework/version.php', "<?php\nprint \"phpOpenFW version {$version}\\n\";\n")) {
	die("Could not write version controller file.\n");
}

//================================================================================
// Source Directory
//================================================================================
$srcRoot = __DIR__ . '/framework';
$phar_name = 'phpOpenFW.phar';

//================================================================================
// Options
//================================================================================
$opts = 'o:'; // Output Directory
$options = getopt($opts);

//================================================================================
// Determine Output Location
//================================================================================
if (!empty($options['o'])) {
	$buildRoot = realpath($options['o']);
	if (!is_dir($buildRoot)) {
		die("Invalid output location '{$buildRoot}'.");
	}
	else if (!is_writable($options['o'])) {
		die("Output location '{$buildRoot}' is not writable.");
	}
	
}
else {
	$buildRoot = getcwd();
	if (!is_writable($buildRoot)) {
		die("Output location '{$buildRoot}' is not writable.");
	}
}

//================================================================================
// Build PHAR
//================================================================================
$phar = new Phar($buildRoot . "/{$phar_name}",
        FilesystemIterator::CURRENT_AS_FILEINFO |
        FilesystemIterator::KEY_AS_FILENAME, $phar_name);
$phar->buildFromDirectory($srcRoot);
$phar->setStub($phar->createDefaultStub('version.php'));

