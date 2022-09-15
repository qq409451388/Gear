<?php
abstract class Anno
{
    protected $classDoc;
    protected $methodDoc;
    protected $propertyDoc;

    public static function get(){
        if(static::$ins == null){
            static::$ins = new static();
        }
        return static::$ins;
    }

    public function parse($docComment1, $docComment2, $docComment3 = ''){
        $this->parseClassDocComment($docComment1);
        $this->parseMethodDocComment($docComment2);
        $this->parsePropertyDocComment($docComment3);
        if(!$this->checkParsed()){
            DBC::throwEx("[RouterAnno] UnSupportDocComment ".$this->_dump());
        }
        return [$this->getClassDoc(), $this->getMethodDoc(), $this->getPropertyDoc()];
    }

    protected function getClassDoc(){
        return $this->classDoc ?? "";
    }

    protected function getMethodDoc(){
        return $this->methodDoc ?? "";
    }

    protected function getPropertyDoc(){
        return $this->propertyDoc ?? "";
    }

    protected function setClassDoc($doc){
        $this->classDoc = $doc;
    }

    protected function setMethodDoc($doc){
        $this->methodDoc = $doc;
    }

    protected function setPropertyDoc($doc){
        $this->propertyDoc = $doc;
    }

    protected function checkClassDocComment(){
        return true;
    }

    protected function checkMethodDocComment(){
        return true;
    }

    protected function checkPropertyDocComment(){
        return true;
    }

    protected function checkParsed(){
        return $this->checkClassDocComment() && $this->checkMethodDocComment() && $this->checkPropertyDocComment();
    }

    public function _dump(){
        return 'classDoc:'.$this->getClassDoc().',methodDoc:'.$this->getMethodDoc().',propertyDoc:'.$this->getPropertyDoc();
    }
}