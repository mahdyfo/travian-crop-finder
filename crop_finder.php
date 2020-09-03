<?php

$server = 'ts4.travian.de';
$username = 'mahdyfo';
$password = 'xxx';
$where = [42, -33];
$distance = 25;
$crop = [15, 9]; // or 9

$use_proxy = true;
$proxy_ip = '127.0.0.1';
$proxy_port = '9150';

function get_base_curl_handler($path){
	global $server, $use_proxy, $proxy_ip, $proxy_port;
	
	$url = 'https://' . $server . '/' . $path;
	$c = curl_init($url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	if($use_proxy){
		curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL , 1);
		curl_setopt($c, CURLOPT_PROXYTYPE, 7); // Tor
		curl_setopt($c, CURLOPT_PROXY, $proxy_ip);
		curl_setopt($c, CURLOPT_PROXYPORT, $proxy_port);
	}
	curl_setopt($c, CURLOPT_HTTPHEADER, ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0']);
	
	return $c;
}

// returns cookie
function auth($username, $password){	
	$c = get_base_curl_handler('login.php');
	$result = curl_exec($c);
	
	// get csrf values from page
	preg_match('/\{"type":"submit","value":"(.*?)","name":"(.*?)"/', $result, $matches);
	$btn_name = $matches[2];
	$btn_val = $matches[1];
	preg_match('/<input type="hidden" name="login" value="(.*?)"/', $result, $matches2);
	$login_csrf = $matches2[1];
	
	
	$c = get_base_curl_handler('login.php');
	// Login
	curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query(['name' => $username, 'password' => $password, 'w'=>'1920:1080', 'login' => $login_csrf, $btn_name => $btn_val]));
	curl_setopt($c, CURLOPT_HEADER, 1);
	curl_setopt($c, CURLOPT_POST, 1);
	$result = curl_exec($c);
	
	if(curl_errno($c)){
		throw new Exception(curl_error($c));
	}
	
	curl_close($c);
	
	echo "Logged in\n"; flush();
	$cookies = extract_cookie($result);
	
	file_put_contents('cookies.txt', $cookies);
	
	return $cookies;
}

function extract_cookie($response){
	preg_match_all('/^set-cookie:\s*([^;]*)/mi', $response, $matches);
	return $matches[1][0];
}

function get_api_key($cookie){
	$c = get_base_curl_handler('dorf1.php');
	
	curl_setopt($c, CURLOPT_HTTPHEADER, ['Cookie: ' . $cookie]);
	$result = curl_exec($c);
	
	// extract encoded api key
	preg_match('/eval\(atob\(\'(.*?)\'\)\)/', $result, $matches);
	$raw_api_key = base64_decode($matches[1]);
	
	// extract api key
	preg_match('/\'(.*?)\'/', $raw_api_key, $matches2);
	$api_key = $matches2[1];
	
	curl_close($c);
	
	echo "Got api key\n"; flush();
	file_put_contents('api_key.txt', $api_key);
	
	return $api_key;
}

function check($crop, $x, $y){
	global $cookie, $api_key, $username, $password;
	$c = get_base_curl_handler('api/v1/ajax/viewTileDetails');
	
	curl_setopt($c, CURLOPT_POSTFIELDS, json_encode(['x'=>$x, 'y'=>$y]));
	curl_setopt($c, CURLOPT_HTTPHEADER, ['Cookie: ' . $cookie, 'TE: Trailers', 'Authorization: Bearer ' . $api_key, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0', 'X-Requested-With: XMLHttpRequest', 'Content-Type: application/json;']);

	$result = curl_exec($c);
	
	if(curl_errno($c)){
		throw new Exception(curl_error($c));
	}
	
	if(preg_match('/login\.php/', $result)){
		echo "expired api key\n";
		$cookie = auth($username, $password);
		$api_key = get_api_key($cookie);
	}
	
	$matches = [];
	preg_match('/<i class=\\\"r4\\\"\s.*?<td class=\\\"val\\\">(?:'. implode('|', $crop) .')</', $result, $matches);
	$matches = array_filter($matches);
	
	curl_close($c);
	
	if(count($matches) > 0){
		return true;
	}else{
		unset($matches);
		return false;
	}
}

$cookie = @file_get_contents('cookies.txt');

$api_key = @file_get_contents('api_key.txt');

$crops_found = 0;
$i=1;
for($x = $where[0] - $distance; $x < $where[0] + $distance; $x++)
{
	for($y = $where[1] + $distance; $y > $where[1] - $distance; $y--)
	{
		if(check($crop, $x, $y) > 0)
		{
			$crops_found++;
			echo " [".$x.",".$y."] ";
			flush();
		}else{
			echo '.';
		}
		
		if($i % (2 * $distance + 1) == 0){
			echo "\n";flush();
		}
		
		$i++;
	}
}

echo "\n--------------\nFound " . $crops_found . " results.";