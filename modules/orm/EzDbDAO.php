<?php
class EzDbDAO implements EzHelper
{
    public function __construct($database) {
        $this->database = $database;
    }

    public function findOne($clazz, $sql, $params) {
        $res = DB::get($this->database)->queryOne($sql, $params);
        if (empty($res)) {
            return null;
        }
        $className = $clazz->getName();
        return EzBeanUtils::createObject($res, $className);
    }

    public function findList($clazz, $sql, $params) {
        $res = DB::get($this->database)->query($sql, $params);
        if (empty($res)) {
            return [];
        }
        $className = $clazz->getName();
        foreach ($res as &$item) {
            $item = EzBeanUtils::createObject($item, $className);
        }
        return $res;
    }
}
