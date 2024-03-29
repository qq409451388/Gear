<?php

class EzCurlResponseHeader extends EzCurlHeader
{
    public $httpVersion;
    public $httpStatus;
    public $keepLive;
    public $server;
    public $date;

    public function toString(): string {
        $s = "";
        $vars = get_object_vars($this);
        foreach ($vars as $varName => $var) {
            $varToStringMethodName = $varName . "ToString";
            if (method_exists($this, $varToStringMethodName)) {
                $s .= $this->$varToStringMethodName().PHP_EOL;
            } else {
                Logger::warn(__CLASS__ . " Unset Method {} for property {}!", $varToStringMethodName, $varName);
            }
        }
        return $s;
    }

    protected function dateToString() {
        return "Date: " . $this->date;
    }

    protected function httpStatusToString() {
        return "HTTP/1.1 " . $this->httpStatus;
    }

    protected function serverToString() {
        return "Server: " . $this->server;
    }

    public function buildSource(): array {
        // TODO: Implement buildSource() method.
    }

    protected function keepLiveToString() {
        return "Connection: " . $this->keepLive;
    }

    protected function httpVersionToString() {
        return "HTTP/".$this->httpVersion;
    }
}
