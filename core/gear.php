<?php
class Gear implements IDispatcher
{
    public function init(Array $classess){
        //初始化对象
        $this->initObjects($classess);
        $this->initAnno();
        return $this;
    }

    private function initObjects($classess){
        foreach($classess as $class) {
            $this->createObject($class);
        }
    }

    /**
     * @throws ReflectionException
     */
    private function initAnno(){
        foreach(BeanFinder::get()->getAll() as $objName => $obj){
            $reflection = new ReflectionClass($obj);
            $reflectionMethods = $reflection->getMethods();
            foreach($reflectionMethods as $reflectionMethod) {
                $this->initRouter($objName, $reflection, $reflectionMethod);
                $this->initCustomize($objName, $reflection, $reflectionMethod);
            }
        }
    }

    private function initRouter(String $objName, ReflectionClass  $reflection, ReflectionMethod $reflectionMethod){
        $path = RouterAnno::get()->buildPath($reflection->getDocComment(), $reflectionMethod->getDocComment());
        if(!empty($path)){
            EzRouter::get()->setMapping($path, $objName, $reflectionMethod->getName());
        }
        $path = $objName.'/'.$reflectionMethod->getName();
        EzRouter::get()->setMapping($path, $objName, $reflectionMethod->getName());
    }

    private function initCustomize(String $objName, ReflectionClass  $reflection, ReflectionMethod $reflectionMethod){

    }

    public function judgePath(string $path):bool{
        return EzRouter::get()->judgePath($path);
    }

    public function disPatch(Request $request):Response {
        try{
            $mapper = EzRouter::get()->getMapping($request->getPath());
            if($mapper instanceof NullMapping){
                return new Response(HttpStatus::NOT_FOUND(), "");
            }else{
                return new Response(HttpStatus::OK(), $mapper->disPatch($request));
            }
        }catch (Exception $e){
            Logger::error($e->getMessage());
            $msg = $e->getMessage().PHP_EOL;
            echo $msg;
            return new Response(HttpStatus::INTERNAL_SERVER_ERROR(), $msg);
        }

    }

    /**
     * create a obj if none in objects[]
     * @param $class
     * @return Object
     */
    private function createObject($class){
        try {
            Logger::console("[Gear]Create Object {$class}");
            BeanFinder::get()->save($class, new $class);
        } catch (ReflectionException $e) {
            DBC::throwEx("[Gear]Create Object Exception {$e->getMessage()}");
        }
    }

    public function invokeInterceptor():bool{
        return true;
    }

    public function invokeMethod($item, Array $params):String{
        $obj = BeanFinder::get()->pull(current($item));
        if(null == $obj){
            return EzRpcResponse::EMPTY_RESPONSE;
        }
        if(!$this->invokeInterceptor()){
            return EzRpcResponse::EMPTY_RESPONSE;
        }
        return call_user_func_array([$obj,end($item)], $params)->toJson();
    }
}