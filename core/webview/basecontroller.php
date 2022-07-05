<?php
class BaseController
{
    public function __get($obj){
        if(!BeanFinder::get()->has($obj)){
            DBC::throwEx('['.__CLASS__.'] class'.$obj.' is not exists!');
        }
        return BeanFinder::get()->pull($obj);
    }

    public function __call($func, $args){
        return '404 Not Found!';
    }

    protected function show($response, $path){
        DBC::assertTrue(defined("TEMPLATE_DIR"), "[Controller] Must Define const TEMPLATE_DIR At Enter File!");
        extract($response);
        $template = strtolower(TEMPLATE_DIR.'/'.$path.'.php');
        ob_start();
        include($template);
        $res = ob_get_contents();
        ob_end_clean();
        return $res;
    }
}