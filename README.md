# proxy-checker
Checks Proxy (type - http, socks4, socks5 )

## Steps :
1. Include ProxyCheck Class.
2. Create its object with parameters :
    
    ```php
        $config = [
                        'timeout'   => 100,
                        'check'     => ['get', 'post', 'cookie', 'referer', 'user_agent'],
                    ];
        $proxyChecker = new ProxyChecker($url, $config);
    ```
    
    a. $url : is a variable for url you would like to ping.
    b. $config : is an optional array for the configuring the `timeout` and `check`.
3. Call the checkProxies function with the array of proxies in the format

    ```php
    $result = $proxyChecker->checkProxies($proxies);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    ```
    
