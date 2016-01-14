<?php

$mod_title = "MySQL Cluster (Node #1)";
$data_source = 'mysql_cluster1';

load_plugin('benchmark');
$cb = new code_benchmark();
$cb->start_timer();
include(dirname(__FILE__) . '/../common/controller.php');
include('tests.php');
$cb->stop_timer();
$times = $cb->get_results();
$time_elapsed = round($times['stop'] - $times['start'], 5);
$gen_message[] = "Elapsed Time: {$time_elapsed} seconds";

//=================================================================
// Number of Connections Used
//=================================================================
if (isset($GLOBALS['mysql_conns'])) {
	print_header("Number of MySQL Connections used: {$GLOBALS['mysql_conns']}");
}

