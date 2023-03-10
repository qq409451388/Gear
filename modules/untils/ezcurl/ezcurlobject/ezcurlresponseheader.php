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
            $varToStringMethodName = $varName."ToString";
            if (method_exists($this, $varToStringMethodName)) {
                $s .= $this->$varToStringMethodName();
            } else {
                Logger::warn(__CLASS__." Unset Method {} for property {}!", $varToStringMethodName, $varName);
            }
        }
        return $s;
    }

    public function buildSource(): array {
        // TODO: Implement buildSource() method.
    }
}
