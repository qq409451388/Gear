<?php

/**
 * @RequestController("/gmp/api")
 */
class EzGmpServerApi extends BaseController
{
    /**
     * @Resource("EzGmpBroker")
     * @var EzGmpBroker
     */
    private $broker;

    /**
     * @GetMapping("/exchanges")
     * @param $request
     */
    public function exchanges($request){
        return EzRpcResponse::OK($this->broker->getExchanges());
    }

    /**
     * @GetMapping("/publish")
     * @param $request
     */
    public function publish(Request $request){
        $exchange = $request->get("exchange");
        $msg = $request->get("msg");
        $res = $this->broker->publish($exchange, $msg);
        return EzRpcResponse::OK($res);
    }

    /**
     * @GetMapping("/bindQueue")
     * @param $request
     */
    public function bindQueue(Request $request){
        $exchange = $request->get("exchange");
        $q = $request->get("queue");
        $this->broker->bindQueueToExchange($q, $exchange);
        return EzRpcResponse::OK(true);
    }

    /**
     * @GetMapping("/fetch")
     * @param $request
     */
    public function fetch(Request $request){
        $q = $request->get("queue");
        $message = $this->broker->consume($q);
        return EzRpcResponse::OK($message);
    }
}