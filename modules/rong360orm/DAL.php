<?php
class DAL
{
    private $code;

    public static function get($code) {
        $obj = new DAL();
        $obj->code = $code;
        return $obj;
    }

    public function find($class, $id){
        if($entity = LocalCache::getIns()->getById($class, $id)) {
            return $entity;
        }
        $nullEntity = new $class();
        $table = $nullEntity->getTable();
        $data = DB::get($this->code)->queryOne("select * from $table where id = :id limit 1",
            [":id" => $id]);
        if(empty($data)){
            return null;
        }
        $entity = $class::generate($data);
        LocalCache::getIns()->add($entity);
        return $entity;
    }
}
