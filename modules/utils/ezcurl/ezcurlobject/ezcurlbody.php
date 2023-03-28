<?php
abstract class EzCurlBody
{
    /**
     * @see {
     *  @link EzCurlBodyXForm::BODY_X_WWW_FORM
     *  @link EzCurlBodyFormData::BODY_FORM_DATA
     *  @link EzCurlBodyJson::BODY_JSON
     *  @link EzCurlBodyNdJson::BODY_NDJSON
     *  @link EzCurlBodyFile::BODY_FILE=> { @link HttpMimeType }
     * }
     * @param string $contentType
     */
    protected $contentType;

    public function __construct() {
        $this->setContentType();
    }

    abstract protected function setContentType();

    abstract public function toString();

    public function getContentType() {
        return $this->contentType;
    }
}
