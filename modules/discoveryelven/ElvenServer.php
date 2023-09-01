<?php
interface ElvenServer
{
    public function register($serviceName, $version = '1.0.0', $env = 'prod');

    public function unregister($serviceName, $version = '1.0.0', $env = 'prod');
    public function discovery($serviceName, $version = '1.0.0', $env = 'prod');
    public function discoveryAll($serviceName, $version = '1.0.0', $env = 'prod');
    public function heartbeat($serviceName);
}