<?php
class RouterAspect extends Aspect implements BuildAspect, RunTimeAspect
{
    private function getHttpMethodLimit() {
        if (RequestMapping::class === get_class($this->getValue())) {
            return null;
        }
        $httpMethod = str_replace("Mapping", "", $this->getAnnoName());
        return HttpMethod::get($httpMethod);
    }

    public function check(): bool
    {
        if (!$this->getAtClass()->isSubclassOf(BaseController::class)) {
            Logger::error("The Router Annoation Must Use At Object instance of BaseController!");
            return false;
        }
        if (!SchemaConst::isHttpOrSecurity()) {
            return false;
        }
        /**
         * 存在一个有效就往下走
         */
        $hasValid = false;
        DBC::assertNonNull($this->getDependList(), "[RouterAspect] DependList Is Empty !");
        foreach ($this->getDependList() as $dependSon) {
            if ($dependSon->getAtMethod()->isPublic()
                && BaseController::class !== $dependSon->getAtMethod()->getDeclaringClass()->getName()) {
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
        foreach ($this->getDependList() as $dependSon) {
            $cPath = trim($this->getValue()->getPath(), "/");
            $aPath = trim($dependSon->getValue()->getPath(), "/");
            $path =  $cPath."/".$aPath;
            if (!empty($path)) {
                EzRouter::get()->setMapping(
                    $path,
                    $this->getAtClass()->getName(),
                    $dependSon->getAtMethod()->getName(),
                    $dependSon->getHttpMethodLimit()
                );
            }
        }
    }

    public function before(RunTimeProcessPoint $rpp): void
    {
        /*// 如有必要，可以限制仅在POST时才使用RequestBody
        if (HttpMethod::POST !== $this->getHttpMethod()) {
            return null;
        }*/
        var_dump($rpp);
    }

    public function after(RunTimeProcessPoint $rpp): void
    {
        // TODO: Implement after() method.
    }
}
