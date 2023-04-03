<?php
class EzCurlBodyNdJson extends EzCurlBody
{
    /**
     * @var array<EzCurlBodyJson>
     */
    private $data;

    /**
     * HTTP BODY NDJSON
     */
    const BODY_NDJSON = "application/x-ndjson";

    protected function setContentType() {
        $this->contentType = self::BODY_NDJSON;
    }

    public function toString() {
        $s = "";

        foreach ($this->data as $d) {
            $s.= $d->toString().PHP_EOL;
        }

        return $s;
    }
}
