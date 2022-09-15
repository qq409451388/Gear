<?php
class Gear implements IDispatcher
{
    public function __construct(){
        $classess = CacheFactory::getInstance(CacheFactory::TYPE_MEM)->get("GLOBAL_USER_CLASS");
        $classess = EzCollection::decodeJson($classess);
        //初始化对象
        $this->initObjects($classess);
        $this->initRouter();
        $this->initAnno();
    }

    private function initObjects($classess){
        foreach($classess as $class) {
            $this->createObject($class);
        }
    }

    private function initRouter(){
        foreach(BeanFinder::get()->getAll() as $objName => $obj) {
            $reflection = new ReflectionClass($obj);
            $reflectionMethods = $reflection->getMethods();
            foreach($reflectionMethods as $reflectionMethod) {
                if(!$reflection->isSubclassOf(BaseController::class)){
                    return;
                }
                if(!$reflectionMethod->isPublic() || BaseController::class === $reflectionMethod->getDeclaringClass()->getName()){
                    return;
                }
                $defaultPath = $objName . '/' . $reflectionMethod->getName();
                EzRouter::get()->setMapping($defaultPath, $objName, $reflectionMethod->getName());
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function initAnno(){
        $annoList = [];
        foreach(BeanFinder::get()->getAll() as $obj){
            $reflection = new ReflectionClass($obj);
            $reflectionMethods = $reflection->getMethods();
            $classDocComment = $reflection->getDocComment();
            $classAnnoList = $this->analyzeDocComment($classDocComment, AnnoElementType::TYPE_CLASS);
            foreach($reflectionMethods as $reflectionMethod) {
                $methodDocComment = $reflectionMethod->getDocComment();
                $methodAnnoList = $this->analyzeDocComment($methodDocComment, AnnoElementType::TYPE_METHOD);
                $this->relationshipAnno($classAnnoList, $methodAnnoList, $reflection, $reflectionMethod, $annoList);
            }
        }
        /**
         * @var $annoItem Aspect
         */
        foreach($annoList as $annoItem){
            //先处理一种情况
            if($annoItem->check()){
                $annoItem->adhere();
            }
        }
    }

    /**
     * @return array<AnnoItem>
     */
    private function analyzeDocComment($comment, $at){
        $result = [];
        if(empty($comment)){
            return $result;
        }
        $s= "/(.*)@(?<annoName>\S+)\(\'?\"?(?<content>[\/a-zA-Z0-9]+)\'?\"?\)/";
        preg_match_all($s, $comment, $matches, 2);
        foreach($matches as $matched){
            $annoName = $matched['annoName']??null;
            $content = $matched['content']??null;
            if(empty($annoName)
                || !is_subclass_of($annoName, Anno::class)
                || ( !is_numeric($content) && empty($content) )
            ) {
                Logger::warn("[Gear] UnExpected AnnoInfo:{} ({})", $annoName, $content);
                continue;
            }
            $result[] = AnnoItem::create($annoName, $content, $at);
        }
        return $result;
    }

    /**
     * @param $methodAnnoList array<AnnoItem> 某一个方法上的注解列表
     * @throws ReflectionException
     */
    private function relationshipAnno($classAnnoList, $methodAnnoList, $reflectionClass, $reflectionMethod, &$annoList){
        foreach($classAnnoList as $annoItem){
            /**
             * @var $annoItem AnnoItem
             */
            $k = $annoItem->annoName;
            $v = $annoItem->value;
            $annoReflection = new ReflectionClass($k);
            $target = $annoReflection->getConstant("TARGET")?:AnnoElementType::TYPE;
            DBC::assertEquals($target, $annoItem->at, "[Gear] Anno $k Must Used At ".AnnoElementType::getDesc($annoItem->at)."!");
            $dependConf = $annoReflection->getConstant("DEPEND");
            DBC::assertTrue($dependConf, "[Gear] Anno $k Must Defined Const DEPEND!");
            $dependList = null;
            $aspectClass = $annoReflection->getConstant("ASPECT");
            DBC::assertTrue($dependConf, "[Gear] Anno $k Must Defined Const ASPECT!");
            if(!is_null($dependConf)){
                if(empty($dependConf)){
                    Logger::warn("[Gear] Anno {} Set DEPEND But Empty", $k);
                    continue;
                }
                $dependListIntersect = [];
                foreach($methodAnnoList as $methodAnnoKey => $methodAnno){
                    if(in_array($methodAnno->annoName, $dependConf)){
                        $dependListIntersect[] = $methodAnno;
                        unset($methodAnnoList[$methodAnnoKey]);
                    }
                }
                if(empty($dependListIntersect)){
                    continue;
                }
                $dependList = [];
                foreach($dependListIntersect as $dependItem){
                    $dependItemName = $dependItem->annoName;
                    $annoReflectionSon = new ReflectionClass($dependItemName);
                    $dependConf = $annoReflectionSon->getConstant("DEPEND");
                    $dependList = null;
                    $aspectClass = $annoReflectionSon->getConstant("ASPECT");
                    DBC::assertTrue($aspectClass, "[Gear] Anno $dependItemName Must Defined Const ASPECT!");
                    $aspectSon = new $aspectClass;
                    $policy = $annoReflectionSon->getConstant("POLICY");
                    DBC::assertTrue($policy, "[Gear] Anno $dependItemName Must Defined Const POLICY!");
                    $aspectSon->setPolicy($policy);
                    $aspectSon->setAnnoName($dependItemName);
                    $aspectSon->setValue($dependItem->value);
                    $aspectSon->setAtClass($reflectionClass);
                    $aspectSon->setAtMethod($reflectionMethod);
                    $target = $annoReflectionSon->getConstant("TARGET")?:AnnoElementType::TYPE;
                    DBC::assertEquals($target, $dependItem->at, "[Gear] Anno $dependItem->annoName Must Used At "
                        .AnnoElementType::getDesc($dependItem->at)."!");
                    $aspectSon->setTarget($target);
                    $aspectSon->setDependConf($dependConf);
                    $aspectSon->setDependList($dependList);
                    $dependList[] = $aspectSon;
                }
            }
            /**
             * @var $aspect Aspect
             */
            $aspect = new $aspectClass;
            $policy = $annoReflection->getConstant("POLICY");
            DBC::assertTrue($policy, "[Gear] Anno $k Must Defined Const POLICY!");
            $aspect->setPolicy($policy);
            $aspect->setAnnoName($k);
            $aspect->setValue($v);
            $aspect->setAtClass($reflectionClass);
            $aspect->setAtMethod($reflectionMethod);
            $aspect->setTarget($target);
            $aspect->setDependConf($dependConf);
            $aspect->setDependList($dependList);
            $annoList[] = $aspect;
        }
        foreach($methodAnnoList as $annoItem){
            /**
             * @var $annoItem AnnoItem
             */
            $k = $annoItem->annoName;
            $v = $annoItem->value;
            $annoReflection = new ReflectionClass($k);
            $target = $annoReflection->getConstant("TARGET")?:AnnoElementType::TYPE;
            DBC::assertEquals($target, $annoItem->at, "[Gear] Anno $k Must Used At ".AnnoElementType::getDesc($annoItem->at)."!");
            $dependConf = $annoReflection->getConstant("DEPEND");
            DBC::assertTrue($dependConf, "[Gear] Anno $k Must Defined Const DEPEND!");
            $dependList = null;
            $aspectClass = $annoReflection->getConstant("ASPECT");
            DBC::assertTrue($dependConf, "[Gear] Anno $k Must Defined Const ASPECT!");
            /**
             * @var $aspect Aspect
             */
            $aspect = new $aspectClass;
            $policy = $annoReflection->getConstant("POLICY");
            DBC::assertTrue($policy, "[Gear] Anno $k Must Defined Const POLICY!");
            $aspect->setPolicy($policy);
            $aspect->setAnnoName($k);
            $aspect->setValue($v);
            $aspect->setAtClass($reflectionClass);
            $aspect->setAtMethod($reflectionMethod);
            $aspect->setTarget($target);
            $aspect->setDependConf($dependConf);
            $aspect->setDependList($dependList);
            $annoList[] = $aspect;
        }
    }

    public function judgePath(string $path):bool{
        return EzRouter::get()->judgePath($path);
    }

    public function matchedRouteMapping(string $path):IRouteMapping {
        return EzRouter::get()->getMapping($path);
    }

    /**
     * create a obj if none in objects[]
     * @param $class
     * @return void
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