<?php

/**
 * @EzGmpDump
 */
class EzGmpBroker
{
    public const DIRECT = "direct";

    /**
     * @var EzLocalCache
     */
    private $memory;

    public function __construct(){
        $this->memory = BeanFinder::get()->pull(EzLocalCache::class);
        $this->init();
    }

    public function init(){
        $defaultExchange = ["default"];
        foreach($defaultExchange as $exchange){
            $exchangeObj = [
                "name" => $exchange,
                "type" => self::DIRECT,
                "exchangeAddress" => __CLASS__.$exchange."ADDR"
            ];
            $exchangeObjJson = EzString::encodeJson($exchangeObj);
            $this->memory->lpush(__CLASS__."EXCHANGE", $exchange);
            $this->memory->set(__CLASS__.$exchange, $exchangeObjJson);
        }
    }

    public function getExchanges(){
        print_r($this->memory->getAll());
        return $this->memory->get(__CLASS__."EXCHANGE");
    }

    public function bindQueueToExchange($queue, $exchange){
        $this->memory->lpush(__CLASS__.$exchange."ADDR", __CLASS__.$queue);
    }

    public function publish($exchange, $message){
        $queues = $this->memory->get(__CLASS__.$exchange."ADDR");
        if(empty($queues)){
            return false;
        }
        foreach($queues as $queue){
            $this->memory->lpush($queue, $message);
        }
        return true;
    }

    public function consume($queue){
        return $this->memory->lpop(__CLASS__.$queue);
    }
}