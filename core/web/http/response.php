<?php
class Response implements IResponse
{
    private $httpStatus;
    private $content;
    private $contentType;

    public function __construct(HttpStatus $header, $content, $contentType = null){
        $this->httpStatus = $header;
        $this->setContent($content);
        $this->setContentType($contentType);
        $this->setContentType($this->guessContent());
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

    public function getContentType(){
        return $this->contentType;
    }

    private function guessContent(){
        if(isset($this->contentType)){
            return $this->contentType;
        }
        if($this->content instanceof EzRpcResponse) {
            $this->content = $this->content->toJson();
            return HttpContentType::H_JSON;
        }else if(null !== json_decode($this->content, true)){
            return HttpContentType::H_JSON;
        }else{
            return HttpContentType::H_HTML;
        }
    }

    public function getHeader(){
        return $this->httpStatus;
    }

    public function toString(): string{
        return EzDataUtils::toString($this);
    }
}