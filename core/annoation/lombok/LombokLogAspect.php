<?php
class LombokLogAspect extends Aspect implements RunTimeAspect
{

    public function check(): bool
    {
        return true;
    }

    public function before(RunTimeProcessPoint $rpp): void
    {
        /**
         * @var Request $request
         */
        $request = current($rpp->getArgs());
        Logger::info("Request [{}] [HTTP Method:{}] {}, with args:{}", $rpp->getClassName(), $request->getRequestMethod(),
            $request->getPath(),
            EzObjectUtils::toString($request->getQuery()));
    }

    public function after(RunTimeProcessPoint $rpp): void
    {
        /**
         * @var Request $request
         */
        $request = current($rpp->getArgs());
        Logger::info("Request [{}] [HTTP Method:{}] {}, with args:{}, return value:{}",
            $rpp->getClassName(), $request->getRequestMethod(), $request->getPath(),
            EzObjectUtils::toString($request->getQuery()), EzObjectUtils::toString($rpp->getReturnValue()));
    }
}