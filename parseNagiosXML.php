<?php
  /*
   * +----------------------------------------------------------------------+
   * | parseNagiosXML.php                                                   |
   * | PHP script to parse statusXML.php XML output and show as a HTML table|
   * |                                                                      |
   * | The canonical source for this project is:                            |
   * |   <http://svn.jasonantman.com/nagios-xml/> (via SVN or HTTP)         |
   * +----------------------------------------------------------------------+
   * | Copyright (c) 2006-2010 Jason Antman.                                |
   * |                                                                      |
   * | This program is free software; you can redistribute it and/or modify |
   * | it under the terms of the GNU General Public License as published by |
   * | the Free Software Foundation; either version 3 of the License, or    |
   * | (at your option) any later version.                                  |
   * |                                                                      |
   * | This program is distributed in the hope that it will be useful,      |
   * | but WITHOUT ANY WARRANTY; without even the implied warranty of       |
   * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the        |
   * | GNU General Public License for more details.                         |
   * |                                                                      |
   * | You should have received a copy of the GNU General Public License    |
   * | along with this program; if not, write to:                           |
   * |                                                                      |
   * | Free Software Foundation, Inc.                                       |
   * | 59 Temple Place - Suite 330                                          |
   * | Boston, MA 02111-1307, USA.                                          |
   * +----------------------------------------------------------------------+
   * | Authors: Jason Antman <jason@jasonantman.com>                        |
   * +----------------------------------------------------------------------+
   * | CHANGELOG:                                                           |
   * | 2010-08-10 (r6) jantman:                                             |
   * |   - updated license, file header, changelog                          |
   * +----------------------------------------------------------------------+
   * | $Date::                                                            $ |
   * | $LastChangedRevision::                                             $ |
   * | $HeadURL::                                                         $ |
   * +----------------------------------------------------------------------+ 
   */

// PARSE IT
$doc = new DOMDocument();
$doc->load($config_nagios_xml_path);

$top = $doc->getElementsByTagName("nagios_status"); // returns DOMNodeList
$hosts = $top->item(0)->getElementsByTagName("host");

/**
 * Return a string containing a <p> with the field descriptions
 * @return string
 */
function getFieldDescriptionP()
{
    $str = "";
    $str .=  '<p class="description">';
    $str .=  '<strong>Field Descriptions:</strong><br />';
    $str .=  '<strong>Host:</strong> Which computer (or logical grouping if not a physical computer).<br />';
    $str .=  '<strong>Service:</strong> The specific service (i.e. web server, database server, etc.).<br />';
    $str .=  '<strong>Status:</strong> Current status (i.e. OK, WARNing, CRITical).<br />';
    $str .=  '<strong>Output:</strong> Output from the check plugin.<br />';
    $str .=  '<strong>Last Check:</strong> How long ago it was last checked (most services should be 5 minutes or less).<br />';
    $str .=  '<strong>Last Change:</strong> How long ago since Status last changed (small amounts of time could be a problem if it\'s currently OK).<br />';
    $str .=  '<strong>Age:</strong> Age of current information (how long ago this information was grabbed from Nagios).<br />';
    $str .=  '<strong>Exec Time:</strong> How long it took to check (numbers over 4-5s are a problem).<br />';
    $str .=  '<strong>Ack:</strong> Whether a problem has been acknowledged.<br />';
    $str .=  '<p>'."\n";
    return $str;
}

/**
 * Return a div containing information on the Nagios program
 * @return string
 */
function getProgramInfo()
{
    global $top;
    // program stuff
    $program = $top->item(0)->getElementsByTagName("programStatus");
    foreach($program as $elem)
    {
	$nagios_pid = $elem->getElementsByTagName("nagios_pid")->item(0)->nodeValue;
	$program_start = $elem->getElementsByTagName("program_start")->item(0)->nodeValue;
	$last_command_check = $elem->getElementsByTagName("last_command_check")->item(0)->nodeValue;
    }

    return  '<div class="nagiosInfo"><strong>Program Info:</strong> PID: '.$nagios_pid.' Start: '.format_ts($program_start).' ago Last Check: '.format_ts($last_command_check).' ago</div>';
}

/**
 * Return the HTML string for the table start and header row.
 * @return string
 */
