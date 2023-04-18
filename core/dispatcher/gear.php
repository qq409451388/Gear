<?php
class Gear implements IDispatcher
{
    public function __construct(){

    }

    public function initWithScript() {
        $this->initConfig();
        Env::setRunModeScript();
    }

    public function initWithHttp() {
        $this->initConfig();
        Env::setRunModeConsole();
        //初始化对象
        $this->initObjects();
        $this->initRouter();
        $this->initAnno();
    }

    public function initWithTcp() {
        $this->initConfig();
        Env::setRunModeConsole();
        $classess = CacheFactory::getInstance(CacheFactory::TYPE_MEM)->get("GLOBAL_USER_CLASS");
        $classess = EzCollectionUtils::decodeJson($classess);
        //初始化对象
        $this->initObjects($classess);
        $this->initAnno();
    }

    private function initObjects(){
        $classess = Config::get("GLOBAL_USER_CLASS");
        if (empty($classess)) {
            return;
        }
        foreach($classess as $class) {
            $this->createBean($class);
        }
    }

    private function initConfig() {
        Config::init();
    }

    protected function initRouter() {
        /**
         * @var DynamicProxy $obj
         */
        foreach(BeanFinder::get()->getAll(DynamicProxy::class) as $objName => $obj) {
            $reflection = $obj->getReflectionClass();
            $reflectionMethods = $reflection->getMethods();
            foreach($reflectionMethods as $reflectionMethod) {
                if(!$reflection->isSubclassOf(BaseController::class)){
                    continue;
                }
                if(!$reflectionMethod->isPublic()
                    || BaseController::class === $reflectionMethod->getDeclaringClass()->getName()){
                    continue;
                }
                if (!$reflectionMethod->isUserDefined() || $reflectionMethod->isConstructor()) {
                    continue;
                }
                $defaultPath = $objName . '/' . $reflectionMethod->getName();
                EzRouter::get()->setMapping($defaultPath, $reflection->getName(), $reflectionMethod->getName());
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function initAnno() {
        $annoList = $this->fetchAnno();
        $this->startAnno($annoList);
    }

    private function fetchAnno(){
        $annoList = [];
        foreach(BeanFinder::get()->getAll() as $obj){
            if ($obj instanceof DynamicProxy) {
                $reflection = $obj->getReflectionClass();
            } else {
                $reflection = new EzReflectionClass($obj);
            }
            $reflectionMethods = $reflection->getMethods();
            $reflectionProperties = $reflection->getProperties();
            $classAnnoList = $reflection->getAnnoationList();
            $twichClassAnno = [];
            foreach($classAnnoList as $classAnno){
                $aspectClass = $this->buildPoorAspect($classAnno);
                $aspectClass->setAtClass($reflection);
                if(!is_null($aspectClass->getDependConf())){
                    $twichClassAnno[] = $aspectClass;
                }else{
                    $annoList[] = $aspectClass;
                }
            }
            $annoMethodList = [];
            foreach($reflectionMethods as $reflectionMethod){
                $methodAnnoList = $reflectionMethod->getAnnoationList();
                foreach($methodAnnoList as $methodAnno){
                    $aspectMethod = $this->buildPoorAspect($methodAnno);
                    $aspectMethod->setAtClass($reflection);
                    $aspectMethod->setAtMethod($reflectionMethod);
                    if($aspectMethod->isCombination()){
                        $annoMethodList[$aspectMethod->getAnnoName()][] = $aspectMethod;
                    }else{
                        $annoList[] = $aspectMethod;
                    }
                }
            }
            $annoPropertyList = [];
            foreach($reflectionProperties as $reflectionProperty){
                $propertyAnnoList = $reflectionProperty->getAnnoationList();
                foreach($propertyAnnoList as $propertyAnno){
                    $aspectProperty = $this->buildPoorAspect($propertyAnno);
                    $aspectProperty->setAtClass($reflection);
                    $aspectProperty->setAtProperty($reflectionProperty);
                    if($aspectProperty->isCombination()){
                        $annoPropertyList[$aspectProperty->getAnnoName()][] = $aspectProperty;
                    }else{
                        $annoList[] = $aspectProperty;
                    }
                }
            }
            foreach($twichClassAnno as $classAnno){
                $dependClassList = $classAnno->getDependConf();
                foreach($dependClassList as $dependClass){
                    $classAnno->addDepend($annoMethodList[$dependClass]??[]);
                    $classAnno->addDepend($annoPropertyList[$dependClass]??[]);
                }
                $annoList[] = $classAnno;
            }
        }
        return $annoList;
    }

    /**
     * @param array<Aspect> $annoList
     * @return void
     */
    private function startAnno($annoList){
        foreach($annoList as $annoItem){
            DBC::assertTrue($annoItem->check(), "[Gear] Init Anno Check Fail! AnnoInfo:".$annoItem->getAnnoName());
            if(AnnoPolicyEnum::POLICY_BUILD == $annoItem->getPolicy()){
                /**
                 * @var $annoItem Aspect|BuildAspect
                 */
                $annoItem->adhere();
            }
            if(AnnoPolicyEnum::POLICY_RUNTIME == $annoItem->getPolicy()){
                /**
                 * @var $annoItem Aspect|RunTimeAspect
                 */
                $annoItem->around();
            }
        }
    }

    /**
     * @param AnnoItem $annoItem
     * @return Aspect
     */
    private function buildPoorAspect(AnnoItem $annoItem){
        $k = $annoItem->annoName;
        $v = $annoItem->getValue();
        $annoReflection = new EzReflectionClass($k);
        $target = $annoReflection->getConstant("TARGET")?:AnnoElementType::TYPE;
        DBC::assertEquals($target, $annoItem->at, "[Gear] Anno $k Must Used At ".AnnoElementType::getDesc($target)."!");
        $dependConf = $annoReflection->getConstant("DEPEND");
        $aspectClass = $annoReflection->getConstant("ASPECT");
        DBC::assertTrue($aspectClass, "[Gear] Anno $k Must Defined Const ASPECT!");
        /**
         * @var $aspect Aspect
         */
        $aspect = new $aspectClass;
        $policy = $annoReflection->getConstant("POLICY");
        DBC::assertTrue($policy, "[Gear] Anno $k Must Defined Const POLICY!");
        $aspect->setPolicy($policy);
        $aspect->setAnnoName($k);
        $aspect->setValue($v);
        $aspect->setIsCombination($annoReflection->getConstant("ISCOMBINATION")??false);
        $aspect->setTarget($target);
        $aspect->setDependConf($dependConf);
        return $aspect;
    }

    public function judgePath(string $path):bool{
        return EzRouter::get()->judgePath($path);
    }

    public function matchedRouteMapping(string $path):IRouteMapping {
        return EzRouter::get()->getMapping($path);
    }

    /**
     * create an obj if none in objects[]
     * @param string $class
     * @return void
     * @throws Exception
     */
    private function createBean($class){
        if (!is_subclass_of($class, EzBean::class)) {
            return;
        }
        try {
            /**
             * isDeep传false， 交由注解逻辑:startAnno()统一注入
             */
            BeanFinder::get()->save($class, EzBeanUtils::createBean($class, false));
            Logger::console("[Gear]Create Bean {$class}");
        } catch (Exception $e) {
            DBC::throwEx("[Gear]Create Bean Exception {$e->getMessage()}", 0, GearShutDownException::class);
        }
    }
}
