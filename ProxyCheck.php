<?php
namespace ProxyCheck;

/*
*   type - http, socks4, socks5 
*   password and type if not required 
*/
class ProxyCheck
{
    private $proxyCheckUrl;

    private $config = [
                        'timeout'   => 100,
                        'check'     => ['get', 'post', 'cookie', 'referer', 'user_agent'],
                    ];

    public function __construct($proxyCheckUrl, array $config = [])
    {
        $this->proxyCheckUrl = $proxyCheckUrl;
        $this->setConfig($config);
    }

    public function setConfig(array $config)
    {
        $this->config = array_merge($this->config, $config);
    }

    public function checkProxies(array $proxies)
    {
        $results = [];

        foreach ($proxies as $proxy) {
            
            try {
                if ( !empty($proxy) ) {
                    $results[$proxy] = $this->checkProxy($proxy);
                }
            } catch (\Exception $e) {
                $results[$proxy]['error'] = $e->getMessage();
            }
        }

        return $results;
    }

    public function checkProxy($proxy)
    {
        list($content, $info) = $this->getProxyContent($proxy);
        return $this->checkProxyContent($content, $info);
    }

    private function getProxyContent($proxy)
    {
        @list($proxyIp, $proxyPassword, $proxyType) = explode(',', $proxy);

        $url = $this->proxyCheckUrl;
        $ch = curl_init($url);

        // check query
        if (in_array('get', $this->config['check'])) {
            $url .= '';
        }

        $options = [
                    CURLOPT_PROXY          => $proxyIp,
                    CURLOPT_HEADER         => true,
                    CURLOPT_TIMEOUT        => $this->config['timeout'],
                    CURLOPT_CONNECTTIMEOUT => $this->config['timeout'],
                    CURLOPT_RETURNTRANSFER => true
                ];

        if (!empty($proxyPassword)) {
            
            $options[CURLOPT_PROXYAUTH] = CURLAUTH_BASIC;
            $options[CURLOPT_PROXYUSERPWD] = $proxyPassword;
        }

        // check post
        if (in_array('post', $this->config['check'])) {
            
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = [
                                                'r' => 'request'
                                            ];
        }
        
        // check cookie
        if (in_array('cookie', $this->config['check']))
            $options[CURLOPT_COOKIE] = 'c=cookie';

        // check referer
        if (in_array('referer', $this->config['check']))
            $options[CURLOPT_REFERER] = 'http://www.google.com/';

        // check user agent
        if (in_array('user_agent', $this->config['check']))
            $options[CURLOPT_USERAGENT] = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)';

        if ( !empty($proxyIp) && !empty($proxyType) ) {

            if ( 'http'  ==  $proxyType )
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_HTTP;
            elseif ( 'Socks4'  ==  $proxyType )
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS4;
            elseif ( 'Socks5'  ==  $proxyType )
                $options[CURLOPT_PROXYTYPE] = CURLPROXY_SOCKS5;            
        }

        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);

        return array($content, $info);
    }

    private function checkProxyContent($content, $info)
    {
        if (!$content)
            throw new \Exception('Empty content');

        if ( strpos($content, 'check this string in proxy response content') !== false )
            throw new \Exception('Wrong content');

        if (200 !== $info['http_code'])
            throw new \Exception('Code invalid: ' . $info['http_code']);

        $allowed = [];
        $disallowed = [];
        foreach ($this->config['check'] as $value) {
            
            if ( strpos($content, "allow_$value") !== false )
                $allowed[] = $value;
            else
                $disallowed[] = $value;
        }

        // proxy level
        $proxyLevel = '';
        if (strpos($content, 'proxylevel_elite') !== false)
            $proxyLevel = 'elite';
        elseif (strpos($content, 'proxylevel_anonymous') !== false)
            $proxyLevel = 'anonymous';
        elseif (strpos($content, 'proxylevel_transparent') !== false)
            $proxyLevel = 'transparent';

        return [
                    'allowed'     => $allowed,
                    'disallowed'  => $disallowed,
                    'proxy_level' => $proxyLevel,
                    'info'        => $info
                ];
    }
}
