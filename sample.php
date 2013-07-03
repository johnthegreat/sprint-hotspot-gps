<?php
/** Copyright (c) 2012-2013 John Nahlen (john.nahlen@gmail.com)
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:
 *
 * Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
 * Neither the name of the authors nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

// First lets include the main class.
require_once("class.sprinthotspotapi.php");
// create a new instance
$api = new SprintHotspotAPI("http://sprinthotspot/","your-password-here");
// get to the meat and potatoes of the demo! grab the gps data.
$gpsdata = $api->getGPSData(true);
if ($gpsdata !== false) {
	echo "Your location is: " . $gpsdata["latitude"] . "," . $gpsdata["longitude"] . " as of " . $gpsdata["sat_timestamp"] . "<br />";
	echo "Full GPS Details: <pre>" . print_r($gpsdata,true) . "</pre>";
} else {
	echo "Unable to connect";
}

$chargePercent = $api->getBatteryChargePercentage();
echo "Your Sprint Hotspot device " . (($chargePercent < 100)?"has " . $chargePercent . "% battery life left.":"is fully charged.") . "<br />";

echo "Your IP address is " . $api->getSessionIPAddress() . "<br />";

echo "Your SSID is " . $api->getSSID() . "<br />";

echo "You are currently using " . $api->getCurrentConnectionType() . "<br />";

echo "The session has been active for " . $api->getSessionDuration() . " and " . $api->getSessionDataAll() . " has been used.<br />";

echo "The devices' speaker is " . ($api->getSpeakerStatus()=="1"?"on":"off") . ".<br />";

// Uncomment this line to shut down the device!
//$api->shutdownDevice();

echo "<p>This page will automatically refresh every 20 seconds.</p><meta http-equiv=\"refresh\" content=\"20\">";

echo "<p>Look at what other cool features I have!<p>";

echo "charge percentage: " . $api->getBatteryChargePercentage() . "<br />";
echo "your 4g mode: " . $api->get4GMode() . "<br />";
echo "your gps status: " . $api->getGPSStatus() . "<br />";
echo "your net state: <pre>" . print_r($api->getNetState(),true) . "</pre><br />";
echo "sd card capacity: " . $api->getSDCardCapacity() . "<br />";
echo "sd card status: " . $api->getSDCardStatus() . "<br />";
echo "your session duration: " . $api->getSessionDuration() . "<br />";
echo "signal strengths: <pre>" . print_r($api->getSignalStrengths(),true) . "</pre><br />";
echo "speaker status: " . $api->getSpeakerStatus() . "<br />";
echo "your wan mode: " . $api->getWANMode() . "<br />";
echo "your panel info: <pre>" . print_r($api->getPanelInfo(),true) . "</pre>";
echo "all data used this session: " . $api->getSessionDataAll() . "<br />";
echo "all data in this session: " . $api->getSessionDataIn() . "<br />";
echo "all data out this session: " . $api->getSessionDataOut() . "<br />";
echo "your ip address: " . $api->getSessionIPAddress() . "<br />";
echo "your ssid: " . $api->getSSID() . "<br />";
echo "your ssid password (if available): " . $api->getSSIDPasswd() . "<br />";
?>