<?php

class Gear implements IDispatcher
{
    public function __construct(){

    }

    public function initWithScript() {
        Env::setRunModeScript();
    }

    public function initWithHttp() {
        Env::setRunModeConsole();
        $this->initComponents();
        //初始化对象
        $this->initObjects();
        $this->initRouter();
        $this->initAnno();
    }

    public function initWithTcp() {
        Env::setRunModeConsole();
        $this->initComponents();
        //初始化对象
        $this->initObjects();
        $this->initAnno();
    }

    private function initComponents() {
        $classess = Config::get("GLOBAL_USER_CLASS");
        if (empty($classess)) {
            return;
        }
        foreach($classess as $class) {
            if (is_subclass_of($class, EzComponent::class)) {
                Config::add("GLOBAL_COMPONENTS", $class);
            }
        }
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
        $annoList = $this->fetchComponentAnno();
        $this->startAnno($annoList);

        $annoList2 = $this->fetchBeanAnno();
        $this->startAnno($annoList2);
    }

    private function fetchAnnoFromClass($reflection, array &$annoList, array &$twichClassAnno) {
        $classAnnoList = $reflection->getAnnoationList();
        foreach($classAnnoList as $classAnno){
            $aspectClass = $this->buildPoorAspect($classAnno);
            if (is_null($aspectClass)) {
                continue;
            }
            $aspectClass->setAtClass($reflection);
            if(!is_null($aspectClass->getDependConf())){
                $twichClassAnno[] = $aspectClass;
            }else{
                $annoList[] = $aspectClass;
            }
        }
    }

    private function fetchAnnoFromMethod($reflection, array &$annoList, array &$annoMethodList) {
        $reflectionMethods = $reflection->getMethods();
        foreach($reflectionMethods as $reflectionMethod){
            $methodAnnoList = $reflectionMethod->getAnnoationList();
            foreach($methodAnnoList as $methodAnno){
                $aspectMethod = $this->buildPoorAspect($methodAnno);
                if (is_null($aspectMethod)) {
                    continue;
                }
                $aspectMethod->setAtClass($reflection);
                $aspectMethod->setAtMethod($reflectionMethod);
                if($aspectMethod->isCombination()){
                    $annoMethodList[$aspectMethod->getAnnoName()][] = $aspectMethod;
                }else{
                    $annoList[] = $aspectMethod;
                }
            }
        }
    }

    private function fetchAnnoFromProperty($reflection, array &$annoList, array &$annoPropertyList) {
        $reflectionProperties = $reflection->getProperties();
        foreach($reflectionProperties as $reflectionProperty){
            $propertyAnnoList = $reflectionProperty->getAnnoationList();
            foreach($propertyAnnoList as $propertyAnno){
                $aspectProperty = $this->buildPoorAspect($propertyAnno);
                if (is_null($aspectProperty)) {
                    continue;
                }
                $aspectProperty->setAtClass($reflection);
                $aspectProperty->setAtProperty($reflectionProperty);
                if($aspectProperty->isCombination()){
                    $annoPropertyList[$aspectProperty->getAnnoName()][] = $aspectProperty;
                }else{
                    $annoList[] = $aspectProperty;
                }
            }
        }
    }

    private function fetchAnnoForDepend($twichClassAnno, $annoMethodList, $annoPropertyList, array &$annoList) {
        foreach($twichClassAnno as $classAnno){
            /**
             * @var Aspect $classAnno
             */
            $dependClassList = $classAnno->getDependConf();
            foreach($dependClassList as $dependClass){
                $classAnno->addDepend($annoMethodList[$dependClass]??[]);
                $classAnno->addDepend($annoPropertyList[$dependClass]??[]);
            }
            $annoList[] = $classAnno;
        }
    }
    private function fetchBeanAnno(){
        $annoList = [];
        foreach(BeanFinder::get()->getAll(DynamicProxy::class) as $obj) {
            /**
             * @var DynamicProxy $obj
             */
            $reflection = $obj->getReflectionClass();
            $twichClassAnno = [];
            $annoMethodList = [];
            $annoPropertyList = [];
            $this->fetchAnnoFromClass($reflection, $annoList, $twichClassAnno);
            $this->fetchAnnoFromMethod($reflection, $annoList, $annoMethodList);
            $this->fetchAnnoFromProperty($reflection, $annoList, $annoPropertyList);
            $this->fetchAnnoForDepend($twichClassAnno, $annoMethodList, $annoPropertyList, $annoList);
        }
        return $annoList;
    }

    private function fetchComponentAnno() {
        $annoList = [];
        if (empty(Config::get("GLOBAL_COMPONENTS"))) {
            return $annoList;
        }
        var_dump(Config::get("GLOBAL_COMPONENTS"));
        foreach (Config::get("GLOBAL_COMPONENTS") as $componentClassName) {
            $reflection = new EzReflectionClass($componentClassName);
            $twichClassAnno = [];
            $annoMethodList = [];
            $annoPropertyList = [];
            $this->fetchAnnoFromClass($reflection, $annoList, $twichClassAnno);
            $this->fetchAnnoFromMethod($reflection, $annoList, $annoMethodList);
            $this->fetchAnnoFromProperty($reflection, $annoList, $annoPropertyList);
            $this->fetchAnnoForDepend($twichClassAnno, $annoMethodList, $annoPropertyList, $annoList);
        }
        return $annoList;
    }

    /**
     * @param array<Aspect> $annoList
     * @return void
     */
    private function startAnno($annoList){
        foreach($annoList as $aspect){
            DBC::assertTrue($aspect->check(), "[Gear] Init Anno Check Fail! AnnoInfo:".$aspect->getAnnoName());
            if(AnnoPolicyEnum::POLICY_BUILD == $aspect->getPolicy()){
                /**
                 * @var $aspect Aspect|BuildAspect
                 */
                $aspect->adhere();
            }
            if(AnnoPolicyEnum::POLICY_RUNTIME == $aspect->getPolicy()){
                /**
                 * @var $aspect Aspect|RunTimeAspect
                 */
                $aspect->around();
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
        $policy = $annoReflection->getConstant("POLICY");
        DBC::assertNotEmpty($policy, "[Gear] Anno $k Must Defined Const POLICY!");
        DBC::assertNotEmpty($annoReflection->getConstant("STRUCT"), "[Gear] Anno $k Must Defined Const STRUCT!");
        $aspectClass = $annoReflection->getConstant("ASPECT");
        //Runtime为必填 才需要检查
        if (AnnoPolicyEnum::POLICY_RUNTIME == $policy) {
            DBC::assertTrue($aspectClass, "[Gear] Anno $k Must Defined Const ASPECT!");
        } else if (!$aspectClass) {
            //如果是BuildPolicy，又没定义过Aspect类走空逻辑
            return null;
        }
        /**
         * @var $aspect Aspect
         */
        $aspect = new $aspectClass;
        $aspect->setPolicy($policy);
        $aspect->setAnnoName($k);
        $aspect->setValue($v);
        $aspect->setIsCombination($annoReflection->implementsInterface(AnnoationCombination::class));
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
