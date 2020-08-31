<?php

$server = 'ts4.travian.de';
$api_key = 'xxx'; // The api key can be found in the headers of map ajax requests
$cookie = 'xxx';
$where = [42, -33];
$distance = 2;
$crop = 6; // or 9

$use_proxy = true;
$proxy_ip = '127.0.0.1';
$proxy_port = '9150';

function check($x, $y){
	global $server, $crop, $api_key, $cookie, $use_proxy, $proxy_ip, $proxy_port;
	$url = 'https://'.$server.'/api/v1/ajax/viewTileDetails';
	$c = curl_init($url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	if($use_proxy){
		curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL , 1);
		curl_setopt($c, CURLOPT_PROXYTYPE, 7); // Tor
		curl_setopt($c, CURLOPT_PROXY, $proxy_ip);
		curl_setopt($c, CURLOPT_PROXYPORT, $proxy_port);
	}
	curl_setopt($c, CURLOPT_POSTFIELDS, json_encode(['x'=>$x, 'y'=>$y]));
	curl_setopt($c, CURLOPT_HTTPHEADER, ['Cookie: ' . $cookie, 'TE: Trailers', 'Authorization: Bearer ' . $api_key, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0', 'X-Requested-With: XMLHttpRequest', 'Content-Type: application/json;']);

	$result = curl_exec($c);
	
	if(curl_errno($c)){
		throw new Exception(curl_error($c));
	}
	
	if(preg_match('/login\.php/', $result)){
		die('bad api key');
	}
	
	$matches = [];
	preg_match('/<td class=\\\"val\\\">'.$crop.'<\\\\\/td>\\\n\s+<td /', $result, $matches);

	curl_close($c);
	
	return count($matches);
}

$crops_found = 0;
for($x = $where[0] - $distance; $x < $where[0] + $distance; $x++)
{
	for($y = $where[1] - $distance; $y < $where[1] + $distance; $y++)
	{
		if(check($x, $y) > 0)
		{
			$crops_found++;
			echo "Found : [".$x.",".$y."]\n";
			flush();
		}
	}
}

echo "\n--------------\nFound " . $crops_found . " results.";