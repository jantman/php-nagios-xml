<?php

// Time-stamp: "2009-03-27 01:29:50 jantman"
// $Id: parseNagiosXML.php,v 1.1 2009/03/29 05:30:51 jantman Exp $

// parses Nagios output from XML generator on MON1 into HTML

#$file = "http://voip1/statusXML.php";
$file = "statusXML.xml";
$nagios_host = "VoIP1";



// PARSE IT
$doc = new DOMDocument();
$doc->load($file);

$top = $doc->getElementsByTagName("nagios_status"); // returns DOMNodeList
$hosts = $top->item(0)->getElementsByTagName("host");
$services = $doc->getElementsByTagName("services");
$services = $services->item(0)->getElementsByTagName("service");

$hostAry = array();
// HOSTS
foreach($hosts as $value)
{
    $name = $value->getElementsByTagName("host_name")->item(0)->nodeValue;
    $hostAry[$name] = array();
}

// SERVICES
// parse the services
foreach($services as $serValue)
{
    $service_host = $serValue->getElementsByTagName("host_name")->item(0)->nodeValue;
    $s_status = $serValue->getElementsByTagName("current_state")->item(0)->nodeValue;
    $s_plugin_output = $serValue->getElementsByTagName("plugin_output")->item(0)->nodeValue;
    $s_last_check = $serValue->getElementsByTagName("last_check")->item(0)->nodeValue;
    $s_last_state_change = $serValue->getElementsByTagName("last_state_change")->item(0)->nodeValue;
    $s_ack = $serValue->getElementsByTagName("problem_has_been_acknowledged")->item(0)->nodeValue;
    $s_last_update = $serValue->getElementsByTagName("last_update")->item(0)->nodeValue;
    $s_service_description = $serValue->getElementsByTagName("service_description")->item(0)->nodeValue;
    $s_exec_time = $value->getElementsByTagName("check_execution_time")->item(0)->nodeValue;
    $temp = array();
    $temp['status'] = $s_status;
    $temp['last_check'] = $s_last_check;
    $temp['ack'] = $s_ack;
    $hostAry[$service_host][$s_service_description] = $temp;
}

// start making the strings
$line1 = "PCRserv, MPAC-SW1 OK";
$line2 = "VoIP1 OK";
$line3 = "WAN, Asterisk OK";
$line4 = "";
$led1 = 0; // 0==green, 1==orange, 2==red
$led2 = 0;
$led3 = 0;
$led4 = 0;

// higher priorities overwrite lower priorities

// SW1 - L1 Pri 1
if(($hostAry['MPAC-SW1']['Uptime']['status'] != 0 && $hostAry['MPAC-SW1']['Uptime']['ack'] == 0) || ($hostAry['MPAC-SW1']['PING']['status'] != 0 && $hostAry['MPAC-SW1']['PING']['ack'] == 0))
{
    $led1 = 1;
    $line1 = str_pad("MPAC-SW1 FAIL", 15).chr(160).date("Hi", $hostAry['MPAC-SW1']['PING']['last_check']);
}

// PCRserv SW - L1 Pri 2
if(($hostAry['PCRserv']['Check HTTP External If']['status'] != 0 && $hostAry['PCRserv']['Check HTTP External If']['ack'] == 0) || ($hostAry['PCRserv']['Check HTTP Internal If']['status'] != 0 && $hostAry['PCRserv']['Check HTTP Internal If']['ack'] == 0) || ($hostAry['PCRserv']['Load Average']['status'] != 0 && $hostAry['PCRserv']['Load Average']['ack'] == 0) || ($hostAry['PCRserv']['MySQLd']['status'] != 0 && $hostAry['PCRserv']['MySQLd']['ack'] == 0) || ($hostAry['PCRserv']['Total Processes']['status'] != 0 && $hostAry['PCRserv']['Total Processes']['ack'] == 0))
{
    $led1 = 2;
    $line1 = str_pad("PCRserv SW FAIL", 15).chr(160).date("Hi", $hostAry['PCRserv']['MySQLd']['last_check']);
}

