<?php

// Time-stamp: "2009-01-31 22:24:57 jantman"
// $Id: parseXML.php,v 1.2 2008/01/27 02:38:48 jantman Exp $

// parses Nagios output from XML generator on MON1 into HTML

$file = "http://voip1/statusXML.php";
$nagios_host = "VoIP1";



// PARSE IT
$doc = new DOMDocument();
$doc->load($file);

$top = $doc->getElementsByTagName("nagios_status"); // returns DOMNodeList
$hosts = $top->item(0)->getElementsByTagName("host");

// program stuff
$program = $top->item(0)->getElementsByTagName("programStatus");
foreach($program as $elem)
{
    $nagios_pid = $elem->getElementsByTagName("nagios_pid")->item(0)->nodeValue;
    $program_start = $elem->getElementsByTagName("program_start")->item(0)->nodeValue;
    $last_command_check = $elem->getElementsByTagName("last_command_check")->item(0)->nodeValue;
}

echo '<p class="description">';
echo '<strong>Field Descriptions:</strong><br />';
echo '<strong>Host:</strong> Which computer (or logical grouping if not a physical computer).<br />';
echo '<strong>Service:</strong> The specific service (i.e. web server, database server, etc.).<br />';
echo '<strong>Status:</strong> Current status (i.e. OK, WARNing, CRITical).<br />';
echo '<strong>Output:</strong> Output from the check plugin (only of consequence to Jason, unless it says somethng that sounds bad).<br />';
echo '<strong>Last Check:</strong> How long ago it was last checked (most services should be 5 minutes or less).<br />';
echo '<strong>Last Change:</strong> How long ago since Status last changed (small amounts of time could be a problem if it\'s currently OK).<br />';
echo '<strong>Age:</strong> Age of current information (how long ago this information was grabbed from the server).<br />';
echo '<strong>Exec Time:</strong> How long it took to check (numbers over 4-5s are a problem).<br />';
echo '<strong>Ack:</strong> Whether a problem has been acknowledged (by Jason).<br />';
echo '<br /><br />';
echo '<strong>What Really Matters to You:</strong> If the Status is not "OK", that\'s probably bad. If the last column ("Ack") is not "Y" and the Status is not "OK", it means there\'s a problem that Jason doesn\'t know about. Call him. (201-906-7347). If you think there have been problems lately, but everything looks OK (or is ACKnowledged) check through the "Last Change" column - very recent times mean that there was a problem but it\'s now ok.<br />';
echo '<p>'."\n";

echo '<div class="nagiosInfo"><strong>Program Info:</strong> PID: '.$nagios_pid.' Start: '.format_ts($program_start).' ago Last Check: '.format_ts($last_command_check).' ago</div>';

// TABLE SETUP
echo '<table class="nagiosStatus">'."\n";
echo '<tr>'."\n";
echo '<th>Host</th><th>Service</th><th>Status</th><th>Output</th><th>Last Check</th><th>Last Change</th><th>Age</th><th>Exec Time</th><th>Ack</th>'."\n";
echo '</tr>'."\n";

$host_count = 0;
$total_hosts = 0;
$services = $doc->getElementsByTagName("services");
$services = $services->item(0)->getElementsByTagName("service");
$service_count = 0;
$total_services = 0;
// HOSTS
foreach($hosts as $value)
{
    $name = $value->getElementsByTagName("host_name")->item(0)->nodeValue;
    $status = $value->getElementsByTagName("current_state")->item(0)->nodeValue;
    $plugin_output = $value->getElementsByTagName("plugin_output")->item(0)->nodeValue;
    $last_check = $value->getElementsByTagName("last_check")->item(0)->nodeValue;
    $last_state_change = $value->getElementsByTagName("last_state_change")->item(0)->nodeValue;
    $ack = $value->getElementsByTagName("problem_has_been_acknowledged")->item(0)->nodeValue;
    $last_update = $value->getElementsByTagName("last_update")->item(0)->nodeValue;
    $exec_time = $value->getElementsByTagName("check_execution_time")->item(0)->nodeValue;
    if($status != 0) { $host_count++;}

    host_tr($name, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time);

    // SERVICES
    // parse the services
    foreach($services as $serValue)
    {
	$service_host = $serValue->getElementsByTagName("host_name")->item(0)->nodeValue;
	if($service_host == $name)
	{
	    if($status != 0) {$service_count++;}
	    $s_status = $serValue->getElementsByTagName("current_state")->item(0)->nodeValue;
	    $s_plugin_output = $serValue->getElementsByTagName("plugin_output")->item(0)->nodeValue;
	    $s_last_check = $serValue->getElementsByTagName("last_check")->item(0)->nodeValue;
	    $s_last_state_change = $serValue->getElementsByTagName("last_state_change")->item(0)->nodeValue;
	    $s_ack = $serValue->getElementsByTagName("problem_has_been_acknowledged")->item(0)->nodeValue;
	    $s_last_update = $serValue->getElementsByTagName("last_update")->item(0)->nodeValue;
	    $s_service_description = $serValue->getElementsByTagName("service_description")->item(0)->nodeValue;
	    $s_exec_time = $value->getElementsByTagName("check_execution_time")->item(0)->nodeValue;
	    serv_tr($s_service_description, $s_status, $s_plugin_output, $s_last_check, $s_last_state_change, $s_ack, $s_last_update, $s_exec_time);
	    $total_services++;
	}
    }
    // END SERVICES
    $total_hosts++;
}
echo '</table>'."\n";


