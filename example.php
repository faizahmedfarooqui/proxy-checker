<?php
include './ProxyChecker.php';

$proxies = [
		'XXX.XXX.XXX.XXX:XXXX,username:password,Socks4',
                'XXX.XXX.XXX.XXX:XXXX,username:password,Socks5',
                'XXX.XXX.XXX.XXX:XXXX'
	];

$proxyChecker = new ProxyChecker('http://www.google.com/');
$results = $proxyChecker->checkProxies($proxies);

echo "<pre>";
print_r($results);
echo "</pre>";
