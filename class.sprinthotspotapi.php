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
 * 
 */
/** For this class to work, your computer must be connected to the Sprint Hotspot device.
 * This class was built and tested with the AirCard 803S from Sierra Wireless.
 * 
 * A list of default request headers have been provided for you. Please see 
 * the addRequestHeader(), addRequestHeaders(), getRequestHeaders(), removeRequestHeader(),
 * removeAllRequestHeaders() methods. The only necessary request header is the Referer header.
 * */
class SprintHotspotAPI {
	protected $host;
	protected $password;
	protected $requestHeaders;
	
	public function SprintHotspotAPI($host,$password) {
		$this->host = $host;
		$this->password = $password;
		$this->requestHeaders = array();
		
		$this->addRequestHeaders(array(
			"Accept" => "*/*",
			"Accept-Charset" => "ISO-8859-1,utf-8;q=0.7,*;q=0.3",
			"Accept-Encoding" => "gzip,deflate,sdch",
			"Accept-Language" => "en-US,en;q=0.8",
			"Connection" => "keep-alive",
			"Host" => "sprinthotspot",
			"Origin" => "http://sprinthotspot",
			"Referer" => "http://sprinthotspot/"
		));
	}
	
	/** Should be an associative array */
	public function addRequestHeader(array $header) {
		$keys = array_keys($this->requestHeaders);
		$key = array_keys($header); $key = $key[0];
		$this->requestHeaders[$key] = $header[$key];
	}
	
	public function addRequestHeaders(array $headers) {
		foreach($headers as $key=>$value) {
			$this->addRequestHeader(array($key => $value));
		}
	}
	
	public function getRequestHeaders() {
		return $this->requestHeaders;
	}
	
	public function removeRequestHeader($key) {
		if (isset($this->requestHeaders[$key])) {
			unset($this->requestHeaders[$key]);
		}
	}
	
	public function removeAllRequestHeaders() {
		$this->requestHeaders = array();
	}
	