function getTableHeader()
{
    // TABLE SETUP
    $str = "";
    $str .=  '<table class="nagiosStatus">'."\n";
    $str .=  '<tr>'."\n";
    $str .=  '<th>Host</th><th>Service</th><th>Status</th><th>Output</th><th>Last Check</th><th>Last Change</th><th>Age</th><th>Exec Time</th><th>Ack</th>'."\n";
    $str .=  '</tr>'."\n";
    return $str;
}

/**
 * Return the HTML string for the table end (and footer row, if used).
 * @return string
 */
function getTableFooter()
{
    return  '</table>'."\n";
}

/**
 * Gets the TRs for the host and service statuses.
 * @param $only_bad boolean if true leave out everything OK. Default false.
 * @return string
 */
function getStatusTRs($only_bad = false) 
{
    global $doc, $hosts;
    $str = "";
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

	$hostStr = host_tr($name, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time);

	// SERVICES
	// parse the services
	$servStr = "";
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
		if($only_bad == false || $s_status != 0)
		{
		    $servStr .= serv_tr($s_service_description, $s_status, $s_plugin_output, $s_last_check, $s_last_state_change, $s_ack, $s_last_update, $s_exec_time);
		}
		$total_services++;
	    }
	}
	// END SERVICES

	if($only_bad == false || $servStr != "")
	{
	    $str .= $hostStr;
	    $str .= $servStr;
	}

	$total_hosts++;
    }
    return $str;
}

//
// FUNCTIONS
//

/**
 * Returns the correct color (hex) for a given status.
 * @param int $status_value the status code from Nagios
 * @return string
 */
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

/**
 * Returns the correct colored ack TD for a given host/service
 * @param int $ack whether it was acknowledged or not
 * @param boolean $is_a_host true if what we're showing is a host
 * @return string
 *
 */
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

/**
 * Format a number of seconds into a textual time period.
 * @param int $ts number of seconds
 * @return string
 */
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

/**
 * PRIVATE - generate a host TR from the host information
 * @return string
 */
function host_tr($name, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time)
{
    $str = "";
    $str .=  '<tr class="hostRow">';
    $str .=  '<td class="hostRow"><strong>'.$name.'</strong></td>';
    $str .=  '<td class="hostRow">(host)</td>';
    $str .=  statusTD($status, true);
    $str .=  '<td class="hostRow">'.$plugin_output.'</td>';
    $str .=  '<td class="hostRow">'.format_ts($last_check).'</td>';
    $str .=  '<td class="hostRow">'.format_ts($last_state_change).'</td>';
    $str .=  '<td class="hostRow">'.format_ts($last_update).'</td>';
    $str .=  '<td class="hostRow">'.$exec_time.'s</td>';
    if($status != 0){ $str .=  ackTD($ack, true);} else { $str .=  '<td class="hostRow">&nbsp;</td>';}
    $str .=  '</tr>';
    return $str;
}

/**
 * PRIVATE - generate a service TR from the service information
 * @return string
 */
function serv_tr($description, $status, $plugin_output, $last_check, $last_state_change, $ack, $last_update, $exec_time)
{
    $str = "";
    $str .=  '<tr>';
    $str .=  '<td>&nbsp;</td>';
    $str .=  '<td>'.$description.'</td>';
    // cope with Remote command execution failed: @@@@@@@@@@@@@@@@@@@[...]
    $str .=  statusTD($status, false);
    if(strpos($plugin_output, "@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@"))
    {
	$plugin_output = substr($plugin_output, 0, strpos($plugin_output, "@")+5)."[...]";
    }

    $str .=  '<td>'.$plugin_output.'</td>';
    $str .=  '<td>'.format_ts($last_check).'</td>';
    $str .=  '<td>'.format_ts($last_state_change).'</td>';
    $str .=  '<td>'.format_ts($last_update).'</td>';
    $str .=  '<td>'.$exec_time.'s</td>';
    if($status != 0){ $str .=  ackTD($ack, false);} else { $str .=  "<td>&nbsp;</td>";}
    $str .=  '</tr>';
    return $str;
}

/**
 * PRIVATE - generate a status TD - select the correct class for the TD based on the status value
 * @param int $status status code
 * @param boolean $is_a_host true if this is a host not a service
 * @return string
 */
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
    elseif($status == 3)
    {
	return '<td class="statusUNK" '.$host.'>UNK</td>';
    }
    else
    {
	return '<td>&nbsp;</td>';
    }
}

?>