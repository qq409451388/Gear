<?php
abstract class EntityBase
{
    public $id;
    private $isDeleted = false;
    private $summary;

    public function __construct() {
        $this->summary = '';
    }

    abstract public function getTable();

    public function getId() {
        return $this->id;
    }

    public function calcSummary() {
        return md5(serialize(get_object_vars($this)));
    }

    public function getSummary() {
        return $this->summary;
    }

    public function createSummary() {
        $this->summary = $this->calcSummary();
    }

    public function delete() {
        $this->isDeleted = true;
    }

    public function isDeleted() {
        return $this->isDeleted;
    }

    public function toArray():array {
        $data = get_object_vars($this);
        unset($data['summary']);
        unset($data['isDeleted']);
        return $data;
    }

    public function toDbArray():array {
        $res = [];
        $arr = $this->toArray();
        foreach($arr as $k => $v) {
            $newKey = $this->transferToColumn($k);
            $res[$newKey] = $v;
        }
        return $res;
    }

    public static function decodeJson(string $json, $defaultValue = null){
        if(empty($json)){
            return $defaultValue;
        }
        $jsonObj = EzCollection::decodeJson($json);
        $t = new static();
        self::fillDTO($jsonObj, $t);
        return $t;
    }

    public static function generate(array $data){
        $t = new static();
        self::fillDTO($data, $t);
        return $t;
    }

    private static function transferToObj($k){
        $keyShards = explode("_", $k);
        array_walk($keyShards, function(&$val){
            $val = ucfirst($val);
        });
        return lcfirst(implode("", $keyShards));
    }

    private function transferToColumn($k){
        $newKey = "";
        for($i=0;$i<strlen($k);$i++) {
            $ord = ord($k[$i]);
            //65 - 90
            if(64 < $ord && $ord < 91){
                $newKey .= "_".chr($ord+32);
            } else {
                $newKey .= $k[$i];
            }
        }
        return $newKey;
    }

    public static function fillDTO(array $jsonArray, EntityBase $object){
        foreach ($jsonArray as $k => $v){
            $k = self::transferToObj($k);
            if(isset($object->dtoKeys()[$k])){
                $dtoClass = $object->dtoKeys()[$k];
                $dtoObj = new $dtoClass;
                self::fillDTO($v, $dtoObj);
                $object->$k = $dtoObj;
            }else if(isset($object->listKeys()[$k])){
                $listClass = $object->listKeys()[$k];
                foreach($v as $vKey => $vItem){
                    $listObj = new $listClass;
                    self::fillDTO($vItem, $listObj);
                    $object->$k[$vKey] = $listObj;
                }
            }else{
                $object->$k = $v;
            }
        }
        $object->createSummary();
    }

    /**
     * 数据传输对象，必填项字段
     * @description
     *      非空数组：isValid对此函数返回的列表进行检查
     *      空数组：isValid不会检查
     *      null：isValid对对象中的所有字段进行检查
     * @return null|array
     */
    protected function requiredKeys(){return null;}

    protected function listKeys():array{
        return [];
    }
    protected function dtoKeys():array{return [];}

    /**
     * 判断对象的某个属性是否是必填项
     * @param $key string 对象的成员属性名
     * @return bool 是否必填项
     */
    public function isRequired($key){
        $requiredKeys = $this->requiredKeys();
        //不设置 默认所有字段均为必填项
        if(is_null($requiredKeys)){
            return true;
        }
        return in_array($key, $requiredKeys);
    }

    /**
     * 检查对象是否有效，只检查必填项
     * @return bool
     */
    public function isValid(){
        foreach($this as $k => $v){
            if($this->isRequired($k) && $this->isParamBlank($v)){
                Logger::warn(sprintf("Check Class [%s] Fail! Column %s is Blank! Print this Object : %s",
                    get_class($this), $k, json_encode($this)));
                return false;
            }
        }
        return true;
    }

    /**
     * 检查数据是否有效
     * @param $data
     * @return bool
     */
    protected function isParamBlank($data){
        if(is_null($data)){
            return true;
        }
        if(is_numeric($data)){
            return false;
        }
        if($data instanceof BaseDO){
            return !$data->isValid();
        }
        return empty($data);
    }
}
