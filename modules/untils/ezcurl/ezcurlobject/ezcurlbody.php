<?php
abstract class EzCurlBody
{
    /**
     * @see {
     *  @link EzCurl2::BODY_X_WWW_FORM
     *  @link EzCurlBodyFormData::BODY_FORM_DATA
     *  @link EzCurlBodyJson::BODY_JSON
     *  @link EzCurlBodyNdJson::BODY_NDJSON
     *  @link EzCurl2::BODY_FILE
     * }
     * @param string $contentType
     */
    public $contentType;

    public function __construct() {
        $this->setContentType();
    }

    abstract protected function setContentType();

    abstract public function toString();

    public function getContentType() {
        return $this->contentType;
    }
}
