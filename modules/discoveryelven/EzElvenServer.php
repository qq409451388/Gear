<?php

/**
 * @RequestController("/elven")
 */
class EzElvenServer extends BaseController implements DiscoveryServer
{
    private static $instances = [];

    /**
     * @PostMapping("/register")
     * @param $serviceName
     * @param $server
     * @param $version
     * @param $env
     */
    public function register($serviceName, $server, $version = '1.0.0', $env = 'prod') : EzRpcResponse
    {
        if (!isset(self::$instances[$serviceName.$env.$version])) {
            self::$instances[$serviceName.$env.$version] = [];
        }
        self::$instances[$serviceName.$env.$version][] = $server;
        return EzRpcResponse::OK();
    }

    /**
     * @PostMapping("/unregister")
     * @param $serviceName
     * @param $version
     * @param $env
     */
    public function unregister($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse
    {
        unset(self::$instances[$serviceName.$env.$version]);
        return EzRpcResponse::OK();
    }

    /**
     * @GetMapping("/discovery")
     * @param $serviceName
     * @param $version
     * @param $env
     */
    public function discovery($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse
    {
        $serverList = self::$instances[$serviceName.$env.$version] ?? [];
        return EzRpcResponse::OK(empty($serverList) ? "" : $serverList[mt_rand(0, count($serverList) - 1)]);
    }

    /**
     * @GetMapping("/discoveryAll")
     * @param $serviceName
     * @param $version
     * @param $env
     * @return array
     */
    public function discoveryAll($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse
    {
        return EzRpcResponse::OK(self::$instances[$serviceName.$env.$version] ?? []);
    }

    public function heartbeat($serviceName) : EzRpcResponse
    {
        $curlM = new EzCurlM();
        foreach (self::$instances as $instanceName => $serverList) {
            if (EzString::containString($instanceName, $serviceName)) {
                foreach ($serverList as $server) {
                    $curl = new EzCurl();
                    $curl->setUrl($server."/heartbeat");
                    $curl->setAlias($instanceName);
                    $curlM->addHandler($curl);
                }
            }
        }
        $res = $curlM->exec();
        foreach ($res as $instanceName => $rtn) {
            if ($rtn['error'] != '' || $rtn['result'] != 'ok') {
                unset(self::$instances[$instanceName]);
            }
        }
        return EzRpcResponse::OK();
    }
}