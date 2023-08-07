<?php

abstract class EzCurlHeader
{
    public $contentLength;

    /**
     * @var array
     */
    public $cookie = [];
    public $contentType;

    /**
     * @return mixed
     */
    public function getContentLength()
    {
        return $this->contentLength;
    }

    /**
     * @param mixed $contentLength
     */
    public function setContentLength($contentLength): void
    {
        $this->contentLength = $contentLength;
    }

    protected function contentTypeToString() {
        return "Content-Type: " . $this->contentType;
    }

    protected function httpVersionToString() {
        return "HTTP/1.1";
    }

    protected function cookieToString() {
        if (empty($this->cookie)) {
            return "";
        }
        return "Cookie: " . wordwrap(implode("; ", $this->cookie), 100);
    }

    protected function contentLengthToString() {
        return "Content-Length: " . $this->contentLength;
    }

    protected function dateToString() {
        return "Date: " . gmdate("D, d M Y H:i:s T");
    }

    /**
     * 构建http头原始信息
     * @return array<string>
     */
    abstract public function buildSource(): array;

    abstract function toString(): string;
}
