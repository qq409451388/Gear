<?php
class RouterAspect extends Aspect implements BuildAspect
{
    public function getHttpMethodLimit(){
        if ($this->getValue() instanceof RequestMapping) {
            return null;
        }
        $httpMethod = str_replace("Mapping", "", $this->getAnnoName());
        return HttpMethod::get($httpMethod);
    }

    public function check(): bool
    {
        if(!$this->getAtClass()->isSubclassOf(BaseController::class)){
            return false;
        }
        /**
         * 存在一个有效就往下走
         */
        $hasValid = false;
        DBC::assertTrue(!is_null($this->getDependList()), "[RouterAspect] DependList Is Empty !");
        foreach($this->getDependList() as $dependSon){
            if($dependSon->getAtMethod()->isPublic() && BaseController::class !== $dependSon->getAtMethod()->getDeclaringClass()->getName()){
                $hasValid = true;
            }
        }
        return $hasValid;
    }

    /**
     * @var RouterAspect $dependSon
     */
    public function adhere(): void
    {
        foreach($this->getDependList() as $dependSon){
            $path = trim($this->getValue()->path, "/") . "/" .trim($dependSon->getValue()->path, "/");
            if(!empty($path)){
                EzRouter::get()->setMapping($path, $this->getAtClass()->getName(), $dependSon->getAtMethod()->getName(), $dependSon->getHttpMethodLimit());
            }
        }
    }
}
