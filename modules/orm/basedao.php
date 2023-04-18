<?php
abstract class BaseDAO implements EzBean
{
    /**
     * @var Clazz $entityClazz
     */
    protected $entityClazz;
    protected $table;
    protected $database;

    public function __construct() {
        $this->entityClazz = $this->bindEntity();
        DBC::assertTrue($this->entityClazz->isSubClassOf(BaseDO::class),
            "[DAO] create Fail!",0, GearShutDownException::class);
        /**
         * @var AnnoItem $annoItem
         */
        $annoItem = AnnoationRule::searchCertainlyRelationshipAnnoation($this->entityClazz->getName(), EntityBind::class);
        /**
         * @var EntityBind $anno
         */
        $anno = $annoItem->getValue();

        $this->table = $anno->table;
        $this->database = $anno->db;
    }

    abstract protected function bindEntity():Clazz;

    /**
     * @param $sql
     * @param $params
     * @return BaseDO
     * @throws ReflectionException
     */
    protected function findOne($sql, $params){
        $sql = "select * from {$this->table} {$sql} limit 1";
        $res = DB::get($this->database)->queryOne($sql, $params);
        $className = $this->entityClazz->getName();
        return EzBeanUtils::createObject($res, $className);
    }

    /**
     * @param $id
     * @return BaseDO
     * @throws ReflectionException
     */
    public function findById($id) {
        /**
         * @var EzLocalCache $localCache
         */
        $localCache = CacheFactory::getInstance(CacheFactory::TYPE_MEM);
        if(null != $localCache && $data = $localCache->get($this->entityClazz->getName().$id)){
            return EzBeanUtils::createObjectFromJson($data, $this->entityClazz->getName());
        }
        $data = $this->findOne("where id = :id", [":id" => $id]);
        if(empty($data)){
            return null;
        }
        null != $localCache && $localCache->set($this->entityClazz->getName().$id, EzString::encodeJson($data));
        return $data;
    }

    public function save(BaseDO $domain) {
        $data = $domain->toArray();
        $data2 = [];
        foreach ($data as $k => $v) {
            $data2[strtolower($k)] = $v;
        }
        return DB::get($this->database)->save($this->table, $data2);
    }
}