// FUNCTIONS
function statusColor($status_value)
{
    switch($status_value)
    {
	case 0:
	    return "#00FF00";
	case 1:
	    return "#FFFF00";
	case 2:
	    return "#FE2E2E";
    }
}

function ackTD($ack, $is_a_host)
{
    if($is_a_host){ $host = 'class="hostRow"';}else{$host = "";}
    switch($ack)
    {
	case 0:
	    return '<td class="statusCRIT" '.$host.'>N</td>'."\n";
	case 1:
	    return '<td class="statusOK" '.$host.'>Y</td>'."\n";
    }
}

function format_ts($ts)
{
    // formats timeticks into a textual representation
    $seconds = time() - $ts;
    $final = "";
    if($seconds > 86400) // > 1 day
    {
	$days = (int)($seconds / 86400);
	$final .= $days."d ";
	$seconds = ((int)$seconds % 86400);
    }
    if($seconds > 3600) // 1 hour
    {
	$hours = (int)($seconds / 3600);
	$final .= $hours."h ";
	$seconds = (int)($seconds % 3600);
    }
    if($seconds > 60) // 1 minute
    {
	$min = (int)($seconds / 60);
	$final .= $min."m ";
	$seconds = (int)($seconds % 60);
    }
    $final .= $seconds."s";
    return $final;
}

function host_tr($name, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time)
{
    echo '<tr class="hostRow">';
    echo '<td class="hostRow"><strong>'.$name.'</strong></td>';
    echo '<td class="hostRow">(host)</td>';
    echo statusTD($status, true);
    echo '<td class="hostRow">'.$plugin_output.'</td>';
    echo '<td class="hostRow">'.format_ts($last_check).'</td>';
    echo '<td class="hostRow">'.format_ts($last_state_change).'</td>';
    echo '<td class="hostRow">'.format_ts($last_update).'</td>';
    echo '<td class="hostRow">'.$exec_time.'s</td>';
    if($status != 0){ echo ackTD($ack, true);} else { echo '<td class="hostRow">&nbsp;</td>';}
    echo '</tr>';
}

function serv_tr($description, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time)
{
    echo '<tr>';
    echo '<td>&nbsp;</td>';
    echo '<td>'.$description.'</td>';
    echo statusTD($status, false);
    echo '<td>'.$plugin_output.'</td>';
    echo '<td>'.format_ts($last_check).'</td>';
    echo '<td>'.format_ts($last_state_change).'</td>';
    echo '<td>'.format_ts($last_update).'</td>';
    echo '<td>'.$exec_time.'s</td>';
    if($status != 0){ echo ackTD($ack, false);} else { echo "<td>&nbsp;</td>";}
    echo '</tr>';
}

function statusTD($status, $is_a_host)
{
    if($is_a_host){ $host = 'class="hostRow"';}else{$host = "";}
    if($status == 0)
    {
	return '<td class="statusOK" '.$host.'>OK</td>';
    }
    elseif($status == 1)
    {
	return '<td class="statusWARN" '.$host.'>WARN</td>';
    }
    elseif($status == 2)
    {
	return '<td class="statusCRIT" '.$host.'>CRIT</td>';
    }
    else
    {

    }
}

?>