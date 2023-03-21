<?php
class Gear implements IDispatcher
{
    public function __construct(){
        $classess = CacheFactory::getInstance(CacheFactory::TYPE_MEM)->get("GLOBAL_USER_CLASS");
        $classess = EzCollectionUtils::decodeJson($classess);
        //初始化对象
        $this->initObjects($classess);
    }

    public function initWithHttp() {
        $this->initRouter();
        $this->initAnno();
    }

    public function initWithTcp() {
        $this->initAnno();
    }

    private function initObjects($classess){
        if (empty($classess)) {
            return;
        }
        foreach($classess as $class) {
            $this->createObject($class);
        }
    }

    private function initRouter() {
        foreach(BeanFinder::get()->getAll() as $objName => $obj) {
            $reflection = new ReflectionClass($obj);
            $reflectionMethods = $reflection->getMethods();
            foreach($reflectionMethods as $reflectionMethod) {
                if(!$reflection->isSubclassOf(BaseController::class)){
                    continue;
                }
                if(!$reflectionMethod->isPublic() || BaseController::class === $reflectionMethod->getDeclaringClass()->getName()){
                    continue;
                }
                $defaultPath = $objName . '/' . $reflectionMethod->getName();
                EzRouter::get()->setMapping($defaultPath, $objName, $reflectionMethod->getName());
                Logger::console("[Gear] Mapping Path ".$defaultPath." To Controller ".$objName);
            }
        }
    }

    /**
     * @throws ReflectionException
     */
    private function initAnno(){
        $annoList = $this->fetchAnno();
        $this->startAnno($annoList);
    }

    private function fetchAnno(){
        $annoList = [];
        foreach(BeanFinder::get()->getAll() as $obj){
            $reflection = new ReflectionClass($obj);
            $reflectionMethods = $reflection->getMethods();
            $reflectionProperties = $reflection->getProperties();
            $classDocComment = $reflection->getDocComment();
            $classAnnoList = $this->analyzeDocComment($classDocComment, AnnoElementType::TYPE_CLASS);
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
                $methodAnnoList = $this->analyzeDocComment($reflectionMethod->getDocComment(), AnnoElementType::TYPE_METHOD);
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
                $propertyAnnoList = $this->analyzeDocComment($reflectionProperty->getDocComment(), AnnoElementType::TYPE_FIELD);
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
        $annoReflection = new ReflectionClass($k);
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

    /**
     * @return array<AnnoItem>
     */
    private function analyzeDocComment($comment, $at){
        $result = [];
        if(empty($comment)){
            return $result;
        }
        /**
         * 注解第一种类型，参数为普通字符串
         * @example: @XXX("qqq") 或 @YYY('qqq')
         */
        $s= "/(.*)@(?<annoName>[a-zA-Z0-9]+)\(\'?\"?(?<content>[\/a-zA-Z0-9\#\{\}\*]+)\'?\"?\)/";
        preg_match_all($s, $comment, $matches, 2);
        foreach($matches as $matched){
            $annoName = $matched['annoName']??null;
            $content = $matched['content']??null;
            if(empty($annoName)){
                Logger::warn("[Gear] Not Found AnnoClass AnnoInfo:{} ({})", $annoName, $content);
                continue;
            } else if (!is_subclass_of($annoName, Anno::class)){
                Logger::warn("[Gear] UnExpected AnnoInfo:{} ({})", $annoName, $content);
                continue;
            }else if(!is_numeric($content) && empty($content)){
                Logger::warn("[Gear] Empty Content AnnoInfo:{} ({})", $annoName, $content);
                continue;
            }
            $result[] = AnnoItem::create($annoName, $content, $at);
        }
        /**
         * 注解第二种类型，参数为JSON字符串
         * @example: @XXX({"a":"a", "b":"b"})
         */
        $s = "/(.*)@(?<annoName>[a-zA-Z0-9]+)\(\{(?<content>(.*)+)\}\)/";
        preg_match_all($s, $comment, $matches, 2);
        foreach($matches as $matched){
            $annoName = $matched['annoName']??null;
            $content = $matched['content']??null;
            if(empty($annoName)){
                Logger::warn("[Gear] Not Found AnnoClass AnnoInfo:{} ({})", $annoName, $content);
                continue;
            } else if (!is_subclass_of($annoName, Anno::class)){
                Logger::warn("[Gear] UnExpected AnnoInfo:{} ({})", $annoName, $content);
                continue;
            }else if(!is_numeric($content) && empty($content)){
                Logger::warn("[Gear] Empty Content AnnoInfo:{} ({})", $annoName, $content);
                continue;
            }

            $content = "{".$content."}";
            $result[] = AnnoItem::createComplex($annoName, $content, $at);
        }
        /**
         * 注解第三种类型，无任何参数
         * @example: @XXX
         */
        $s = "/(.*)@(?<annoName>[a-zA-Z0-9]+)[\f\n\r\t\v]+/";
        preg_match_all($s, $comment, $matches, 2);
        foreach($matches as $matched){
            $annoName = $matched['annoName']??null;
            if(empty($annoName)){
                Logger::warn("[Gear] Not Found AnnoClass AnnoInfo:{} ", $annoName);
                continue;
            } else if (!is_subclass_of($annoName, Anno::class)){
                Logger::warn("[Gear] UnExpected AnnoInfo:{}", $annoName);
                continue;
            }

            $result[] = AnnoItem::createComplex($annoName, null, $at);
        }
        return $result;
    }

    public function judgePath(string $path):bool{
        return EzRouter::get()->judgePath($path);
    }

    public function matchedRouteMapping(string $path):IRouteMapping {
        return EzRouter::get()->getMapping($path);
    }

    /**
     * create an obj if none in objects[]
     * @param $class
     * @return void
     */
    private function createObject($class){
        try {
            if (is_subclass_of($class, EzComponent::class) || !is_subclass_of($class, EzBean::class)) {
                return;
            }
            BeanFinder::get()->import($class);
        } catch (Exception $e) {
            DBC::throwEx("[Gear]Create Object Exception {$e->getMessage()}");
        }
    }
}
