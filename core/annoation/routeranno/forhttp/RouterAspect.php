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
        DBC::assertTrue($rpp->getClassInstance()->isSubclassOf(BaseController::class), "[RouterAspect] The Router Annoation Must Use At Object instance of BaseController!");
        $contextInstanceList = $rpp->getContextInstanceList();
        $mapping = $request = null;
        foreach ($contextInstanceList as $contextInstance) {
            if (is_null($mapping) && $contextInstance instanceof UrlMapping) {
                $mapping = $contextInstance;
            }
            if (is_null($request) && $contextInstance instanceof Request) {
                $request = $contextInstance;
            }
        }
        DBC::assertNonNull($mapping, "[RouterAspect] UrlMapping Not Found!");
        if ($mapping->getHttpMethod() !== $request->getRequestMethod()) {
            $rpp->setIsSkip(true);
            $response = new Response(HttpStatus::BAD_REQUEST(), "Http Method {$request->getRequestMethod()} Is Not Support!");
            $rpp->setReturnValue($response);
        }
    }

    public function after(RunTimeProcessPoint $rpp): void
    {
        // TODO: Implement after() method.
    }
}
