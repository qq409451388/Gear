<?php

class RouterAnno
{
    public const POLICY = AnnoPolicyEnum::POLICY_RUNTIME;

    protected static $ins;

    protected function parseClassDocComment(string $docComment){
        preg_match("/@RequestController\(\'?\"?\/?(\w+)\"?\'?\)/", $docComment, $res);
        $this->classDoc = end($res);
    }

    protected function parseMethodDocComment(string $docComment){
        preg_match("/@RequestMapping\(\'?\"?\/?(\w+)\"?\'?\)/", $docComment, $res);
        $this->methodDoc = end($res);
    }

    public function buildPath($docComment1, $docComment2, $docComment3 = ''){
        $this->parse($docComment1, $docComment2, $docComment3);
        $path = '';
        if(!empty($this->getClassDoc()) && !empty($this->getMethodDoc())){
            $path = $this->getClassDoc() . 'routeranno.php/' .$this->getMethodDoc();
        }
        return $path;
    }
}