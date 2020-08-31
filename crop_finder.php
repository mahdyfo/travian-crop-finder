<?php

$server = 'ts4.travian.de';
$api_key = 'xxx'; // The api key can be found in the headers of map ajax requests
$where = [42, -33];
$distance = 2;
$crop = 15; // or 9

function check($x, $y, $use_proxy = false){
	global $server, $crop, $api_key;
	$url = 'https://'.$server.'/api/v1/ajax/viewTileDetails';
	$c = curl_init($url);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
	if($use_proxy){
		curl_setopt($c, CURLOPT_HTTPPROXYTUNNEL , 1);
		curl_setopt($c, CURLOPT_PROXYTYPE, 7);
		curl_setopt($c, CURLOPT_PROXY, '127.0.0.1');
		curl_setopt($c, CURLOPT_PROXYPORT, '9150');
	}
	curl_setopt($c, CURLOPT_POSTFIELDS, json_encode(['x'=>$x, 'y'=>$y]));
	curl_setopt($c, CURLOPT_HTTPHEADER, ['Cookie: travian-language=de-DE; JWT=eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJleHAiOjE1OTg4NTQzMTUsInByb3BlcnRpZXMiOnsic2FsdCI6IjliMjc3NTMzNGNmODNmZWFiMTczYTUwMTcxYWE3YzhjIiwiaGFzaCI6IjAwMDAwMDAwNjM3MDYzNzA3OTJhYzA1ZjkzM2RjMGU4IiwibG9naW5JZCI6NjI2MjUsImxvd1JlcyI6ZmFsc2UsIm1vYmlsZU9wdGltaXphdGlvbnMiOmZhbHNlLCJ2aWxsYWdlUGVyc3BlY3RpdmUiOiJwZXJzcGVjdGl2ZVJlc291cmNlcyIsInV1aWQiOiJmMjIzYjAwMC1kYmRhLTExZWEtMDkwNC0wMTAwMDAwMDA3MzYiLCJwdyI6ImM3NmFjZmVmMTgwMWEyZjU2ZmFmMjNjZTk4NGIwYjU4YTAwODYzZDkifX0.ZGxDBbZwN5k_a1orh7aCt81Nv3UQKLc4u5AtYRVr7eVRgeA_AugLAdOTV-5wfnWfhfeZIjvmm9rcCbckHEvW3lSTr-JatJ62weCIGzKWyj7RSgfJGj1-d5L2CiGhRC2Qd_yH9FGqUBaykDsWIx4EhPD8h-mRzgSP_wc1yHycaVh7FPiWQMCK1L7f3L3od4fiBIHEKtfYzTRSOXIJgn07Y2UvmMWBazIzFA_Cx2JR4Xp70HOP1c6eef6kuzKBH-ypRPp2Tr-OkerBfVxL4vkeJJkemhCFo1dLP4Esj3qBpAfxeU0CxLPtMkf2-kmqHbTYCSllSAj1TtHsd_H0UObT2w; CookieConsent={stamp:%279pvTqspCWEVNOcq00Wq+235VMT+Owm/SLkY+a8jvmjAukt/1LVTrOw==%27%2Cnecessary:true%2Cpreferences:true%2Cstatistics:true%2Cmarketing:true%2Cver:1%2Cutc:1598411404950%2Ciab2:%27CO4sEi-O4sEi-CGABBENAzCsAP_AAH_AAAAAGVtf_X9fb2vj-_599_t0eY1f9_63t-wzjheNs-8NyZ_X_J4Xv2MyvB34pqYKmR4kunbBAQdtHGncTQgBwIlVqTLsYk2MjzNKJ7JEmlsbe2dYGH9vn8XT_ZKZ70-v___7v3______7oGUEEmGpfAQJCWMBJNmlUKIEIVxIVAOACihGFo0sNCRwU7I4CPUACABAYgIQIgQYgohZBAAIAAElEQAgAwIBEARAIAAQAjQEIACJAEFgBIGAQACoGhYARRBKBIQYHBUcogQFSLRQTzAAAAA.YAAAAAAAAAAA%27%2Cgacm:%271~AQgAABAAAAIiAABAAAgAIAAABEAhAACACAAABAAAQAQQAAAAAAABBBAAIAoAAAAAAAAAAQAAAIDAAAAAIgMAAAAAAAgAAAASAAoAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAQAAAAAAAgAAAAAAAAAAIAAAAAAAEAAAAAAAAAAAQAAAAAAFAAAADAAAAAAAwAAAARBAAAIgAgAAACAAABABAAAAAgAAEAAAAAAABAAAAAFAAAAAAAAAEAAAAAAAAACBAAAAAAAAAAAAQAAQAAAAAAAgABQAAQAQAAAAAAAAAACAAAAACAgBAgAAAIAAAAAAAAAAAAAAIAAAAAAAAAIMAAAAAAAAAAAAAAAAAAAAgAAAAAAAEAAAAACAQAAgAAAAABAAEAAAAAAAAAAAAAAAAAAAAAAAAAhAAAAAAkQAAAAAAAAAAAAABAAQAAAAAAAAAAAAAAACw%27%2Cregion:%27nl%27}; mapId1=%7B%22grid%22%3Atrue%7D', 'TE: Trailers', 'Authorization: Bearer ' . $api_key, 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:80.0) Gecko/20100101 Firefox/80.0', 'X-Requested-With: XMLHttpRequest', 'Content-Type: application/json;']);

	$result = curl_exec($c);
	if(curl_errno($c)){
		throw new Exception(curl_error($c));
	}
	
	$matches = [];
	preg_match('/<td class=\\\"val\\\">'.$crop.'<\\\\\/td>\\\n\s+<td class=\\\"desc\\\">Getreidefarmen/', $result, $matches);

	curl_close($c);
	
	return count($matches);
}

$crops_found = 0;
for($x = $where[0] - $distance; $x < $where[0] + $distance; $x++)
{
	for($y = $where[1] - $distance; $y < $where[1] + $distance; $y++)
	{
		if(check($x, $y, true) > 0)
		{
			$crops_found++;
			echo "Found : [".$x.",".$y."]\n";
			flush();
		}
	}
}

echo "\n--------------\nFound " . $crops_found . " results.";