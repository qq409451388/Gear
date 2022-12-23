<?php
abstract class BaseDAO implements EzBean
{
    abstract public function getEntity();
    abstract public function getTable();

    protected function findOne($sql, $params){
        $sql = "select * from ".$this->getTable()." ".$sql." limit 1";
        $res = DB::get("cuishou")->queryOne($sql, $params);
        $className = $this->getEntity();
        return $className::generate($res);
    }

    public function findById($id) {
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        if(null != $localCache && $data = $localCache->get($this->getEntity().$id)){
            $className = $this->getEntity();
            return $className::decodeJson($data);
        }
        $data = $this->findOne("where id = :id", [":id" => $id]);
        if(empty($data)){
            return null;
        }
        null != $localCache && $localCache->setOrReplace($this->getEntity().$id, EzString::encodeJson($data));
        return $data;
    }
}
