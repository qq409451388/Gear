<?php
interface DiscoveryServer extends EzBean,EzComponent
{
    public function register($serviceName, $server, $version = '1.0.0', $env = 'prod') : EzRpcResponse;

    public function unregister($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse;
    public function discovery($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse;
    public function discoveryAll($serviceName, $version = '1.0.0', $env = 'prod') : EzRpcResponse;
    public function heartbeat($serviceName) : EzRpcResponse;
}