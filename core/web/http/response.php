<?php
class Response implements IResponse
{
    /**
     * @var HttpStatus
     */
    private $httpStatus;
    private $content;
    private $contentType;
    private $charset;

    public function __construct(HttpStatus $header, $content = null, $contentType = HttpMimeType::MIME_HTML, $charset = "charset=utf-8;"){
        $this->httpStatus = $header;
        $this->setContent($content);
        if (empty($contentType)) {
            $this->setContentType($this->guessContent());
        } else {
            $this->setContentType($contentType);
        }
        $this->setCharset($charset);
    }

    public function setContent($content){
        $this->content = $content;
    }

    public function getContent(){
        return $this->content;
    }

    public function setContentType($contentType){
        $this->contentType = $contentType;
    }

    public function setCharset($charset) {
        $this->charset = $charset;
    }

    public function getContentType(){
        return $this->contentType;
    }

    private function guessContent(){
        if(isset($this->contentType)){
            return $this->contentType;
        }
        if ($this->content instanceof EzRpcResponse) {
            $this->content = $this->content->toJson();
            return HttpContentType::H_JSON;
        } elseif (null !== json_decode($this->content, true)) {
            return HttpContentType::H_JSON;
        } else {
            return HttpContentType::H_HTML;
        }
    }

    public function getCharset() {
        return $this->charset;
    }

    public function getHeader(){
        return $this->httpStatus;
    }

    public function toString():string{
        return (new HttpInterpreter())->encode($this);
    }
}
