<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Node.php v0.1
 * (c) 2014 Jerzy Głowacki
 * MIT License
 *
 * Modified by Knunu
 * Specialize to get Salah pray times - 15.08.21.
 */

error_reporting(E_ALL);
set_time_limit(120);

class Node
{
	public function node_start($file) {

		#$file = escapeshellarg($file);
		$result = exec("../usr/bin/nodejs $file");

		#sleep(1); //Wait for node to spin up

		return $result;
	}

	public function node_serve($path = "") {
		$result = '';

		if(!file_exists(NODE_DIR)) {
			$result = "Node.js is not yet installed. Switch to Admin Mode.\n";
			return $result;
		}

		$node_pid = intval(file_get_contents("nodepid"));

		if($node_pid === 0) {
			$result = "Node.js is not yet running. Switch to Admin Mode.\n";
			return $result;
		}

		$curl = curl_init("http://52.68.136.59" . "/$path");
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$resp = curl_exec($curl);
		if($resp === false) {
			$result = "Error requesting $path: " . curl_error($curl);
		} else {
			# response control part
		}
		curl_close($curl);

		return $result;
	}
}
