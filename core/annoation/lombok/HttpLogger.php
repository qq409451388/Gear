<?php
class HttpLogger extends Anno implements ILogger
{
    /**
     * 指定注解可以放置的位置（默认: 所有）@see AnnoElementType
     */
    public static function constTarget()
    {
        return [
            AnnoElementType::TYPE_METHOD,
            AnnoElementType::TYPE_CLASS
        ];
    }

    public static function constPolicy()
    {
        return AnnoPolicyEnum::POLICY_RUNTIME;
    }

    public static function constStruct()
    {
        return AnnoValueTypeEnum::TYPE_LITE;
    }

    public static function constAspect()
    {
        return LombokLogAspect::class;
    }

    public function logBefore(RunTimeProcessPoint $rpp)
    {
        /**
         * @var Request $request
         */
        $request = current($rpp->getArgs());
        Logger::info("Request [{}] [HTTP Method:{}] {}, with args:{}", $rpp->getClassName(), $request->getRequestMethod(),
            $request->getPath(),
            EzObjectUtils::toString($request->getQuery()));
    }

    public function logAfter(RunTimeProcessPoint $rpp)
    {
        /**
         * @var Request $request
         */
        $request = current($rpp->getArgs());
        Logger::info("Request [{}] [HTTP Method:{}] {}, with args:{}, return value:{}",
            $rpp->getClassName(), $request->getRequestMethod(), $request->getPath(),
            EzObjectUtils::toString($request->getQuery()), EzObjectUtils::toString($rpp->getReturnValue()));
    }

    public function logException(RunTimeProcessPoint $rpp)
    {
        /**
         * @var Request $request
         */
        $request = current($rpp->getArgs());
        Logger::error("Request [{}] [HTTP Method:{}] {}, with args:{}, return value:{}",
            $rpp->getClassName(), $request->getRequestMethod(), $request->getPath(),
            EzObjectUtils::toString($request->getQuery()), EzObjectUtils::toString($rpp->getReturnValue()));
    }
}
