<?php

class EzCurlRequestHeader extends EzCurlHeader
{
    private $customHeader = [];

    public function setCustomHeader(string $header) {
        $this->customHeader[] = $header;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType): void
    {
        $this->contentType = $contentType;
    }

    private function customHeaderToString() {
        if (empty($this->customHeader)) {
            return "";
        }
        return implode(PHP_EOL, $this->customHeader);
    }

    public function toString(): string {
        $vars = get_object_vars($this);
        $s = "";
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

    protected function contentLengthToString() {
        return "Content-Length: " . $this->contentLength;
    }

    public function buildSource(): array {
        $vars = get_object_vars($this);
        $s = [];
        foreach ($vars as $varName => $var) {
            $varToStringMethodName = $varName . "ToString";
            if (method_exists($this, $varToStringMethodName)) {
                $h = $this->$varToStringMethodName();
                if (empty($h)) {
                    continue;
                }
                $s[] = $h;
            } else {
                Logger::warn(__CLASS__ . " Unset Method {} for property {}!", $varToStringMethodName, $varName);
            }
        }
        return $s;
    }
}
