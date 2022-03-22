<?php
class Response
{
    private $httpStatus;
    private $content;
    private $contentType;

    public function __construct(HttpStatus $header, $content){
        $this->httpStatus = $header;
        $this->setContent($content);
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
        /*if($this->content instanceof EzRpcResponse) {
            $this->content = $this->content->toJson();
            return EzHeader::H_JSON;
        }elseif(null !== json_decode($this->content, true)){
            return EzHeader::H_JSON;
        }elseif(null !== ($jcontent = json_encode($this->content))){
            $this->content = $jcontent;
            return EzHeader::H_JSON;
        }else{
        }*/
        return EzHeader::H_TEXT_HTML;
    }

    public function getHeader(){
        return $this->httpStatus;
    }
}