// PCRserv HW - L1 Pri 3
if(($hostAry['PCRserv']['Fans']['status'] != 0 && $hostAry['PCRserv']['Fans']['ack'] == 0) || ($hostAry['PCRserv']['Power Supplies']['status'] != 0 && $hostAry['PCRserv']['Power Supplies']['ack'] == 0) || ($hostAry['PCRserv']['Processors']['status'] != 0 && $hostAry['PCRserv']['Processors']['ack'] == 0) || ($hostAry['PCRserv']['Temperatures']['status'] != 0 && $hostAry['PCRserv']['Temperatures']['ack'] == 0))
{
    $led1 = 2;
    $line1 = str_pad("PCRserv HW FAIL", 15).chr(160).date("Hi", $hostAry['PCRserv']['MySQLd']['last_check']);
}

// VoIP1 - Asterisk - L3 Pri 3
if(($hostAry['VoIP1']['Asterisk - LesNet Peer']['status'] != 0 && $hostAry['VoIP1']['Asterisk - LesNet Peer']['ack'] == 0) || ($hostAry['VoIP1']['Asterisk - Mgr']['status'] != 0 && $hostAry['VoIP1']['Asterisk - Mgr']['ack'] == 0) || ($hostAry['VoIP1']['Asterisk Proc Running']['status'] != 0 && $hostAry['VoIP1']['Asterisk Proc Running']['ack'] == 0))
{
    $led3 = 1;
    $line3 = str_pad("VoIP1 AST FAIL", 15).chr(160).date("Hi", $hostAry['VoIP1']['Asterisk - LesNet Peer']['last_check']);
}

// VoIP1 SW - L2 Pri 1
if(($hostAry['VoIP1']['Current Load']['status'] != 0 && $hostAry['VoIP1']['Current Load']['ack'] == 0) || ($hostAry['VoIP1']['PING']['status'] != 0 && $hostAry['VoIP1']['PING']['ack'] == 0) || ($hostAry['VoIP1']['Swap Usage']['status'] != 0 && $hostAry['VoIP1']['Swap Usage']['ack'] == 0))
{
    $led2 = 1;
    $line2 = str_pad("VoIP1 SW FAIL", 15).chr(160).date("Hi", $hostAry['VoIP1']['Current Load']['last_check']);
}

// VoIP1 HW - L2 Pri 2
if(($hostAry['VoIP1']['Power Supplies']['status'] != 0 && $hostAry['VoIP1']['Power Supplies']['ack'] == 0) || ($hostAry['VoIP1']['Processors']['status'] != 0 && $hostAry['VoIP1']['Processors']['ack'] == 0) || ($hostAry['VoIP1']['DIMMs']['status'] != 0 && $hostAry['VoIP1']['DIMMs']['ack'] == 0) || ($hostAry['VoIP1']['Fans']['status'] != 0 && $hostAry['VoIP1']['Fans']['ack'] == 0) || ($hostAry['VoIP1']['Temperatures']['status'] != 0 && $hostAry['VoIP1']['Temperatures']['ack'] == 0))
{
    $led2 = 2;
    $line2 = str_pad("VoIP1 HW FAIL", 15).chr(160).date("Hi", $hostAry['VoIP1']['Current Load']['last_check']);
}

//WAN
if(($hostAry['wantest']['PING Google']['status'] != 0 && $hostAry['wantest']['PING Google']['ack'] == 0) || ($hostAry['wantest']['PING OptOnline.net']['status'] != 0 && $hostAry['wantest']['PING OptOnline.net']['ack'] == 0) || ($hostAry['wantest']['PING Rutgers']['status'] != 0 && $hostAry['wantest']['PING Rutgers']['ack'] == 0) || ($hostAry['wantest']['PING Yahoo']['status'] != 0 && $hostAry['wantest']['PING Yahoo']['ack'] == 0))
{
    $led3 = 2;
    $line3 = str_pad("WAN Conn FAIL", 15).chr(160).date("Hi", $hostAry['wantest']['PING Google']['last_check']);
}

// we now have an array of the information for the first 3 lines and LEDs

echo $led1.",".$line1."\n";
echo $led2.",".$line2."\n";
echo $led3.",".$line3."\n";

?>