	/** This method is used to execute most requests to the device */
	public function execute($request) {
		$password = $this->password;
		
		$ch = curl_init($this->host . "ajax_request?" . $request);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this->joinRequestHeaders());
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		$val = curl_exec($ch);
		return $val;
	}
	
	/** Tells the device to power off */
	public function shutdownDevice() {
		$this->addRequestHeaders(array("Pragma"=>"no-cache","Content-Type"=>"application/x-www-form-urlencoded","X-Requested-With"=>"XMLHttpRequest"));
		$postfields = "which_cgi=miniwindowadmin&home_connect_disc=1&poweroff=1";
		$url = $this->host . "cgi-bin/ajax.cgi";
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_HTTPHEADER,$this->joinRequestHeaders());
		curl_setopt($ch,CURLOPT_POST,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postfields);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLINFO_HEADER_OUT,true);
		$val = curl_exec($ch);
		return $val;
	}
	
	private function joinRequestHeaders() {
		$array = array();
		foreach($this->requestHeaders as $key=>$value) {
			$array[] = $key . ": " . $value;
		}
		return $array;
	}
	
	/** Gets the GPS location information from the device. Returns false if unable to connect.
	 * $assoc specifies if the data should be returned as an associative array. Default is false. */
	public function getGPSData($assoc = false) {
		//nLatitude,nLongitude,nHeight,nVelVert,nHeading,nSatelliteNum,nTimeStamp,nHEPE
		$arr = $this->execute("ajax_get_gps_fix_data");
		if ($arr === false) {
			return false;
		}
		$arr = explode(",",$arr);
		if ($assoc == false) {
			return $arr;
		}
		
		$newarr = array(
			"latitude" => trim($arr[0]),
			"longitude" => trim($arr[1]),
			"altitude" => trim($arr[2]),
			"speed" => trim($arr[3]),
			"heading" => trim($arr[4]),
			"satellites" => trim($arr[5]),
			"sat_timestamp" => trim($arr[6]),
			"hepe" => trim($arr[7]),
			"timestamp" => trim($arr[8])
		);
		return $newarr;
	}
	
	public function getSessionDuration() {
		return $this->execute("ajax_get_session_dur");
	}
	
	public function getGPSStatus() {
		return $this->execute("ajax_get_gps_fix_status");
	}
	
	public function getSDCardStatus() {
		// SD card not detected*1
		$val = $this->execute("ajax_get_sd_speak_statu");
		$arr = explode("*",$val);
		return $arr[0];
	}
	
	/** returns 0 or 1 */
	public function getSpeakerStatus() {
		// SD card not detected*1
		$val = $this->execute("ajax_get_sd_speak_statu");
		$arr = explode("*",$val);
		return $arr[1]=="1";
	}
	
	/** returns FALSE if no SD card */
	public function getSDCardCapacity() {
		$val = $this->execute("ajax_get_sd_capacity");
		if (trim($val) == "") {
			return false;
		}
		return $val;
	}
	
	public function getBatteryChargePercentage() {
		return $this->execute("ajax_get_power_level");
	}
	
	/** Returns one of: 3G Only, 4G Only, 3G Preferred, 4G Preferred, null */
	public function getWANMode() {
		$val = $this->execute("ajax_get_wan_modes");
		$val = explode(",",$val);
		$val = $val[0];
		
		switch($val) {
			case "0": return "3G Only";
			case "1": return "4G Only";
			case "2": return "3G Preferred";
			case "3": return "4G Preferred";
			default: return null;
		}
	}
	
	/** Returns one of: 4G Disabled, 4G LTE Only, 4G WiMAX Only, 4G LTE and WiMAX, null */
	public function get4GMode() {
		$val = $this->execute("ajax_get_wan_modes");
		$val = explode(",",$val);
		$val = $val[1];
		
		switch($val) {
			case "0": case "4": return "4G Disabled";
			case "1": return "4G LTE Only";
			case "2": return "4G WiMAX Only";
			case "3": return "4G LTE and WiMAX";
			default: return null;
		}
	}
	
	public function getNetState() {
		return explode("*",$this->execute("ajax_get_netstate"));
	}
	
	public function getCurrentConnectionType() {
		$arr = $this->getNetState();
		return $arr[0];
	}
	
	public function getPanelInfo($assoc = true) {
		// http://sprinthotspot/js/miniwindow.js
		$arr = preg_split("/" . chr(0x08) . "/",$this->execute("ajax_get_panel_info"),null);
		
		if ($assoc) {
			/*$arr["signal_strength"] = $arr[0];
			$arr["wan_mode"] = $arr[1];
			$arr["roamicon_status"] = $arr[2];*/
			
			$pos = strpos($arr[11],"\t(");
			if ($pos !== false) {
				$arr["SSID_PASSWD"] = str_replace(")","",substr($arr[11],$pos+2));
				$arr["SSID"] = substr($arr[11],0,$pos);
			} else {
				$arr["SSID"] = $arr[11];
			}
			
			$arr["sess_data_in"] = $arr[13];
			$arr["sess_data_out"] = $arr[14];
			$arr["sess_data_all"] = $arr[15];
			$arr["sess_ip_addr"] = $arr[16];
		}
		
		return $arr;
	}
	
	public function getSSID() {
		$arr = $this->getPanelInfo(true);
		return $arr["SSID"];
	}
	
	/** This function will only work if Advanced Settings -> Wi-Fi -> Security -> Password Reminder is on. Otherwise, returns null. */
	public function getSSIDPasswd() {
		$arr = $this->getPanelInfo(true);
		if (isset($arr["SSID_PASSWD"])) {
			return $arr["SSID_PASSWD"];
		} else {
			return null;
		}
	}
	
	public function getSessionDataIn() {
		$arr = $this->getPanelInfo(true);
		return $arr["sess_data_in"];
	}
	
	public function getSessionDataOut() {
		$arr = $this->getPanelInfo(true);
		return $arr["sess_data_out"];
	}
	
	public function getSessionDataAll() {
		$arr = $this->getPanelInfo(true);
		return $arr["sess_data_all"];
	}
	
	public function getSessionIPAddress() {
		$arr = $this->getPanelInfo(true);
		return $arr["sess_ip_addr"];
	}
	
	
	public function getSignalStrengths() {
		$arr = $this->getNetState();
		
		$_3g = array($arr[1]);
		$_4g = array($arr[3]);
		$_4glte = array($arr[13]);
		
		switch($_3g[0]) {
			case 99: $_3g[1] = "Searching"; break;
			case 0: $_3g[1] = "Very weak signal (0%)"; break;
			case 1: $_3g[1] = "Very weak signal (10%)"; break;
			case 2: $_3g[1] = "Weak signal (20%)"; break;
			case 3: $_3g[1] = "Good signal (40%)"; break;
			case 4: $_3g[1] = "Good signal (60%)"; break;
			case 5: $_3g[1] = "Great signal (80%)"; break;
			case 6: $_3g[1] = "Excellent signal (100%)"; break;
		}
		
		switch($_4g[0]) {
			case 99: $_4g[1] = "Searching"; break;
			case 98: $_4g[1] = "Power Save Mode"; break;
			case 97: $_4g[1] = "4G Off"; break;
			case 0: $_4g[1] = "Very weak signal (0%)"; break;
			case 1: $_4g[1] = "Very weak signal (10%)"; break;
			case 2: $_4g[1] = "Weak signal (20%)"; break;
			case 3: $_4g[1] = "Good signal (40%)"; break;
			case 4: $_4g[1] = "Good signal (60%)"; break;
			case 5: $_4g[1] = "Great signal (80%)"; break;
			case 6: $_4g[1] = "Excellent signal (100%)"; break;
		}
		
		switch($_4glte[0]) {
			case 99: $_4glte[1] = "Searching"; break;
			case 98: $_4glte[1] = "Power Save Mode"; break;
			case 97: $_4glte[1] = "4G Off"; break;
			case 0: $_4glte[1] = "Very weak signal (0%)"; break;
			case 1: $_4glte[1] = "Very weak signal (10%)"; break;
			case 2: $_4glte[1] = "Weak signal (20%)"; break;
			case 3: $_4glte[1] = "Good signal (40%)"; break;
			case 4: $_4glte[1] = "Good signal (60%)"; break;
			case 5: $_4glte[1] = "Great signal (80%)"; break;
			case 6: $_4glte[1] = "Excellent signal (100%)"; break;
		}
		
		return array(
			"3G" => $_3g[1],
			"4G" => $_4g[1],
			"4G LTE" => $_4glte[1]
		);
	}
}
?>