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
        return "Content-Type: ".$this->contentType;
    }

    protected function cookieToString() {
        if (empty($this->cookie)) {
            return "";
        }
        return "Cookie: ".wordwrap(implode("; ", $this->cookie), 100).PHP_EOL;
    }

    /**
     * 构建http头原始信息
     * @return array<string>
     */
    abstract public function buildSource():array;

    abstract function toString():string;
}
